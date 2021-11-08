<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if (
            $exception instanceof ForumDiscussion
            || $exception instanceof ForumResponse
        ) {
            return redirect()->back()->with('alert-danger', $exception->getMessage());
        }

        if ($exception instanceof AfterForumResponse) {
            return redirect()->back()->with('alert-success', trans('chatter::alert.success.reason.submitted_to_post'));
        }

        $context = ['type' => get_class($exception)];

        if ($exception instanceof NotFoundHttpException) {
            $monolog = Log::getMonolog();
            $monolog->pushHandler(new StreamHandler(storage_path('logs/info.log'), Logger::INFO, false));
            $monolog->info('Path not found; User ip: '.$request->ip().'; Url: '.$request->getPathInfo());
        } else {
            Log::error(
                $exception->getMessage(),
                array_merge($context, ['stack_trace' => $exception->getTraceAsString()])
            );
        }

        return parent::render($request, $exception);
    }
}
