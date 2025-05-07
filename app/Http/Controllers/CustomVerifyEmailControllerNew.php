<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\User;

class CustomVerifyEmailControllerNew extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        // Find the user by the given id in the URL
        $user = User::findOrFail($request->route('id'));

        // If the user is already verified, return early.
        if ($user->hasVerifiedEmail()) {
            return redirect($this->redirectPath())->with('verified', true);
        }

        // If the URL signature is invalid, throw an exception.
        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            throw new \Illuminate\Auth\Access\AuthorizationException;
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect($this->redirectPath())->with('verified', true);
    }

    /**
     * Get the post-register / post-email verification redirect path.
     *
     * @return string
     */
    protected function redirectPath()
    {
        // Specify your custom redirect path here
        return '/';
    }
}
