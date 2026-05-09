<?php

namespace App\Exceptions;

use App\Models\ErrorLog;
use App\Services\ErrorAlertingService;
use App\Services\ErrorContextEnricher;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Foundation\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class CustomExceptionHandler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom messages.
     *
     * @var array<int, class-string<Throwable>, string>
     */
    protected $internalExceptions = [
        AuthenticationException::class => 'Unauthenticated.',
        ValidationException::class => 'Validation failed.',
        NotFoundHttpException::class => 'Page not found.',
    ];

    /**
     * Register the exception handling callbacks for the application.
     * Note: Reporting/logging to DB is handled via bootstrap/app.php withExceptions callback
     * to avoid infinite recursion. Only rendering customizations are registered here.
     */
    public function register(): void
    {
        // Reporting is intentionally moved to bootstrap/app.php withExceptions
        // to avoid infinite recursion caused by double-calling parent::report()
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // Handle specific exception types with custom responses
        if ($e instanceof AllModelsUnavailableException) {
            return $this->handleAllModelsUnavailable($request, $e);
        }

        if ($e instanceof AllProvidersUnavailableException) {
            return $this->handleAllProvidersUnavailable($request, $e);
        }

        if ($e instanceof InsufficientPlanException) {
            return $this->handleInsufficientPlan($request, $e);
        }

        if ($e instanceof ModelNotFoundException) {
            return $this->handleModelNotFound($request, $e);
        }

        if ($e instanceof ValidationException) {
            return $this->handleValidationException($request, $e);
        }

        if ($e instanceof NotFoundHttpException) {
            return $this->handleNotFound($request, $e);
        }

        if ($e instanceof AuthenticationException) {
            return $this->handleAuthenticationException($request, $e);
        }

        // Default rendering
        return parent::render($request, $e);
    }

    /**
     * Log exception to database with enriched context
     */
    protected function logToDatabase(Throwable $e): void
    {
        try {
            $level = $this->determineLogLevel($e);

            ErrorContextEnricher::logToDatabase(
                exception: $e,
                level: $level,
                type: 'exception'
            );
        } catch (Throwable $logError) {
            // Prevent infinite loop if logging fails
            Log::error('Failed to log exception to database', [
                'original_exception' => get_class($e),
                'log_error' => $logError->getMessage(),
            ]);
        }
    }

    /**
     * Send alert for critical errors
     */
    protected function sendAlert(Throwable $e): void
    {
        try {
            // Find the most recent error log for this exception
            $errorLog = ErrorLog::where('exception_class', get_class($e))
                ->where('message', $e->getMessage())
                ->orderBy('created_at', 'desc')
                ->first();

            if ($errorLog && ! $errorLog->notified) {
                $alertingService = app(ErrorAlertingService::class);
                $alertingService->sendAlert($errorLog);
            }
        } catch (Throwable $alertError) {
            Log::error('Failed to send error alert', [
                'original_exception' => get_class($e),
                'alert_error' => $alertError->getMessage(),
            ]);
        }
    }

    /**
     * Determine if an error is critical and should trigger an alert
     */
    protected function isCriticalError(Throwable $e): bool
    {
        $criticalExceptions = [
            \Error::class,
            \ParseError::class,
            \TypeError::class,
            \ErrorException::class,
            HttpExceptionInterface::class,
        ];

        foreach ($criticalExceptions as $criticalClass) {
            if ($e instanceof $criticalClass) {
                return true;
            }
        }

        // Check if it's a 5xx HTTP error
        if ($e instanceof HttpExceptionInterface) {
            $statusCode = $e->getStatusCode();

            return $statusCode >= 500;
        }

        return false;
    }

    /**
     * Determine the log level for an exception
     */
    protected function determineLogLevel(Throwable $e): string
    {
        // Critical errors that require immediate attention
        if ($e instanceof \Error || $e instanceof \ParseError) {
            return 'emergency';
        }

        // Server errors
        if ($e instanceof HttpExceptionInterface && $e->getStatusCode() >= 500) {
            return match (true) {
                $e->getStatusCode() >= 503 => 'critical',
                $e->getStatusCode() >= 502 => 'alert',
                default => 'error',
            };
        }

        // Client errors - don't log as severely
        if ($e instanceof HttpExceptionInterface && $e->getStatusCode() >= 400) {
            return 'warning';
        }

        // Default to error level
        return 'error';
    }

    /**
     * Handle AllModelsUnavailableException — all Gemini fallback models are unavailable.
     * Requirements: 9.1
     */
    protected function handleAllModelsUnavailable(Request $request, AllModelsUnavailableException $e)
    {
        Log::warning('All AI models unavailable', [
            'url' => $request->fullUrl(),
            'tenant_id' => auth()->check() ? auth()->user()->tenant_id : null,
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Layanan AI sedang tidak tersedia. Silakan coba beberapa saat lagi.',
        ], 503);
    }

    /**
     * Handle AllProvidersUnavailableException — all AI providers in the fallback chain are unavailable.
     * Requirements: 9.1, 9.2
     */
    protected function handleAllProvidersUnavailable(Request $request, AllProvidersUnavailableException $e)
    {
        Log::warning('All AI providers unavailable', [
            'url' => $request->fullUrl(),
            'tenant_id' => auth()->check() ? auth()->user()->tenant_id : null,
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Layanan AI sedang tidak tersedia. Silakan coba beberapa saat lagi.',
        ], 503);
    }

    /**
     * Handle InsufficientPlanException — tenant plan does not meet the minimum required plan.
     * Requirements: 3.4, 3.5, 11.1, 11.2
     */
    protected function handleInsufficientPlan(Request $request, InsufficientPlanException $e)
    {
        $message = "Fitur ini memerlukan plan {$e->requiredPlan}. Upgrade plan Anda untuk mengakses {$e->useCase}.";

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'required_plan' => $e->requiredPlan,
                'current_plan' => $e->currentPlan,
                'use_case' => $e->useCase,
            ], 403);
        }

        return redirect()->route('subscription.index')
            ->with('error', $message);
    }

    /**
     * Handle model not found exceptions
     */
    protected function handleModelNotFound(Request $request, ModelNotFoundException $e)
    {
        $model = $e->getModel();

        Log::warning('Model not found', [
            'model' => $model,
            'ids' => $e->getIds(),
            'url' => $request->fullUrl(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'The requested resource was not found.',
                'errors' => ['Resource not found'],
            ], 404);
        }

        return redirect()->back()
            ->with('error', 'The requested resource was not found.');
    }

    /**
     * Handle validation exceptions
     */
    protected function handleValidationException(Request $request, ValidationException $e)
    {
        // Don't log validation errors to database (too noisy)
        // They're already logged by Laravel's validator

        return parent::render($request, $e);
    }

    /**
     * Handle 404 not found errors
     */
    protected function handleNotFound(Request $request, NotFoundHttpException $e)
    {
        Log::info('Page not found', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found.',
            ], 404);
        }

        return response()->view('errors.404', [
            'exception' => $e,
        ], 404);
    }

    /**
     * Handle authentication exceptions
     */
    protected function handleAuthenticationException(Request $request, AuthenticationException $e)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        return redirect()->guest(route('login'));
    }

    /**
     * Get the view used to render HTTP 500 errors.
     */
    protected function convertExceptionToResponse(Throwable $e)
    {
        // For production, show a generic error page
        if (config('app.debug') === false) {
            return response()->view('errors.500', [
                'message' => 'An unexpected error occurred. Please try again later.',
                'error_id' => session()->get('error_id'),
            ], 500);
        }

        // For development, show detailed error page
        return parent::convertExceptionToResponse($e);
    }

    /**
     * Ignore specific exception types from being reported
     */
    protected $ignore = [
        // Ignored exceptions
    ];

    /**
     * Determine if the exception should be reported.
     * Override to prevent re-triggering the global report callback.
     */
    public function report(Throwable $e): void
    {
        // Reporting is handled via bootstrap/app.php withExceptions callback.
        // Do NOT call parent::report() here to avoid infinite recursion.
    }

    /**
     * Get the default context variables for logging
     */
    protected function context(): array
    {
        return array_merge(parent::context(), [
            'tenant_id' => auth()->check() ? auth()->user()->tenant_id : null,
            'user_id' => auth()->id(),
        ]);
    }
}
