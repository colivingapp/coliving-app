<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
            if ($exception->getStatusCode() == 403) {
                // Check if it's due to an expired email verification link
                if ($request->is('email/verify/*')) {
                    // Additional check if the user is already verified
                    if (auth()->check() && auth()->user()->hasVerifiedEmail()) {
                        return redirect('/');
                    }
                    return redirect('/email/verify?expired=true');
                }
            }
        }
    
        return parent::render($request, $exception);
    }

}
