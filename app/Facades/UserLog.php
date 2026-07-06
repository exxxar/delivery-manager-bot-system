<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Models\UserLog|null log(string $message, ?int $userId = null)
 * @method static mixed getLogs(?int $userId = null, int $perPage = 20, int $page = 1)
 * @method static array logSuper(string $message, ?int $actorId = null)
 * @method static int cleanup(?int $userId = null)
 * @method static int cleanupAll()
 *
 * @see \App\Services\UserLogger
 */
class UserLog extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\UserLogger::class;
    }
}
