<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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

    /**
     * Render an exception into an HTTP response.
     *
     */
    // public function render($request, Throwable $e)
    // {
    //     $response = parent::render($request, $e);
        
    //     if ($e instanceof ValidationException) 
    //         return $response;
    
    //     Log::channel('slack')->critical($e->getMessage());
    //     return redirect()->back()->withErrors([
    //         'status' => $response->getStatusCode(),
    //         'messageError' => 'Oops...! hubo un Error!',
    //     ])->withInput();
            
    // }
}
