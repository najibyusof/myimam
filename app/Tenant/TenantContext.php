<?php

namespace App\Tenant;

/**
 * Holds the resolved tenant (Masjid) for the current request lifecycle.
 *
 * Set by ResolveTenant middleware.
 * Used by MasjidScope to automatically filter queries.
 * SuperAdmin calls bypass() to disable automatic filtering.
 */
class TenantContext
{
    private static ?int $masjidId = null;
    private static bool $bypassed = false;

    /**
     * Set the active tenant for this request.
     */
    public static function set(int $id): void
    {
        self::$masjidId = $id;
        self::$bypassed = false;
    }

    /**
     * Bypass tenant scoping (SuperAdmin use only).
     */
    public static function bypass(): void
    {
        self::$bypassed = true;
        self::$masjidId = null;
    }

    /**
     * Get the currently active tenant ID, or null if not set.
     */
    public static function get(): ?int
    {
        return self::$masjidId;
    }

    /**
     * Whether tenant scoping is active with a resolved ID.
     */
    public static function isResolved(): bool
    {
        return self::$masjidId !== null;
    }

    /**
     * Whether all tenant scoping has been explicitly bypassed (SuperAdmin mode).
     */
    public static function isBypassed(): bool
    {
        return self::$bypassed;
    }

    /**
     * Reset context — called at test teardown or between jobs.
     */
    public static function flush(): void
    {
        self::$masjidId = null;
        self::$bypassed = false;
    }
}
