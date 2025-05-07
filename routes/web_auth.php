<?php

use App\Models\GetAppData;
use App\Models\User;
use App\Models\Mate;

use App\Models\SpaceMate;
use App\Models\Log;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\CustomVerifyEmailControllerNew;

use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;

/*
|--------------------------------------------------------------------------
| AUTH Routes
|--------------------------------------------------------------------------
*/


Route::get('/account', function (Request $request) {
    $meta = [];
    $meta['title'] = "Coliving App - My Account";
    $meta['description'] = "";
    $meta['soc_title'] = "Coliving App";
    $meta['soc_description'] = "Access Your Account Settings";
    $meta['image'] = "thumb-main.png";
    $meta['image_w'] = 1200;
    $meta['image_h'] = 1200;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mate = Mate::where('user_id', $user_id)->get();
        return view('welcome', ['meta' => $meta, 'mate' => $mate]);
    }
    else {
        return view('welcome', ['meta' => $meta]);
    }
})->name('account');


Route::get('/account/settings', function (Request $request) {
    $meta = [];
    $meta['title'] = "Coliving App - My Account";
    $meta['description'] = "";
    $meta['soc_title'] = "Coliving App";
    $meta['soc_description'] = "Access Your Account Settings";
    $meta['image'] = "thumb-main.png";
    $meta['image_w'] = 1200;
    $meta['image_h'] = 1200;

    if (auth()->check()) {
        $user_id = auth()->user()->id;
        $mate = Mate::where('user_id', $user_id)->get();
        return view('welcome', ['meta' => $meta, 'mate' => $mate]);
    }
    else {
        return view('welcome', ['meta' => $meta]);
    }
})->name('account.settings');


Route::get('/login', function (Request $request) {
    $meta = [];
    $meta['title'] = "Coliving App - Login";
    $meta['description'] = "Log in to your Coliving App account.";
    $meta['soc_title'] = "Coliving App";
    $meta['soc_description'] = "Log in to your Coliving App account.";
    $meta['image'] = "thumb-main.png";
    $meta['image_w'] = 1200;
    $meta['image_h'] = 1200;

    return view('welcome', ['meta' => $meta]);
})->name('login');


Route::get('/register', function (Request $request) {
    $meta = [];
    $meta['title'] = "Coliving App - Sign Up for a Free Account";
    $meta['description'] = "";
    $meta['soc_title'] = "Coliving App";
    $meta['soc_description'] = "Sign Up for a Free Account";
    $meta['image'] = "thumb-main.png";
    $meta['image_w'] = 1200;
    $meta['image_h'] = 1200;

    return view('welcome', ['meta' => $meta]);
})->name('register');


Route::get('/email/verify', function (Request $request) {
    $meta = [];
    $meta['title'] = "Coliving App - Verify Your Email Address";
    $meta['description'] = "";
    $meta['soc_title'] = "Coliving App";
    $meta['soc_description'] = "Verify Your Email Address";
    $meta['image'] = "thumb-main.png";
    $meta['image_w'] = 1200;
    $meta['image_h'] = 1200;

    return view('welcome', ['meta' => $meta]);
});


$verificationLimiter = config('fortify.limiters.verification', '6,1');

Route::get('/email/verify/{id}/{hash}', [CustomVerifyEmailControllerNew::class, '__invoke'])
    ->middleware(['signed', 'throttle:' . $verificationLimiter])
    ->name('verification.verify');


Route::get('password/reset/{token}', function() {
    $meta = [];
    $meta['title'] = "Coliving App - Reset Password";
    $meta['description'] = "";
    $meta['soc_title'] = "Coliving App";
    $meta['soc_description'] = "Reset Password";
    $meta['image'] = "thumb-main.png";
    $meta['image_w'] = 1200;
    $meta['image_h'] = 1200;

    return view('welcome', ['meta' => $meta]);
})->name('password.reset');


Route::get('/forgot-password', function (Request $request) {
    $meta = [];
    $meta['title'] = "Coliving App - Password Recovery";
    $meta['description'] = "";
    $meta['soc_title'] = "Coliving App";
    $meta['soc_description'] = "Password Recovery";
    $meta['image'] = "thumb-main.png";
    $meta['image_w'] = 1200;
    $meta['image_h'] = 1200;

    return view('welcome', ['meta' => $meta]);
})->name('forgotPassword');


Route::middleware('auth:sanctum')->post('/user/delete', function (Request $request) {
    $id_user = Auth::user()->id;
    // $user_id = $request->input('user_id', $user_id);
    $user_id = $request['user_id'];
    $type = false;
    
    $user = User::find($id_user);

    // return($request['user_id']);

    if ($request['user_id'] && $id_user == 1) {
        // delete user as admin
        $user = User::find($user_id);
        $id_user = $user_id;
        $type = 'admin';
        // return($user);
    }
    else if (Hash::check($request['password'], $user->password)) {
        $type = 'user'; 
    }
    else {
        return('Wrong password');
    }
    // return($user);

    if ($type) {
        $user_id = $id_user;

        $spaces = SpaceMate::where('user_id', $user_id)->where('status', 'active')->get();

        foreach ($spaces as $space) {
            $uid = $space->space_uid;

            // $response = $chatsController->userLeft($user_id, $uid);

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
        }

        $photo = DB::table('mates')->where('user_id', $id_user)->value('photo');

        // Delete the photo from storage
        if ($photo && Storage::exists($photo)) {
            Storage::delete($photo);
        }

        // Delete additional user data
        DB::table('mates')->where('user_id', $id_user)->delete();
        DB::table('bookmarks')->where('user_id', $id_user)->delete();

        if ($type == 'user') {
            // Log out user
            Session::flush();
        }

        Log::create([
            'user_id' => $user_id,
            'space_uid' => '',
            'action' => 'user acc deleted',
        ]);

        // Delete user
        if ($user->delete()) {
            echo('success');
        }
    }
});
