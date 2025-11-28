<?php
declare(strict_types=1);

namespace OriginCore;

class Config
{
    public const DEFAULT_UNIVERSE = 'home';
    public const DEFAULT_PAGE     = 'index';

    public static function basePath(): string
    {
        return \ORIGIN_CORE_PATH;
    }

    public static function universesPath(): string
    {
        return self::basePath() . '/universes';
    }

    public static function sharedPath(): string
    {
        return self::basePath() . '/shared';
    }
}
