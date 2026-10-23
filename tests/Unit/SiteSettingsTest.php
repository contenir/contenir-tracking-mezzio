<?php

declare(strict_types=1);

namespace Contenir\Tracking\Mezzio\Tests\Unit;

use Contenir\Tracking\Mezzio\SiteSettings;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class SiteSettingsTest extends TestCase
{
    /**
     * @return array<string, array{array<array-key, mixed>, array<array-key, mixed>}>
     */
    public static function configs(): array
    {
        $global = ['site' => ['title' => 'Studio', 'url' => 'https://example.com'], 'google' => ['key' => 'G-1']];

        return [
            'top level only'                 => [$global, $global],
            'settings over top level'        => [
                [...$global, 'settings' => ['site' => ['url' => 'http://example.local']]],
                [
                    'site'     => ['title' => 'Studio', 'url' => 'http://example.local'],
                    'google'   => ['key' => 'G-1'],
                    'settings' => ['site' => ['url' => 'http://example.local']],
                ],
            ],
            'settings alone'                 => [
                ['settings' => ['google' => ['key' => 'G-2']]],
                ['settings' => ['google' => ['key' => 'G-2']], 'google' => ['key' => 'G-2']],
            ],
            'settings that are not an array' => [
                [...$global, 'settings' => 'not settings'],
                [...$global, 'settings' => 'not settings'],
            ],
            'nothing'                        => [[], []],
        ];
    }

    /**
     * @param array<array-key, mixed> $config
     * @param array<array-key, mixed> $settings
     */
    #[Test]
    #[DataProvider('configs')]
    public function laysTheSettingsKeyOverTheTopLevelConfig(array $config, array $settings): void
    {
        static::assertSame($settings, SiteSettings::fromConfig($config));
    }
}
