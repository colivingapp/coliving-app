<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Models\User;

use App\Models\MatesRating;
use App\Models\SpacesRating;
use App\Models\Mate;
use App\Models\Space;
use App\Models\SpaceMate;
use App\Models\Photo;
use App\Models\Log;
use App\Models\Claim;
use App\Models\Bookmark;
use App\Models\InboxMateMate;
use App\Models\InboxMateSpace;
use App\Models\InboxJoinRequest;
use App\Models\SettingsNotification;

use App\Services\EmailService;
use App\Services\MateService;

use App\Http\Controllers\SpaceController;
use App\Http\Controllers\InboxConversationController;
use App\Http\Controllers\InboxJoinRequestController;

use App\Http\Controllers\InboxMateSpaceController;
use App\Http\Controllers\InboxMateMateController;
use App\Http\Controllers\InboxHubController;

use Illuminate\Support\Facades\DB;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::get('/', function (SpaceController $spaceController) {
    return $spaceController->homeData();
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->get('/mate', function (Request $request) {
    $user_id = auth()->user()->id;
    $mateService = app(MateService::class);
    $mate = $mateService->getMateWithBookmarks($user_id);
    return ($mate);
});

Route::middleware('auth:sanctum')->get('/acc_settings', function (Request $request) {
    $user_id = auth()->user()->id;
    $notifications = SettingsNotification::where('user_id', $user_id)->get();
    $newsletter = User::where('id', $user_id)->value('newsletter');
    return (['notifications' => $notifications, 'newsletter' => $newsletter]);
});

Route::middleware('auth:sanctum')->post('/update_settings', function (Request $request) {
    $user = Auth::user();

    // Update notifications
    $notifications = SettingsNotification::where('user_id', $user->id)->first();
    if ($notifications) {
        $notifications->new_join_request_received = $request->input('notifySpaceJoinReq', $notifications->new_join_request_received);
        $notifications->new_conversation_started = $request->input('notifyNewConvo', $notifications->new_conversation_started);
        $notifications->new_review_received = $request->input('notifyNewReview', $notifications->new_review_received);
        $notifications->save();
    }

    // Update newsletter
    $user->newsletter = $request->input('newsletter', $user->newsletter);
    $user->save();

    return response()->json(['status' => 'success']);
});

Route::get('/space/{uid}', [SpaceController::class, 'show']);

Route::post('/space', function (Request $request) {
    // Validate
    if (!$request->input('name')) {
        return('error_name');
    }

    $email = $request->input('email');
    if (!$email) {
        return 'error_email';
    } else if (!preg_match('/\S+@\S+\.\S+/', $email)) {
        return 'error_email';
    }

    $uid = $request->input('uid');

    $type = "edited";
    if ($uid) {
        $space = Space::where('uid', $uid)->first();
        if (!$space) {
            return response()->json(['error' => 'Space not found.'], 404);
        }
    } else {
        $type = "created";
        $space = new Space();

        function generateRandomId($length) {
            // with removed i, and l
            $characters = '1234567890abcdefghjkmnpqrstouvwxyz';
            $id = '';
            
            for ($i = 0; $i < $length; $i++) {
                do {
                    $randomIndex = mt_rand(0, strlen($characters) - 1);
                    $nextChar = $characters[$randomIndex];
        
                    // Check if the previous three characters are of the same type (either all letters or all numbers)
                    $sameTypeSequence = $i > 2 && 
                        ctype_alpha($nextChar) == ctype_alpha($id[$i - 1]) &&
                        ctype_alpha($nextChar) == ctype_alpha($id[$i - 2]) &&
                        ctype_alpha($nextChar) == ctype_alpha($id[$i - 3]);
        
                } while ($sameTypeSequence);
        
                $id .= $nextChar;
            }
            
            return $id;
        }
        
        $space->uid = generateRandomId(12);
    }

    function slugify($text){
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        if (empty($text)) {
          return 'n-a';
        }
        return $text;
    }

    $user_id = Auth::user()->id;

    if ($type == "edited") {
        if ($user_id != 1 && $user_id != (int)$space->user_id) {
            return response()->json(['error' => 'Unauthorized action.'], 401);
        }
    }
    
    $space->name = (string)$request->input('name');
    $space->username = (string)$request->input('username');
    if (!$space->username) {
        $space->username = null;
    }
    $space->country = (string)$request->input('country');
    $space->country_code = (string)$request->input('country_code');
    $space->address = (string)$request->input('address');
    $space->city = (string)$request->input('city');
    $space->country_slug = (string)slugify($request->input('country'));
    $space->city_slug = (string)slugify($request->input('city'));
    $space->email = (string)$request->input('email');
    $space->website = (string)$request->input('website');
    $space->phone = (string)$request->input('phone');
    $space->whatsapp = (string)$request->input('whatsapp');
    $space->description = (string)$request->input('description');
    $space->info = (string)$request->input('info');
    $space->user_id = $user_id;

    $space->orgSpont = $request->input('orgSpont');
    $space->quietLoud = $request->input('quietLoud');
    $space->earlyNight = $request->input('earlyNight');
    $space->workFun = $request->input('workFun');
    $space->expTrad = $request->input('expTrad');
    $space->multiLocal = $request->input('multiLocal');

    $space->amenities = (string)$request->input('amenities');    

    $space->twitter =(string)$request->input('twitter');
    $space->facebook = (string)$request->input('facebook');
    $space->instagram = (string)$request->input('instagram');
    $space->youtube = (string)$request->input('youtube');
    $space->tiktok  = (string)$request->input('tiktok');
    $space->pinterest = (string)$request->input('pinterest');
    $space->linkedin  = (string)$request->input('linkedin');
    $space->linktree  = (string)$request->input('linktree');
    $space->telegram  = (string)$request->input('telegram');
    $space->github  = (string)$request->input('github');
    $space->substack  = (string)$request->input('substack');
    $space->medium  = (string)$request->input('medium');

    $space->booking_com  = (string)$request->input('booking_com');
    $space->coliving_com  = (string)$request->input('coliving_com');
    $space->airbnb_com  = (string)$request->input('airbnb_com');
    
    $space->google_url = (string)$request->input('google_url');
    $space->latitude = (string)$request->input('latitude');
    $space->longitude = (string)$request->input('longitude');

    $space->private = (string)$request->input('private');

    // Auto-approve new spaces for admins
    if (in_array($user_id, config('ca.admins'))) {
        $space->status = 'approved';
    }

    $space->save();


    // DELETE PHOTOS (if any)

    // Convert $request->imageUrls to an array of IDs, excluding empty objects
    $imageUrls = json_decode($request->imageUrls, true);
    $imageIds = [];
    
    if (is_array($imageUrls)) {
        foreach ($imageUrls as $imageUrl) {
            if (isset($imageUrl['id'])) {
                $imageIds[] = $imageUrl['id'];
            }
        }
    }

    // Get the IDs of photos to be deleted
    $idsToDelete = [];
    $fileNamesToDelete = [];

    foreach ($space->photos as $photo) {
        if (!in_array($photo->id, $imageIds)) {
            $idsToDelete[] = $photo->id;
            $fileNamesToDelete[] = $photo->filename;
        }
    }

    // Delete the photos from the database
    if (!empty($idsToDelete)) {
        Photo::whereIn('id', $idsToDelete)->delete();
    }

    // var_dump($fileNamesToDelete);

    // Delete photo files
    if (!empty($fileNamesToDelete)) {
        foreach ($fileNamesToDelete as $fileName) {
            // return($fileName);
            $existingPhoto = str_replace('/storage/spaces/', 'public/spaces/', '/storage/spaces/'.$fileName);
            Storage::delete($existingPhoto);
        }
    }


    // SAVE NEW PHOTOS

    $photosSaved = false;
    
    // Save the uploaded files
    if ($request->hasFile('photos')) {
        $files = $request->file('photos');
    
        // Validate each file in the array
        foreach ($files as $file) {
            // Check file format
            $allowedFormats = ['jpeg', 'jpg', 'png', 'jfif', 'webp'];
            $fileExtension = strtolower($file->getClientOriginalExtension());
    
            if (!in_array($fileExtension, $allowedFormats)) {
                return response()->json(['error' => 'Invalid file format. Only JPEG and PNG are allowed.'], 400);
            }
    
            // Check file size
            $maxFileSize = 4 * 1024 * 1024; // 4MB
            if ($file->getSize() > $maxFileSize) {
                return response()->json(['error' => 'File size exceeds 4MB.'], 400);
            }
        }
    
        // Validate the total number of files
        $maxFiles = 20;
        if (count($files) > $maxFiles) {
            return response()->json(['error' => 'Maximum 20 files allowed.'], 400);
        }
    
        foreach ($files as $photo) {
            $filename = uniqid() . '.' . $photo->getClientOriginalExtension();
            $path = $photo->storeAs('public/spaces', $filename);
            // $photoPath = str_replace('public/', 'storage/', $path);
            $photo = new Photo(['filename' => $filename, 'space_uid' => $space->uid]);
            $space->photos()->save($photo);
            $photosSaved = true;
        }
    }

    if ($photosSaved && !$space->photo) {
        $space->photo = 1;
        $space->save();
    }

    // Update the positions of existing photos if 'pos' is not equal to 0
    if (!empty($imageUrls)) {
        foreach ($imageUrls as $imageUrl) {
            if (isset($imageUrl['id']) && isset($imageUrl['pos']) && $imageUrl['pos'] !== 0) {
                $photo = Photo::find($imageUrl['id']);

                if ($photo) {
                    $photo->pos = $imageUrl['pos'];
                    $photo->save();
                }
            }
        }
    }

    Log::create([
        'user_id' => $user_id,
        'space_uid' => $space->uid,
        'action' => 'space '.$type,
    ]);

    if ($type == 'created') {
        $to = env('ADMIN_EMAIL');
        $template = 'space_created';
        $extraData = 'extra_data';

        $data = [
            'name' => 'admin',
            'extraData' => $extraData
        ];

        $emailService = new EmailService();
        $emailService->sendEmail($to, 'Space Created', $template, $data);
    }

    return response()->json(['message' => 'success', 'space' => $space]);
})->middleware('auth:sanctum');



Route::get('/mates', function(SpaceController $spaceController) {
    
    $matesData = Mate::where('private', 0)
    ->where(function ($query) {
        $query->where('name', '!=', '')
              ->orWhere('username', '!=', '');
    })
    ->select('uid', 'name', 'username', 'avatar', 'profile_pic', 'photo')
    ->get();

    // Loop through the collection and modify each item
    foreach ($matesData as $mate) {
        if ($mate->profile_pic === 'avatar') {
            unset($mate->photo);
        }
        else {
            unset($mate->avatar);
        }
    }

    $data[0] = $matesData;

    return ($data); 
});



Route::get('/space/{uid}/hub', function(SpaceController $spaceController, $uid) {
    $data = $spaceController->hub($uid);
   
    // $spaceMates = SpaceMate::where("space_uid", $uid)->where('status', 'active')->get();
    $spaceMates = SpaceMate::where("space_uid", $uid)->get();

    $user_id = Auth::user()->id;
    $found = 0;

    $mateIds = [];

    foreach ($spaceMates as $mate) {
        if ($mate->user_id == $user_id) {
            $found = true;
        }

        $mateIds[] = $mate->user_id;
    }

    if(!$found) {
        return("not_a_member");
    }

    $matesData = Mate::whereIn('user_id', $mateIds)
    ->select('user_id', 'uid', 'name', 'username', 'avatar')
    ->get();

    foreach ($matesData as $mateData) {
        foreach ($spaceMates as $spaceMate) {
            if ($spaceMate->user_id == $mateData->user_id) {
                $spaceMate->uid = $mateData->uid;
                $spaceMate->name = $mateData->name;
                $spaceMate->username = $mateData->username;
                $spaceMate->avatar = $mateData->avatar;
            }
        }
    }


    $uniqueMates = [];

    foreach ($spaceMates as $mate) {
        $userId = $mate->user_id;
    
        if (!isset($uniqueMates[$userId])) {
            $uniqueMates[$userId] = $mate;
        } else {
            if ($mate->id > $uniqueMates[$userId]->id) {
                $uniqueMates[$userId] = $mate;
            }
        }
    }
    
    $spaceMates = [];
    foreach ($uniqueMates as $uniqueMate) {
        $spaceMates[] = $uniqueMate;
    }

    $data[0] = $spaceMates;

    return ($data); 
})->middleware('auth:sanctum');


Route::post('/space/{uid}/leave', function($uid) {
    $user_id = Auth::user()->id;

    // if ($response == 'Message Sent!') {
        $spaceMate = SpaceMate::where('user_id', $user_id)->where('space_uid', $uid)->where('status', 'active')->first();
        $spaceMate->left_at = now();
        $spaceMate->status = 'left';
        $spaceMate->save();
    // }
    
    Log::create([
        'user_id' => $user_id,
        'space_uid' => $uid,
        'action' => 'mate left space',
    ]);

})->middleware('auth:sanctum');


Route::get('/space/{uid}/space-data', function (Request $request, $uid) {
    if (Auth::check()) {
        $spaceMates = SpaceMate::where("space_uid", $uid)->get();
        
        $user_id = Auth::user()->id;
        $found = 0;

        $mateIds = [];

        foreach ($spaceMates as $mate) {
            if ($mate->user_id == $user_id) {
                if ($mate->status == 'active') {
                    $found = true;
                }
            }
            $mateIds[$mate->user_id] = $mate->user_id;
        }

        if(!$found) {
            return("not_a_member");
        }

        $matesData = Mate::whereIn('user_id', $mateIds)
        ->select('id', 'user_id', 'uid', 'name', 'username', 'avatar', 'profile_pic', 'photo')
        ->get();

        foreach ($matesData as $mateData) {
            foreach ($spaceMates as $spaceMate) {
                if ($spaceMate->user_id == $mateData->user_id) {
                    $spaceMate->uid = $mateData->uid;
                    $spaceMate->name = $mateData->name;
                    $spaceMate->username = $mateData->username;
                    $spaceMate->avatar = $mateData->avatar;
                    $spaceMate->profile_pic = $mateData->profile_pic;
                    $spaceMate->photo = $mateData->photo;
                }
            }
        }

        $uniqueMates = [];

        foreach ($spaceMates as $mate) {
            $userId = $mate->user_id;
        
            if (!isset($uniqueMates[$userId])) {
                $uniqueMates[$userId] = $mate;
            } else {
                if ($mate->id > $uniqueMates[$userId]->id) {
                    $uniqueMates[$userId] = $mate;
                }
            }
        }
        
        $spaceMates = [];
        foreach ($uniqueMates as $uniqueMate) {
            $spaceMates[] = $uniqueMate;
        }

        return json_encode([$spaceMates]);
    }
    else {
        return('unauthenticated');
    }
})->middleware('auth:sanctum');


Route::delete('/space/{space_uid}/delete', function (Request $request, $space_uid) {

    $space = Space::where('uid', $space_uid)->first();
    if (!$space) {
        return response()->json(['error' => 'Space not found.'], 404);
    }

    $user_id = $request->user()->id;
    
    if ((int)$space->user_id !== $user_id && $user_id != 1) {
        return response()->json(['error' => 'Unauthorized action.'], 401);
    }

    if ((int)$space->id == 1) {
        return response()->json(['error' => 'Unauthorized action.'], 401);
    }

    // DELETE PHOTOS FROM DB AND LOCAL PHOTO FILES
    if ($space->photo) {
        $photos = Photo::where('space_uid', $space->uid)->get();

        foreach ($photos as $photo) {
            $imageIds = [];
            if (!in_array($photo->id, $imageIds)) {
                $idsToDelete[] = $photo->id;
                $fileNamesToDelete[] = $photo->filename;
            }
        }
    
        // Delete the photos from the database
        if (!empty($idsToDelete)) {
            Photo::whereIn('id', $idsToDelete)->delete();
        }
    
        // Delete photo files
        if (!empty($fileNamesToDelete)) {
            foreach ($fileNamesToDelete as $fileName) {
                // return($fileName);
                $existingPhoto = str_replace('/storage/spaces/', 'public/spaces/', '/storage/spaces/'.$fileName);
                Storage::delete($existingPhoto);
            }
        }
    }
    
    // @TODO: Delete all conversations
    // 

    // Delete all messages
    InboxMateSpace::where('user2', $space->uid)->delete();

    // DELETE ALL SPACE_MATES records
    SpaceMate::where('space_uid', $space->uid)->delete();

    // Delete bookmarks
    Bookmark::where('space_uid', $space->uid)->delete();

    // Delete join requests
    InboxJoinRequest::where('space_uid', $space->uid)->delete();

    // Delete the space
    $space->delete();

    Log::create([
        'user_id' => $user_id,
        'space_uid' => $space_uid,
        'action' => 'space deleted',
    ]);

    return response()->json(['message' => 'Space deleted successfully']);
})->middleware('auth:sanctum');


Route::post('/space/{space_uid}/claim', function (Request $request, $space_uid) {
    
    $user = $request->user();
    $user_id = $user->id;
    $user_email = $user->email;

    $comments = $request->input('comments');

    $space = Space::where('uid', $space_uid)->first();
    if (!$space) {
        $space = Space::where('username', $space_uid)->first();
        $space_uid = $space->uid;
        if (!space) {
            return response()->json('space not found', 404);
        }
    }

    $existing_claim = Claim::where('space_uid', $space_uid)->where('user_id', $user_id)->first();
    if (!empty($existing_claim)) {
        return response()->json('already claimed', 409);
    }

    $to = env('ADMIN_EMAIL');
    $template = 'profile_claimed';
    $extraData = 'extra_data';

    $data = [
        'name' => $space->name,
        'uid' => $space->uid
    ];

    $emailService = new EmailService();
    $emailService->sendEmail($to, 'Profile Claimed', $template, $data);


    $claim = Claim::create([
        'user_id' => $user_id,
        'space_uid' => $space_uid,
        'status' => 'pending',
        'comments' =>  $comments,
    ]);

    Log::create([
        'user_id' => $user_id,
        'space_uid' => $space_uid,
        'action' => 'space claimed',
    ]);

    // Auto-approve if user's email matches the space's email
    if ($space->email === $user_email) {
        $claim->update(['status' => 'approved']);
        Log::create([
            'user_id' => $user_id,
            'space_uid' => $space_uid,
            'action' => 'space claim auto-approved',
        ]);

        $space->user_id = $user_id;
        $space->claimed = 1;
        $space->save();

        // send email
        $to =  $user_email;
        $template = 'profile_claim_approved';

        $data = [
            'name' => $space->name,
            'uid' => $space->uid
        ];

        $emailService = new EmailService();
        $emailService->sendEmail($to, 'Profile Claim Approved', $template, $data);

        return response()->json('approved', 200);
    }

    return response()->json('success', 200);
})->middleware('auth:sanctum');


Route::get('/space/{space_uid}/claim', function (Request $request, $space_uid) {

    $user_id = $request->user()->id;
    $claim = Claim::where('space_uid', $space_uid)->where('user_id', $user_id)->first();

    // return($claim);
    
    if (!empty($claim)) {
        return($claim['status']);
    }
    else {
        return(false);
    }

})->middleware('auth:sanctum');


Route::get('/space/{space_uid}/jr', function (Request $request, $space_uid) {

    $user_id = $request->user()->id;

    $space = Space::where('uid', $space_uid)->first();

    if($space->user_id != $user_id) {
        return response()->json('Wrong user', 403); // Forbidden access
    }
    
    // Query and return data from join_requests table
    $joinRequests = DB::table('join_requests')->where('space_uid', $space_uid)->get();

    // return($claim);
    
    if (!empty($joinRequests)) {
        return($joinRequests);
    }
    else {
        return(false);
    }

})->middleware('auth:sanctum');


Route::post('/space/{uid}/admin/send-invite-email', function($uid) {
    $user_id = Auth::user()->id;

    if ($user_id != 1) {
        return ('error');
    }

    $space = Space::where('uid', $uid)->first();

    // send email
    $to =  $space->email;
    $template = 'claim_invitation';    
    $fromAddress = ['address' => 'info@coliving.app', 'name' => 'Coliving App']; 

    $data = [
        'name' => $space->name,
        'uid' => $uid
    ];

    $emailService = new EmailService();
    $emailService->sendEmail($to, 'Claim Your Profile on Coliving App', $template, $data, $fromAddress);
    
    Log::create([
        'user_id' => $user_id,
        'space_uid' => $uid,
        'action' => 'admin invite email sent',
    ]);

    return('success');

})->middleware('auth:sanctum');


Route::get('/space/{uid}/admin/check-invite-email', function($uid) {
    $user_id = Auth::user()->id;

    if ($user_id != 1) {
        return ('error');
    }

    $count = Log::where('action', 'admin invite email sent')->where('space_uid', $uid)->count();

    return($count);

})->middleware('auth:sanctum');


Route::get('/spaces', [SpaceController::class, 'getCountries']);

Route::get('/spaces/{countrySlug}', [SpaceController::class, 'getSpacesByCountry']);

// Route::get('/cities', [SpaceController::class, 'getCities']);

// Route::get('/cities/{citySlug}', [SpaceController::class, 'getSpacesByCity']);


Route::get('/my-spaces', function(SpaceController $spaceController) {
    $spaces = $spaceController->mySpaces();
    $mate = Mate::where('user_id', auth()->user()->id)->get();
    return (['mySpaces' => $spaces, 'mate' => $mate]); 
})->middleware('auth:sanctum');


Route::get('/hub-all', function(SpaceController $spaceController) {
    $coliving_spaces = $spaceController->myColivingSpaces();
    $mate = Mate::where('user_id', auth()->user()->id)->get();
    return (['myColivingSpaces' => $coliving_spaces[0], 'pastColivingSpaces' => $coliving_spaces[1], 'mate' => $mate]); 
})->middleware('auth:sanctum');


Route::post('/mate', function (Request $request) {
    $user_id = Auth::user()->id;
    
    // Ensure visitedCountries is always an array
    if (!$request->has('visitedCountries') || is_null($request->visitedCountries)) {
        $request->merge(['visitedCountries' => []]);
    } elseif (is_string($request->visitedCountries)) {
        // Convert the comma-separated string to an array if it exists as a string
        $request->merge([
            'visitedCountries' => explode(',', $request->visitedCountries),
        ]);
    }

    $request->validate([
        'photo' => 'image|mimes:jpeg,png,jpg|max:2048', // Validate only for image files with max size 4MB
        'visitedCountries' => 'nullable|array',
        'visitedCountries.*' => 'string|size:2',
    ]);

    $mateData = [
        'username' => (string)$request->username,
        'name' => (string)$request->name,
        
        'avatar' => (string)$request->avatar,
        'profile_pic' => (string)$request->profile_pic,
        
        'orgSpont' => (string)$request->orgSpont,
        'quietLoud' => (string)$request->quietLoud,
        'earlyNight' => (string)$request->earlyNight,
        'workFun' => (string)$request->workFun,
        'expTrad' => (string)$request->expTrad,
        'multiLocal' => (string)$request->multiLocal,

        'twitter' => (string)$request->twitter,
        'facebook' => (string)$request->facebook,
        'instagram' => (string)$request->instagram,
        'youtube' => (string)$request->youtube,
        'tiktok'  => (string)$request->tiktok,
        'pinterest' => (string)$request->pinterest,
        'linkedin' => (string)$request->linkedin,
        'github' => (string)$request->github,
        'substack' => (string)$request->substack,
        'medium' => (string)$request->medium,

        'location_country' => (string)$request->location_country,

        'private' => $request->private,

        'hobbies_interests' => (string)$request->hobbies_interests,
        'visited_countries' => $request->visitedCountries, 
    ];

    // Check if the request has a 'photo' file
    if ($request->hasFile('photo')) {
        // get photo field from mates db
        $mate = Mate::where('user_id', $user_id)->select('photo')->first();

        if ($mate->photo) {
            // Delete the existing photo file
            $existingPhoto = str_replace('/storage/mates/', 'public/mates/', $mate->photo);
            Storage::delete($existingPhoto);
        }

        $photo = $request->file('photo');

        // Generate a unique filename for the uploaded file
        $filename = uniqid() . '.' . $photo->getClientOriginalExtension();

        // Resize and store the uploaded file in the 'public/uploads' directory
        $manager = new Intervention\Image\ImageManager();
        $resizedPhoto = $manager->make($photo)
            ->resize(800, 800, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

        $resizedPhoto->save(storage_path('app/public/mates/' . $filename));

        // Save the file URL in the database
        $mateData['photo'] = '/storage/mates/' . $filename;
    }

    // Update the existing Mate record
    $mate = Mate::where('user_id', $user_id)->first();
    if ($mate) {
        $mate->update($mateData);
    } else {
        // Handle the situation where no record exists (optional)
        return response()->json(['error' => 'No existing Mate found'], 404);
    }

    Log::create([
        'user_id' => $user_id,
        'space_uid' => '',
        'action' => 'mate profile updated',
    ]);

    // return response()->json(['message' => 'Mate created/updated successfully!']);
    $mateService = app(MateService::class);
    $mate = $mateService->getMateWithBookmarks($user_id);
    return ($mate);
})->middleware('auth:sanctum');


Route::get('/mate/{mate}', function ($mate) {

    $m = Mate::where('uid', $mate)->get();
    
    if (sizeof($m) == 0) {
        $m = Mate::where('username', $mate)->get();
    }

    if ($m[0]['private']) {
        if (!auth()->check()) {
            abort(403);
        }

        if ($m[0]['user_id'] !== auth()->user()->id) {
            abort(403);
        }
    }

    $ratings = MatesRating::where('user_id', $m[0]['user_id'])
    ->with(['ratedByUser' => function ($query) {
        $query->select('user_id', 'uid', 'username', 'name', 'profile_pic', 'photo', 'avatar');
    }])
    ->get();

    $current_user_uid = null;
    if (auth()->check()) {
        $current_user_uid = Mate::where('user_id', auth()->user()->id)->value('uid');
    }

    return([$m, $ratings, $current_user_uid]);
});


// RATINGS: MATES

Route::post('/mates/{mate}/ratings', function (Request $request, $mate) { 
    
    $m = Mate::where('uid', $mate)->get();

    if (!$request['reference']) {
        return('referrence missing');
    }

    if ($request['rating'] == 'Positive') {
        $rating = 1;
    }
    else {
        $rating = 0;
    }

    if ($m[0]['user_id'] == Auth::user()->id) {
        return('cannot rate yourself');
    }

    // Check if current user already rated
    $existingRating = MatesRating::where('user_id', $m[0]['user_id'])
    ->where('from_user_id', Auth::user()->id)
    ->first();

    if ($existingRating) {
        return('already rated');
    }

    $rating = new MatesRating([
        'user_id' => $m[0]['user_id'],
        'from_user_id' => Auth::user()->id,
        'rating' => $rating,
        'reference' => strip_tags($request['reference']),
    ]);

    $user_id = $m[0]['user_id'];
    $user = User::with('settingsNotification')->findOrFail($m[0]['user_id']);
    $notificationSettings = $user->settingsNotification;
    // return($notificationSettings);

    if ($notificationSettings->new_review_received) {
        // $user_email = User::where('id', $user_id)->value('email');
        $user_email = $user->email;

        $to = $user_email;
        $subj = 'New review received';
        $template = 'new_review_received_mate';

        $data = [
            // 'name' => $space_name,
            'uid' => $mate,
        ];

        $emailService = new EmailService();
        $emailService->sendEmail($to, $subj, $template, $data);
    }

    Log::create([
        'user_id' => Auth::user()->id,
        'user_uid' => $m[0]['user_id'],
        'space_uid' => '',
        'action' => 'review for mate',
    ]);
    
    $rating->save();

    return('success');

})->middleware('auth:sanctum');


Route::post('/mates/{mate}/ratings/edit', function (Request $request, $mate) { 
    
    $user_id = Auth::user()->id;
    $m = Mate::where('uid', $mate)->get();
    $r = MatesRating::where('user_id', $m[0]['user_id'])->where('from_user_id', $user_id)->get();

    foreach ($r as $rating) {
        $rating->reference = strip_tags($request['reference']);
        $rating->rating = $request['rating'];
        $rating->save();
    }

    Log::create([
        'user_id' => $user_id,
        'user_uid' => $m[0]['user_id'],
        'space_uid' => '',
        'action' => 'review for mate edited',
    ]);

    return($r);

})->middleware('auth:sanctum');


// RATINGS: SPACES

Route::post('/spaces/{space}/ratings', function (Request $request, $space) { 
    
    if (!$request['reference']) {
        return('referrence missing');
    }

    if ($request['rating'] == 'Positive') {
        $rating = 1;
    }
    else {
        $rating = 0;
    }

    $user_id = Auth::user()->id;

    // Check if current user already rated
    $existingRating = SpacesRating::where('space_uid', $space)
    ->where('from_user_id', $user_id)
    ->first();

    if ($existingRating) {
        return('already rated');
    }

    $rating = new SpacesRating([
        'space_uid' => $space,
        'from_user_id' => $user_id,
        'rating' => $rating,
        'reference' => strip_tags($request['reference']),
    ]);


// @TODO: like below for /mate:

// $user_id = $m[0]['user_id'];
// $user = User::with('settingsNotification')->findOrFail($m[0]['user_id']);
// $notificationSettings = $user->settingsNotification;
// // return($notificationSettings);

// if ($notificationSettings->new_review_received) {
    // $user_email = User::where('id', $user_id)->value('email');
    // $user_email = $user->email;

    // get space for email, name and user_id
    $space_uid = $space;
    $space = Space::where('uid', $space)->select('name', 'user_id', 'email')->first();

    if (!$space->email) {
        $email = User::where('id', $space->user_id)->value('email');
    }
    else {
        $email = $space->email;
    }

    $to = $email;
    $subj = 'New review received';
    $template = 'new_review_received_space';

    $data = [
        'name' => $space->name,
        'uid' => $space_uid,
    ];

    $emailService = new EmailService();
    $emailService->sendEmail($to, $subj, $template, $data);
// }


    Log::create([
        'user_id' => Auth::user()->id,
        'space_uid' => $space,
        'action' => 'review for space',
    ]);
    
    $rating->save();

    return('success');

})->middleware('auth:sanctum');


Route::post('/spaces/{space}/ratings/edit', function (Request $request, $space) { 
    
    $user_id = Auth::user()->id;
    $r = SpacesRating::where('space_uid', $space)->where('from_user_id', $user_id)->get();

    foreach ($r as $rating) {
        $rating->reference = strip_tags($request['reference']);                                
        $rating->rating = $request['rating'];
        $rating->save();
    }

    Log::create([
        'user_id' => $user_id,
        'space_uid' => $space,
        'action' => 'review for space edited',
    ]);

    return($r);

})->middleware('auth:sanctum');


Route::get('/search/', function (SpaceController $spaceController) {
    return $spaceController->searchData();
});


Route::post('/join_requests/{uid}', [InboxJoinRequestController::class, 'store'])->middleware('auth:sanctum');

Route::post('/join_requests/{uid}/accept', [InboxJoinRequestController::class, 'accept'])->middleware('auth:sanctum');

Route::post('/join_requests/{id}/{mate_uid}/decline', [InboxJoinRequestController::class, 'decline'])->middleware('auth:sanctum');


// Route::post('/conversations/{uid}', [InboxConversationController::class, 'store']);
// Route::get('/conversations/{uid}', [InboxConversationController::class, 'showConversation']);

Route::get('/conversations', [InboxConversationController::class, 'showConversations'])->middleware('auth:sanctum');


Route::get('/conversation/mate/{uid}', [InboxMateMateController::class, 'showConversation'])->middleware('auth:sanctum');

Route::post('/conversation/mate/{uid}', [InboxMateMateController::class, 'store'])->middleware('auth:sanctum');

Route::get('/conversation/space/{uid}', [InboxMateSpaceController::class, 'showConversation'])->middleware('auth:sanctum');

Route::post('/conversation/space/{uid}', [InboxMateSpaceController::class, 'store'])->middleware('auth:sanctum');


Route::get('/conversation/space/{space_uid}/mate/{mate_uid}', [InboxMateSpaceController::class, 'showConversationSpacePOV'])->middleware('auth:sanctum');

Route::post('/conversation/space/{space_uid}/mate/{mate_uid}', [InboxMateSpaceController::class, 'storeSpacePOV'])->middleware('auth:sanctum');


Route::get('/conversation/hub/{uid}', [InboxHubController::class, 'showConversation'])->middleware('auth:sanctum');

Route::post('/conversation/hub/{uid}', [InboxHubController::class, 'store'])->middleware('auth:sanctum');


Route::get('/my-bookmarks', function() {
    $user_id = Auth::user()->id;
    $bookmarks = Bookmark::where('user_id', $user_id)->select('space_uid', 'created_at')->get();

    foreach ($bookmarks as $bookmark) {
        $space = Space::where('uid', $bookmark->space_uid)->select('name', 'longitude', 'country', 'country_code', 'latitude')->first();
        if ($space) {
            $bookmark->name = $space->name;
            $bookmark->latitude = $space->latitude;
            $bookmark->longitude = $space->longitude;
            $bookmark->country = $space->country;
            $bookmark->country_code = $space->country_code;
    
            $photo = Space::where('uid', $bookmark->space_uid)->select('photo')->get();
    
            if ($photo) {
                $bookmark->photo = true;
                $latestPhoto = Photo::where('space_uid', $bookmark->space_uid)
                    ->orderBy('created_at', 'desc')
                    ->select('filename')
                    ->first();
                    
                if ($latestPhoto) {
                    $latestPhoto = $latestPhoto->filename;
                } else {
                    $latestPhoto = false;
                }
            } else {
                $bookmark->photo = false;
                $latestPhoto = false;
            }
    
            $bookmark->first_photo = $latestPhoto;
        }
    }

    return $bookmarks;
})->middleware('auth:sanctum');


Route::post('/bookmark/{uid}', function($uid) {
    $user_id = Auth::user()->id;

    $bookmark_existing = Bookmark::where('user_id', $user_id)->where('space_uid', $uid)->first();

    if (empty($bookmark_existing)) {
        $bookmark = new Bookmark();
        $bookmark->space_uid = $uid;
        $bookmark->user_id = $user_id;
        $bookmark->save();
        return('created');
    }
    else {
        $bookmark_existing->delete();
        return('deleted');
    }
})->middleware('auth:sanctum');


// ACCOUNT FEEDBACK
Route::middleware(['auth:sanctum', 'verified'])->post('/feedback', function (Request $request) {
    $id_user = Auth::user()->id;
    $feedback = htmlspecialchars($request['feedback']);

    // return('f:'.$feedback);

    if (DB::table('feedback')->insert(array('message' => $feedback, 'user_id' => $id_user)) === FALSE) {
        echo('error');
    }
    else {
        echo('success');
    }

    $user_email = DB::table('users')->where('id', $id_user)->value('email');
    
    // EMAIL NOTICE TO ADMIN
    $message = "User ID: " . $id_user . "\nUser email: " . $user_email . "\n\n" . $feedback;
    Mail::raw($message, function ($message) use ($id_user) {
        $message->subject("New Feedback Received: " . $id_user)->to(env('ADMIN_EMAIL'));
    });

    exit();
})->middleware('auth:sanctum');
