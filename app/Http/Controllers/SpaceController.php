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
                echo("Space is private");
                abort(403); // Space is private and user is not authenticated
            }

            if ($space->user_id != auth()->user()->id) {
                echo("Space is private");
                abort(403); // Space is private and user is not the owner
            }
        }

        if (($space->status == 'pending' || $space->status == 'declined')) {
            if (!auth()->check()) {
                echo("The space has not been approved yet, please come back later");
                abort(403); // Space has not been approved
            }
            else if (auth()->user()->id != 1) {
                echo("The space has not been approved yet, please come back later");
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
        $spaces = Space::select('name', 'uid', 'country', 'country_slug', 'country_code', 'photo')
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
        $coliving_spaces = SpaceMate::select('space_uid', 'left_at', 'joined_at')
            ->where('status', 'active')
            ->where('user_id', auth()->user()->id)
            ->get();

        foreach ($coliving_spaces as $cs) {
            $space = Space::where('uid', $cs['space_uid'])->select('name', 'country_slug')->get();

            if (!empty($space[0])) {
                $cs['name'] = $space[0]['name'];
                $cs['country_slug'] = $space[0]['country_slug'];
            }
        }

        $past_coliving_spaces = SpaceMate::select('space_uid', 'left_at', 'joined_at')
            ->where('status', 'left')
            ->where('user_id', auth()->user()->id)
            ->get();

        foreach ($past_coliving_spaces as $cs) {
            $space = Space::where('uid', $cs['space_uid'])->select('name', 'country_slug')->get();

            if (!empty($space[0])) {
                $cs['name'] = $space[0]['name'];
                $cs['country_slug'] = $space[0]['country_slug'];
            }
        }

        return [$coliving_spaces, $past_coliving_spaces];
    }
    

    public function hub($uid)
    {
        $space = Space::where('uid', $uid)->select('user_id', 'name', 'info')->first();
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
        $spaces = Space::select('uid', 'name', 'country', 'formatted_address', 'longitude', 'latitude', 'photo')->where('status', 'approved')->get();

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

        $latest_space_photo = DB::table('photos')
        ->where('space_uid', $latest_space->uid)
        ->where('pos', 0)
        ->select('filename')
        ->first();

        $latest_space->space_photo = $latest_space_photo->filename;

        $countries_count = $countries->count();
        $cities_count = Space::distinct('city')->count();
        $spaces_count = Space::select('uid')->distinct()->count();

        return response()->json([$countries, $countries_count, $cities_count, $spaces_count, $latest_space]);
    }


    public function getSpacesByCountry($countrySlug)
    {
        $spaces = Space::where('country_slug', $countrySlug)->select('name', 'uid', 'longitude', 'latitude', 'country_code', 'country', 'address', 'photo')->where('status', 'approved')->where('private', 0)->get();
        $country = Space::where('country_slug', $countrySlug)->select('country')->first();

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


    public function getCities()
    {
        $cities = Space::select('country', 'country_slug', 'country_code', 'city', 'city_clug')
        ->groupBy('country', 'country_slug', 'country_code', 'city', 'city_clug')
        ->selectRaw('country, country_slug, country_code, city, city_clug, COUNT(*) as count')
        ->where('status', 'approved')
        ->where('private', 0)
        ->get();

        $countries_count = $countries->count();
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
