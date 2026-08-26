<?php

namespace App\Http\Controllers;

use App\Models\Space;
use App\Models\SpacesRating;
use App\Models\Mate;
use App\Models\SpaceMate;
use Illuminate\Support\Facades\DB;
use App\Models\Photo;

class SpaceController extends Controller
{
    
    public function show($uid)
    {
        // Ignore the $country_slug parameter and just query based on $uid
        $space = Space::where('uid', $uid)->first();

        if (!$space) {
            $space = Space::where('username', $uid)->first();
            if (!$space) {
                abort(404);
            }
        }

        if ($space->private) {
            if (!auth()->check()) {
                echo("<b style='color:red;'>This space is private!</b>");
                abort(403); // Space is private and user is not authenticated
            }

            if ($space->user_id != auth()->user()->id && !auth()->user()->isAdmin()) {
                echo("<b style='color:red;'>This space is private!</b>");
                abort(403); // Space is private and user is not the owner
            }
        }

        if (($space->status == 'pending' || $space->status == 'declined')) {
            if (!auth()->check()) {
                echo("<b style='color:red;'>This space is awaiting approval!</b>");
                abort(403); // Space has not been approved
            }
            else if ($space->user_id != auth()->user()->id && !auth()->user()->isAdmin()) {
                echo("<b style='color:red;'>This space is awaiting approval!</b>");
                abort(403); // Space has not been approved
            }
        }

        if ($space->photo) {
            $photos = DB::table('photos')
                ->where('space_uid', $space->uid)
                ->select('filename', 'id', 'pos')
                ->get()
                ->keyBy('id')
                ->toArray();
        
            $space->photos = $photos;
            
            $space->first_photo = null;

            if ($photos) {
                $firstPhoto = null;

                foreach ($photos as $photo) {
                    if ($photo->pos == 1) {
                        $firstPhoto = $photo->filename;
                        break;
                    }
                }

                if (!$firstPhoto) {
                    // Find the photo with the highest 'id' value
                    $maxId = max(array_keys($photos));
                    if ($maxId) {
                        $firstPhoto = $photos[$maxId]->filename;
                    }
                }

                $space->first_photo = $firstPhoto;
            }
        }

        $ratings = SpacesRating::where('space_uid', $space->uid)
        ->with(['ratedByUser' => function ($query) {
            $query->select('user_id', 'uid', 'username', 'name', 'profile_pic', 'photo', 'avatar');
        }])
        ->get();

        // return response()->json(['space' => $space]);
        return(['space'=> $space, 'ratings'=>$ratings]);
    }


    public function mySpaces()
    {
        $spaces = Space::select('name', 'uid', 'country', 'country_slug', 'country_code', 'address', 'photo')
            ->where('user_id', auth()->user()->id)
            ->limit(100)
            ->get();

        // Using a subquery to count active SpaceMates for each space and fetch the latest photo if 'photo' is set to '1'
        $spaces->each(function ($space) {
            $activeMatesCount = SpaceMate::where('space_uid', $space->uid)
                ->where('status', 'active')
                ->count();

            $space->mates = $activeMatesCount;

            if ($space->photo) {
                $photos = DB::table('photos')
                    ->where('space_uid', $space->uid)
                    ->select('filename', 'id', 'pos')
                    ->get()
                    ->keyBy('id')
                    ->toArray();
                
                $space->first_photo = null;
    
                if ($photos) {
                    $firstPhoto = null;
    
                    foreach ($photos as $photo) {
                        if ($photo->pos == 1) {
                            $firstPhoto = $photo->filename;
                            break;
                        }
                    }
    
                    if (!$firstPhoto) {
                        // Find the photo with the highest 'id' value
                        $maxId = max(array_keys($photos));
                        if ($maxId) {
                            $firstPhoto = $photos[$maxId]->filename;
                        }
                    }
    
                    $space->first_photo = $firstPhoto;
                }
            }

        });

        return $spaces;
    }


    public function myColivingSpaces()
    {
        $userId = auth()->id();

        // current & past memberships
        $colivingSpaces = SpaceMate::where('status', 'active')
            ->where('user_id', $userId)
            ->get(['space_uid', 'left_at', 'joined_at']);

        $pastColivingSpaces = SpaceMate::where('status', 'left')
            ->where('user_id', $userId)
            ->get(['space_uid', 'left_at', 'joined_at']);

        // all space UIDs we’ll need
        $allUids = $colivingSpaces->pluck('space_uid')
            ->merge($pastColivingSpaces->pluck('space_uid'))
            ->unique()
            ->values();

        // fetch base space data, keyed by uid
        $spacesByUid = Space::whereIn('uid', $allUids)
            ->select('uid', 'name', 'country_slug', 'photo')
            ->get()
            ->keyBy('uid');

        // fetch best photo per space (prefer pos=1, else latest id)
        $photoCandidates = DB::table('photos')
            ->select('space_uid', 'filename', 'pos', 'id')
            ->whereIn('space_uid', $allUids)
            ->orderByRaw('CASE WHEN pos = 1 THEN 0 ELSE 1 END')
            ->orderByDesc('id')
            ->get()
            ->groupBy('space_uid')
            ->map(fn ($rows) => optional($rows->first())->filename);

        // attach space info + photo
        $attach = function ($spaces) use ($spacesByUid, $photoCandidates) {
            return $spaces->map(function ($item) use ($spacesByUid, $photoCandidates) {
                $space = $spacesByUid->get($item->space_uid);
                if ($space) {
                    $item->name         = $space->name;
                    $item->country_slug = $space->country_slug;
                    $item->first_photo  = $photoCandidates[$space->uid] ?? null;
                } else {
                    $item->name = $item->country_slug = $item->first_photo = null;
                }
                return $item;
            });
        };

        return [
            $attach($colivingSpaces),
            $attach($pastColivingSpaces),
        ];
    }
    

    public function hub($uid)
    {
        $space = Space::where('uid', $uid)->select('uid', 'user_id', 'name', 'info', 'photo')->first();
        
        // Add first photo if space has photos (using efficient query like myColivingSpaces)
        if ($space && $space->photo) {
            $firstPhoto = DB::table('photos')
                ->where('space_uid', $space->uid)
                ->orderByRaw('CASE WHEN pos = 1 THEN 0 ELSE 1 END')
                ->orderByDesc('id')
                ->value('filename');
            
            $space->first_photo = $firstPhoto;
            
            // Debug logging
            \Log::info('Hub photo debug', [
                'space_uid' => $space->uid,
                'space_photo_flag' => $space->photo,
                'first_photo_filename' => $firstPhoto,
                'photos_count' => DB::table('photos')->where('space_uid', $space->uid)->count()
            ]);
        }
        
        $mates = SpaceMate::where('space_uid', $uid)->where('status', 'active')->select('user_id')->get();
        foreach ($mates as $mate) {
            $m[] = $mate['user_id'];
        }
        return ['mates' => $m, 'space' => $space];
    }


    public function homeData()
    {
        $spaces = Space::select('uid', 'name', 'country', 'city', 'formatted_address', 'longitude', 'latitude')->where('status', 'approved')->where('private', 0)->get();

        if (auth()->check()) {
            $user_id = auth()->user()->id;
            $mate = Mate::where('user_id', $user_id)->get();
            return compact('spaces', 'mate');
            // return [$spaces, $mate];
        } else {
            // return [$spaces];
            return compact('spaces');
        }
    }

    
    public function searchData()
    {
        $spaces = Space::select('uid', 'name', 'country', 'city', 'formatted_address', 'longitude', 'latitude', 'photo')->where('status', 'approved')->where('private', 0)->get();

        foreach ($spaces as $space) {
            if ($space->photo) {

                $photos = DB::table('photos')
                    ->where('space_uid', $space->uid)
                    ->select('filename', 'id', 'pos')
                    ->get()
                    ->keyBy('id')
                    ->toArray();
            
                $space->photos = $photos;
                
                $space->first_photo = null;

                if ($photos) {
                    $firstPhoto = null;

                    foreach ($photos as $photo) {
                        if ($photo->pos == 1) {
                            $firstPhoto = $photo->filename;
                            break;
                        }
                    }

                    if (!$firstPhoto) {
                        // Find the photo with the highest 'id' value
                        $maxId = max(array_keys($photos));
                        if ($maxId) {
                            $firstPhoto = $photos[$maxId]->filename;
                        }
                    }

                    $space->first_photo = $firstPhoto;
                }

            }
            else {
                $space->photo = false;
            }
        }

        return compact('spaces');
    }
    

    public function index(): View
    {
        $spacesByCountry = Space::select('country', 'country_slug')
            ->distinct()
            ->where('status', 'approved')
            ->with('spaces')
            ->get();

        return view('welcome', compact('spacesByCountry'));
    }
    
    
    public function getCountries()
    {
        $countries = Space::select('country', 'country_slug', 'country_code')
            ->groupBy('country', 'country_slug', 'country_code')
            ->selectRaw('country, country_slug, country_code, COUNT(*) as count')
            ->where('status', 'approved')
            ->where('private', 0)
            ->get();

        $latest_space = Space::where('status', 'approved')
            ->where('photo', 1)
            ->where('claimed', 1)
            ->where('private', 0)
            ->select('country_code', 'country', 'country_slug', 'name', 'uid')
            ->latest('created_at')
            ->first();

        // Default null in case there is no suitable space or no photo
        if ($latest_space) {
            $latest_space_photo = DB::table('photos')
                ->where('space_uid', $latest_space->uid)
                // same logic as in other methods:
                ->orderByRaw('CASE WHEN pos = 1 THEN 0 ELSE 1 END')
                ->orderByDesc('id')
                ->value('filename');   // returns string|NULL

            $latest_space->space_photo = $latest_space_photo ?? null;
        }

        $countries_count = $countries->count();
        $cities_count    = Space::distinct('city')->count();
        $spaces_count    = Space::select('uid')->distinct()->count();

        return response()->json([
            $countries,
            $countries_count,
            $cities_count,
            $spaces_count,
            $latest_space,
        ]);
    }

    public function getNewestSpaces()
    {
        $spaces = Space::where('status', 'approved')
            ->where('photo', 1)
            ->where('claimed', 1)
            ->where('private', 0)
            ->select('uid', 'name', 'address', 'country_code', 'country', 'country_slug')
            ->latest('created_at')
            ->limit(9)
            ->get();

        $uids = $spaces->pluck('uid');

        $photoCandidates = DB::table('photos')
            ->select('space_uid', 'filename')
            ->whereIn('space_uid', $uids)
            ->orderByRaw('CASE WHEN pos = 1 THEN 0 ELSE 1 END')
            ->orderByDesc('id')
            ->get()
            ->groupBy('space_uid')
            ->map(fn ($rows) => optional($rows->first())->filename);

        $spaces->each(function ($space) use ($photoCandidates) {
            $space->first_photo = $photoCandidates[$space->uid] ?? null;
        });

        return response()->json($spaces);
    }


    public function getSpacesByCountry($countrySlug)
    {
        $spaces = Space::where('country_slug', $countrySlug)
            ->select('name', 'uid', 'longitude', 'latitude', 'country_code', 'country', 'address', 'photo')
            ->where('status', 'approved')
            ->where('private', 0)
            ->get();
        $country = Space::where('country_slug', $countrySlug)->select('country')->first();
        if (!$country) {
            abort(404);
        }

        $spaces->each(function ($space) {
            $activeMatesCount = SpaceMate::where('space_uid', $space->uid)
                ->where('status', 'active')
                ->count();

            $space->mates = $activeMatesCount;

            
            if ($space->photo) {
                $photos = DB::table('photos')
                    ->where('space_uid', $space->uid)
                    ->select('filename', 'id', 'pos')
                    ->get()
                    ->keyBy('id')
                    ->toArray();
                
                $space->first_photo = null;

                if ($photos) {
                    $firstPhoto = null;
    
                    foreach ($photos as $photo) {
                        if ($photo->pos == 1) {
                            $firstPhoto = $photo->filename;
                            break;
                        }
                    }
    
                    if (!$firstPhoto) {
                        // Find the photo with the highest 'id' value
                        $maxId = max(array_keys($photos));
                        if ($maxId) {
                            $firstPhoto = $photos[$maxId]->filename;
                        }
                    }
    
                    $space->first_photo = $firstPhoto;
                }
            }

        });

        return response()->json([
            'country' => $country->country,
            'spaces' => $spaces,
        ]);
    }

    public function getSpacesByCategory($category)
    {
        $validCategories = array_keys(Space::CATEGORIES);
        if (!in_array($category, $validCategories)) {
            abort(404);
        }

        $spaces = Space::where('category', $category)
            ->select('name', 'uid', 'longitude', 'latitude', 'country_code', 'country', 'country_slug', 'address', 'photo')
            ->where('status', 'approved')
            ->where('private', 0)
            ->get();

        $spaces->each(function ($space) {
            $activeMatesCount = SpaceMate::where('space_uid', $space->uid)
                ->where('status', 'active')
                ->count();

            $space->mates = $activeMatesCount;

            if ($space->photo) {
                $photos = DB::table('photos')
                    ->where('space_uid', $space->uid)
                    ->select('filename', 'id', 'pos')
                    ->get()
                    ->keyBy('id')
                    ->toArray();
                
                $space->first_photo = null;

                if ($photos) {
                    $firstPhoto = null;
    
                    foreach ($photos as $photo) {
                        if ($photo->pos == 1) {
                            $firstPhoto = $photo->filename;
                            break;
                        }
                    }
    
                    if (!$firstPhoto) {
                        $maxId = max(array_keys($photos));
                        if ($maxId) {
                            $firstPhoto = $photos[$maxId]->filename;
                        }
                    }
    
                    $space->first_photo = $firstPhoto;
                }
            }
        });

        return response()->json([
            'category' => Space::CATEGORIES[$category],
            'categorySlug' => $category,
            'spaces' => $spaces,
        ]);
    }

    public function getSpacesByCountryCategory($countrySlug, $category)
    {
        $validCategories = array_keys(Space::CATEGORIES);
        if (!in_array($category, $validCategories)) {
            abort(404);
        }

        $spaces = Space::where('country_slug', $countrySlug)
            ->where('category', $category)
            ->select('name', 'uid', 'longitude', 'latitude', 'country_code', 'country', 'address', 'photo')
            ->where('status', 'approved')
            ->where('private', 0)
            ->get();
        
        $country = Space::where('country_slug', $countrySlug)->select('country')->first();
        
        if (!$country) {
            abort(404);
        }

        $spaces->each(function ($space) {
            $activeMatesCount = SpaceMate::where('space_uid', $space->uid)
                ->where('status', 'active')
                ->count();

            $space->mates = $activeMatesCount;

            
            if ($space->photo) {
                $photos = DB::table('photos')
                    ->where('space_uid', $space->uid)
                    ->select('filename', 'id', 'pos')
                    ->get()
                    ->keyBy('id')
                    ->toArray();
                
                $space->first_photo = null;

                if ($photos) {
                    $firstPhoto = null;
    
                    foreach ($photos as $photo) {
                        if ($photo->pos == 1) {
                            $firstPhoto = $photo->filename;
                            break;
                        }
                    }
    
                    if (!$firstPhoto) {
                        // Find the photo with the highest 'id' value
                        $maxId = max(array_keys($photos));
                        if ($maxId) {
                            $firstPhoto = $photos[$maxId]->filename;
                        }
                    }
    
                    $space->first_photo = $firstPhoto;
                }
            }

        });

        return response()->json([
            'country' => $country->country,
            'category' => Space::CATEGORIES[$category],
            'categorySlug' => $category,
            'spaces' => $spaces,
        ]);
    }

    public function getCities()
    {
        $cities = Space::select('country', 'country_slug', 'country_code', 'city', 'city_clug')
        ->groupBy('country', 'country_slug', 'country_code', 'city', 'city_clug')
        ->selectRaw('country, country_slug, country_code, city, city_clug, COUNT(*) as count')
        ->where('status', 'approved')
        ->where('private', 0)
        ->get();

        $countries_count = Space::select('country_slug')->distinct()->count();
        $cities_count = Space::distinct('city')->count();
        $spaces_count = Space::select('uid')->distinct()->count();

        return response()->json([$cities, $countries_count, $cities_count, $spaces_count]);
    }
    
    
    public function getSpacesByCity($citySlug)
    {
        $spaces = Space::where('city_slug', $citySlug)->select('name', 'uid', 'longitude', 'latitude', 'country_code', 'country', 'address', 'photo')->where('status', 'approved')->where('private', 0)->get();
        $city = Space::where('city_slug', $citySlug)->select('city')->first();

        $spaces->each(function ($space) {
            $activeMatesCount = SpaceMate::where('space_uid', $space->uid)
                ->where('status', 'active')
                ->count();

            $space->mates = $activeMatesCount;

            
            if ($space->photo) {
                $photos = DB::table('photos')
                    ->where('space_uid', $space->uid)
                    ->select('filename', 'id', 'pos')
                    ->get()
                    ->keyBy('id')
                    ->toArray();
                
                $space->first_photo = null;

                if ($photos) {
                    $firstPhoto = null;
    
                    foreach ($photos as $photo) {
                        if ($photo->pos == 1) {
                            $firstPhoto = $photo->filename;
                            break;
                        }
                    }
    
                    if (!$firstPhoto) {
                        // Find the photo with the highest 'id' value
                        $maxId = max(array_keys($photos));
                        if ($maxId) {
                            $firstPhoto = $photos[$maxId]->filename;
                        }
                    }
    
                    $space->first_photo = $firstPhoto;
                }
            }

        });

        return response()->json([
            'city' => $city->city,
            'spaces' => $spaces,
        ]);
    }

}
