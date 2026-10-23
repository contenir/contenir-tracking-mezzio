<?php

declare(strict_types=1);

namespace Contenir\Tracking\Mezzio;

use function array_replace_recursive;
use function is_array;

/**
 * A Mezzio site's settings, read from its merged config. The site's own
 * settings (config/autoload/settings.global.php) are merged at the top level
 * of the config, and the CMS writes each environment's settings
 * (settings.local.php) under a `settings` key, so the settings are the
 * top-level config with config['settings'] laid over it.
 *
 * @api
 */
final class SiteSettings
{
    public const string KEY = 'settings';

    /**
     * @param array<array-key, mixed> $config The application's merged config
     *
     * @return array<array-key, mixed>
     */
    public static function fromConfig(array $config): array
    {
        return is_array($config[self::KEY] ?? null) ? array_replace_recursive($config, $config[self::KEY]) : $config;
    }
}
