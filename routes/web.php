<?php

use Illuminate\Support\Facades\Route;

use App\Models\Space;
use App\Models\Mate;
use App\Http\Controllers\SpaceController;

use App\Services\MateService;

// AUTH
require __DIR__.'/web_auth.php';

// ADMIN DASHBOARD
if (file_exists(__DIR__.'/web_admin.php')) {
    require __DIR__.'/web_admin.php';
}

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function (SpaceController $spaceController) {
    $meta = [];
    $meta['title'] = "Coliving App - Experience the Coliving Revolution";
    // $meta['title'] = "Coliving App - Shared Spaces, Rooms for Rent, Affordable Housing";
    $meta['description'] = "";
    $meta['soc_title'] = "Coliving App";
    $meta['soc_description'] = "Coliving App helps you find coliving spaces and mates that match your lifestyle, turning shared living into meaningful community.";
    $meta['image'] = "thumb-world-3-small.png?v=001";
    // $meta['image_w'] = 3800;
    // $meta['image_h'] = 1830;
    $meta['image_w'] = 1900;
    $meta['image_h'] = 915;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', ['mate' => $mate, 'meta' => $meta]);
    }
    else {
        return view('welcome', ['meta' => $meta]);
    }
})->name('home');


Route::get('/mate/update', function () {
    $meta = [];
    $meta['title'] = "Coliving App";
    $meta['description'] = "";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-disco.png";
    $meta['image_w'] = 1270;
    $meta['image_h'] = 760;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', ['meta' => $meta, 'mate' => $mate]);
    }
    else {
        return view('welcome', ['meta' => $meta]);
    }
})->middleware(['auth:sanctum']);


Route::get('/space/new', function () {
    $meta = [];
    $meta['title'] = "Coliving App";
    $meta['description'] = "";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-world-2-small.png?v=001";
    $meta['image_w'] = 1900;
    $meta['image_h'] = 915;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', ['meta' => $meta, 'mate' => $mate]);
    }
    else {
        return view('welcome', ['meta' => $meta]);
    }
});


Route::get('/mate/{username}', function ($username) {
    $meta = [];
    $meta['title'] = "Coliving Mate - " . $username;
    $meta['description'] = "";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-disco.png";
    $meta['image_w'] = 1270;
    $meta['image_h'] = 760;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', ['meta' => $meta, 'mate' => $mate]);
    }
    else {
        return view('welcome', ['meta' => $meta]);
    }
})->name('mate');


Route::get('/space/{uid}/edit', function ($uid) {
    $meta = [];
    $meta['title'] = "Coliving App";
    $meta['description'] = "";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-disco.png";
    $meta['image_w'] = 1270;
    $meta['image_h'] = 760;

    if (auth()->check()) {
        $space = Space::where('uid', $uid)->first();
        $user_id = auth()->user()->id;
        
        if ((int)$space->user_id !== $user_id && $user_id != 1) {
            abort(403);
        }
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', ['meta' => $meta, 'mate' => $mate]);
    }
    else {
        abort(403);
    }
})->middleware(['auth:sanctum'])->name('space.edit');


Route::get('/space/{uid}/dashboard', function ($uid) {
    $meta = [];
    $meta['title'] = "Coliving App";
    $meta['description'] = "";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-disco.png";
    $meta['image_w'] = 1270;
    $meta['image_h'] = 760;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        $space = Space::where('uid', $uid)->where('user_id', $user_id)->first();
        if(!$space) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }
        return view('welcome', ['meta' => $meta, 'mate' => $mate]);
    }
    else {
        return response()->json(['error' => 'Unauthenticated.'], 401);
    }
})->middleware(['auth:sanctum'])->name('space.dashboard');


Route::get('/space/{uid}/claim', function ($uid) {
    $meta = [];
    $meta['title'] = "Coliving App";
    $meta['description'] = "";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-disco.png";
    $meta['image_w'] = 1270;
    $meta['image_h'] = 760;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', ['meta' => $meta, 'mate' => $mate]);
    }
    else {
        return view('welcome', ['meta' => $meta]);
    }
})->middleware(['auth:sanctum'])->name('space.claim');


Route::get('/space/{uid}', function ($uid) {
    $spaceController = new SpaceController();
    $spaceData = $spaceController->show($uid);

    $meta = [];
    $meta['title'] = $spaceData['space']['name'] . " - Coliving App";
    $description = $spaceData['space']['description'];
    if (strlen($description) > 170) {
        $description = substr($spaceData['space']['description'], 0, 170) . '...';
    }
    $meta['description'] = $description;
    $meta['soc_title'] = $spaceData['space']['name'];
    $meta['soc_description'] = $description;
    if ($spaceData['space']['photo']) {
        $meta['image'] = $spaceData['space']['first_photo'];
    }   
    else {
        $meta['image'] = "thumb-disco.png";
        $meta['image_w'] = 1270;
        $meta['image_h'] = 760;
    } 

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);

        return view('welcome', ['meta' => $meta, 'mate' => $mate, 'space' => $spaceData]);
    }
    else {
        return view('welcome', ['meta' => $meta, 'space' => $spaceData]);
    }
})->name('space.show');


Route::get('/mates', function () {
    $meta = [];
    $meta['title'] = "Coliving App - Mates";
    $meta['description'] = "Whether you're a digital nomad, remote worker, student, or just a traveler, create a free profile on Coliving App to showcase your visited countries, find coliving mates nearby, and connect with the coliving community.";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-world-2-small.png?v=002";
    $meta['image_w'] = 1900;
    $meta['image_h'] = 915;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', ['meta' => $meta, 'mate' => $mate]);
    }
    else {
        return view('welcome', ['meta' => $meta]);
    }
});


Route::get('/space/{uid}/hub', function () {
    $meta = [];
    $meta['title'] = "Coliving App";
    $meta['description'] = "";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-disco.png";
    $meta['image_w'] = 1270;
    $meta['image_h'] = 760;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', ['meta' => $meta, 'mate' => $mate]);
    }
    else {
        return view('welcome', ['meta' => $meta]);
    }
})->middleware(['auth:sanctum']);


Route::get('/space/{uid}/hub/chat', function () {
    $meta = [];
    $meta['title'] = "Coliving App";
    $meta['description'] = "";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-disco.png";
    $meta['image_w'] = 1270;
    $meta['image_h'] = 760;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', ['meta' => $meta, 'mate' => $mate]);
    }
    else {
        return view('welcome', ['meta' => $meta]);
    }
})->middleware(['auth:sanctum']);


Route::get('/space/{uid}/hub/mates', function () {
    $meta = [];
    $meta['title'] = "Coliving App";
    $meta['description'] = "";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-disco.png";
    $meta['image_w'] = 1270;
    $meta['image_h'] = 760;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', ['meta' => $meta, 'mate' => $mate]);
    }
    else {
        return view('welcome', ['meta' => $meta]);
    }
})->middleware(['auth:sanctum']);


Route::get('/space/{uid}/hub/settings', function () {
    $meta = [];
    $meta['title'] = "Coliving App";
    $meta['description'] = "";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-disco.png";
    $meta['image_w'] = 1270;
    $meta['image_h'] = 760;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', ['meta' => $meta, 'mate' => $mate]);
    }
    else {
        return view('welcome', ['meta' => $meta]);
    }
})->middleware(['auth:sanctum']);


Route::get('/spaces', function () {
    // Query the spaces table for distinct countries and country slugs
    $countries = Space::select('country', 'country_slug')->distinct()->get();

    $meta = [];
    // $meta['title'] = "Global Coliving Spaces, Shared Apartments - Coliving App";
    $meta['title'] = "Coliving App - Spaces";
    $meta['description'] = "Discover your perfect blend of community, comfort, and connection as you explore coliving spaces on Coliving App.";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-world-2-small.png?v=002";
    $meta['image_w'] = 1900;
    $meta['image_h'] = 915;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', ['meta' => $meta, 'mate' => $mate, 'countries' => $countries]);
    }
    else {
        return view('welcome', ['meta' => $meta, 'countries' => $countries]);
    }
});


// Route::get('/spaces/cities', function () {
//     // Query the spaces table for distinct countries and country slugs
//     $countries = Space::select('city', 'city_slug')->distinct()->get();

//     $meta = [];
//     $meta['title'] = "Worldwide Coliving Spaces - Coliving App";
//     $meta['description'] = "";
//     $meta['soc_title'] = "";
//     $meta['soc_description'] = "";
//     $meta['image'] = "thumb-disco.png";
//     $meta['image_w'] = 1270;
//     $meta['image_h'] = 760;

//     if (auth()->check()) {
//         $user_id = auth()->user()->id;
//         $mateService = app(MateService::class);
//         $mate = $mateService->getMateWithBookmarks($user_id);
//         return view('welcome', ['meta' => $meta, 'mate' => $mate, 'countries' => $countries]);
//     }
//     else {
//         return view('welcome', ['meta' => $meta, 'countries' => $countries]);
//     }
// });


// Route::get('/spaces/{country}/{city}', function ($country_slug, $city_slug) {
//     $spaces = Space::where('city_slug', $city_slug)->get();
//     $city = Space::where('city_slug', $city_slug)->select('city')->first();

//     $meta = [];
//     $meta['title'] = "Coliving Spaces in ". $city->city ." - Coliving App";
//     $meta['description'] = "";
//     $meta['soc_title'] = "";
//     $meta['soc_description'] = "";
//     $meta['image'] = "thumb-disco.png";
//     $meta['image_w'] = 1270;
//     $meta['image_h'] = 760;

//     if (auth()->check()) {
//         $user_id = auth()->user()->id;
//         $mateService = app(MateService::class);
//         $mate = $mateService->getMateWithBookmarks($user_id);
//         return view('welcome', ['meta' => $meta, 'mate' => $mate, 'spaces' => $spaces]);
//     }
//     else {
//         return view('welcome', ['meta' => $meta, 'spaces' => $spaces]);
//     }
// })->name('spaces.show');


Route::get('/spaces/{country}', function ($country_slug) {
    $spaces = Space::where('country_slug', $country_slug)->get();
    $countries = Space::select('country', 'country_slug')->distinct()->get();

    $country = Space::where('country_slug', $country_slug)->select('country')->first();

    $meta = [];
    $meta['title'] = "Coliving Spaces in ". $country->country ." - Coliving App";
    $meta['description'] = "";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-world-2-small.png?v=002";
    $meta['image_w'] = 1900;
    $meta['image_h'] = 915;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', ['meta' => $meta, 'mate' => $mate, 'spaces' => $spaces, 'countries' => $countries]);
    }
    else {
        return view('welcome', ['meta' => $meta, 'spaces' => $spaces, 'countries' => $countries]);
    }
})->name('spaces.show');


Route::get('/my-spaces', function (SpaceController $spaceController) {
    $meta = [];
    $meta['title'] = "Coliving App";
    $meta['description'] = "";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-disco.png";
    $meta['image_w'] = 1270;
    $meta['image_h'] = 760;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        $spaces = $spaceController->mySpaces();
        $coliving_spaces = $spaceController->myColivingSpaces();
        return view('welcome', ['meta' => $meta, 'mySpaces' => $spaces, 'myColivingSpaces' => $coliving_spaces[0], 'pastColivingSpaces' => $coliving_spaces[1], 'mate' => $mate]);
    }
    else {
        return view('welcome', ['meta' => $meta]);
    }
})->middleware(['auth:sanctum']);


Route::get('/search/{search_term}', function (SpaceController $spaceController) {
    $meta = [];
    $meta['title'] = "Coliving App - Search";
    $meta['description'] = "";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-disco.png";
    $meta['image_w'] = 1270;
    $meta['image_h'] = 760;

    $spaces = $spaceController->searchData();

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', compact('meta', 'mate', 'spaces'));
    }
    else {
        return view('welcome', compact('meta', 'spaces'));
    }
});


Route::get('/about', function (SpaceController $spaceController) {
    $meta = [];
    $meta['title'] = "Coliving App";
    $meta['description'] = "";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-disco.png";
    $meta['image_w'] = 1270;
    $meta['image_h'] = 760;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', ['meta' => $meta, 'mate' => $mate]);
    }
    else {
        return view('welcome', ['meta' => $meta]);
    }
})->name('about');


Route::get('/privacy', function (SpaceController $spaceController) {
    $meta = [];
    $meta['title'] = "Coliving App";
    $meta['description'] = "";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-disco.png";
    $meta['image_w'] = 1270;
    $meta['image_h'] = 760;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', ['meta' => $meta, 'mate' => $mate]);
    }
    else {
        return view('welcome', ['meta' => $meta]);
    }
})->name('privacy');


Route::get('/terms', function (SpaceController $spaceController) {
    $meta = [];
    $meta['title'] = "Coliving App";
    $meta['description'] = "";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-disco.png";
    $meta['image_w'] = 1270;
    $meta['image_h'] = 760;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', ['meta' => $meta, 'mate' => $mate]);
    }
    else {
        return view('welcome', ['meta' => $meta]);
    }
})->name('terms');


Route::get('/contacts', function (SpaceController $spaceController) {
    $meta = [];
    $meta['title'] = "Coliving App";
    $meta['description'] = "";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-disco.png";
    $meta['image_w'] = 1270;
    $meta['image_h'] = 760;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', ['meta' => $meta, 'mate' => $mate]);
    }
    else {
        return view('welcome', ['meta' => $meta]);
    }
})->name('contacts');


Route::get('/inbox', function () {
    $meta = [];
    $meta['title'] = "Coliving App - Inbox";
    $meta['description'] = "";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-world-2-small.png?v=001";
    $meta['image_w'] = 1900;
    $meta['image_h'] = 915;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', ['meta' => $meta, 'mate' => $mate]);
    }
    else {
        return view('welcome', ['meta' => $meta]);
    }
})->name('inbox');


Route::get('/inbox/my-spaces', function () {
    $meta = [];
    $meta['title'] = "Coliving App - Inbox | My Spaces";
    $meta['description'] = "";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-world-2-small.png?v=001";
    $meta['image_w'] = 1900;
    $meta['image_h'] = 915;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', ['meta' => $meta, 'mate' => $mate]);
    }
    else {
        return view('welcome', ['meta' => $meta]);
    }
})->name('inboxMySpaces');


Route::get('/inbox/space/{uid}', function () {
    $meta = [];
    $meta['title'] = "Coliving App - Search";
    $meta['description'] = "";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-disco.png";
    $meta['image_w'] = 1270;
    $meta['image_h'] = 760;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', ['meta' => $meta, 'mate' => $mate]);
    }
    else {
        return view('welcome', ['meta' => $meta]);
    }
})->middleware(['auth:sanctum']);


Route::get('/inbox/mate/{uid}', function () {
    $meta = [];
    $meta['title'] = "Coliving App";
    $meta['description'] = "";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-disco.png";
    $meta['image_w'] = 1270;
    $meta['image_h'] = 760;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', ['meta' => $meta, 'mate' => $mate]);
    }
    else {
        return view('welcome', ['meta' => $meta]);
    }
})->middleware(['auth:sanctum']);


Route::get('/inbox/space/{space_uid}/mate/{mate_uid}', function () {
    $meta = [];
    $meta['title'] = "Coliving App - Search";
    $meta['description'] = "";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-disco.png";
    $meta['image_w'] = 1270;
    $meta['image_h'] = 760;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', ['meta' => $meta, 'mate' => $mate]);
    }
    else {
        return view('welcome', ['meta' => $meta]);
    }
})->middleware(['auth:sanctum']);


Route::get('/bookmarks', function () {
    $meta = [];
    $meta['title'] = "Coliving App";
    $meta['description'] = "";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-disco.png";
    $meta['image_w'] = 1270;
    $meta['image_h'] = 760;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', ['meta' => $meta, 'mate' => $mate]);
    }
    else {
        return view('welcome', ['meta' => $meta]);
    }
})->middleware(['auth:sanctum'])->name('bookmarks');


Route::get('/hub', function () {
    $meta = [];
    $meta['title'] = "Coliving App";
    $meta['description'] = "";
    $meta['soc_title'] = "";
    $meta['soc_description'] = "";
    $meta['image'] = "thumb-disco.png";
    $meta['image_w'] = 1270;
    $meta['image_h'] = 760;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mateService = app(MateService::class);
        $mate = $mateService->getMateWithBookmarks($user_id);
        return view('welcome', ['meta' => $meta, 'mate' => $mate]);
    }
    else {
        return view('welcome', ['meta' => $meta]);
    }
})->name('hub');
