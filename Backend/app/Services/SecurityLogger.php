<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SecurityLogger
{
    /**
     * Log a security event
     */
    public static function logSecurityEvent(string $event, array $context = [], string $level = 'info'): void
    {
        $logData = [
            'event' => $event,
            'timestamp' => now()->toISOString(),
            'environment' => app()->environment(),
        ];

        // Merge additional context
        $logData = array_merge($logData, $context);

        // Log with appropriate level
        Log::channel('security')->{$level}($event, $logData);
    }

    /**
     * Log authentication events
     */
    public static function logAuthEvent(string $event, Request $request, ?int $userId = null, array $extra = []): void
    {
        $context = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
        ];

        if ($userId) {
            $context['user_id'] = $userId;
        }

        $context = array_merge($context, $extra);

        self::logSecurityEvent($event, $context);
    }

    /**
     * Log failed login attempt
     */
    public static function logFailedLogin(Request $request, string $email, string $reason): void
    {
        self::logAuthEvent('failed_login_attempt', $request, null, [
            'email' => $email,
            'reason' => $reason,
        ]);
    }

    /**
     * Log successful login
     */
    public static function logSuccessfulLogin(Request $request, int $userId, string $email): void
    {
        self::logAuthEvent('successful_login', $request, $userId, [
            'email' => $email,
        ]);
    }

    /**
     * Log logout event
     */
    public static function logLogout(Request $request, int $userId): void
    {
        self::logAuthEvent('user_logout', $request, $userId);
    }

    /**
     * Log profile access
     */
    public static function logProfileAccess(Request $request, int $userId): void
    {
        self::logAuthEvent('profile_access', $request, $userId);
    }

    /**
     * Log rate limiting event
     */
    public static function logRateLimit(Request $request, ?int $userId = null): void
    {
        self::logAuthEvent('rate_limit_exceeded', $request, $userId, [], 'warning');
    }

    /**
     * Log suspicious activity
     */
    public static function logSuspiciousActivity(string $activity, Request $request, array $details = []): void
    {
        self::logAuthEvent('suspicious_activity', $request, null, array_merge([
            'activity' => $activity,
        ], $details), 'warning');
    }

    /**
     * Log security violation
     */
    public static function logSecurityViolation(string $violation, Request $request, array $details = []): void
    {
        self::logAuthEvent('security_violation', $request, null, array_merge([
            'violation' => $violation,
        ], $details), 'error');
    }
}