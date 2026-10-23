<?php

declare(strict_types=1);

namespace Contenir\Tracking\Mezzio\Tests\TestAsset;

/**
 * A site's settings, as they sit in the merged config: at its top level from
 * settings.global.php, or under config['settings'] from settings.local.php.
 */
final class Settings
{
    /**
     * A Google tag and the Meta pixel, tracked here, not waiting for consent
     * unless told to.
     *
     * @return array<string, mixed>
     */
    public static function googleAndMeta(bool $wait = false): array
    {
        return [...self::tags(), ...self::trackHere($wait)];
    }

    /**
     * The tag IDs alone, as a site's settings.global.php holds them.
     *
     * @return array<string, mixed>
     */
    public static function tags(): array
    {
        return [
            'google'   => ['analytics' => ['key' => 'G-ABC12345']],
            'facebook' => ['analytics' => ['key' => '123456789012345']],
        ];
    }

    /**
     * The Tracking add-on's environment settings, as the CMS writes them to
     * settings.local.php.
     *
     * @return array<string, mixed>
     */
    public static function trackHere(bool $wait = false): array
    {
        return ['extensions' => ['tracking' => ['wait_for_consent' => $wait, 'track_environment' => '1']]];
    }
}
