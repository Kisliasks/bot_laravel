<?php

namespace App\Exceptions;

use App\Helpers\Telegram;
use Illuminate\Container\Container;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Http;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
     *
     * @return void
     */

    //  protected $telegram;
    //  public function __construct(Container $container, Telegram $telegram)
    //  {
    //     parent::__construct($container);
    //     $this->telegram = $telegram; 
    //  }

    

    // public function report(Throwable $e) {
    //     $data = [
    //         'description' => $e->getMessage(),
    //         'file' => $e->getFile(),
    //         'line' => $e->getLine(),

    //     ];

        
    //     $this->telegram->sendMessage(env('REPORT_TELEGRAM_ID'), (string)view('report', $data));
    // }


    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
