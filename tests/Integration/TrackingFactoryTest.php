<?php

declare(strict_types=1);

namespace Contenir\Tracking\Mezzio\Tests\Integration;

use Contenir\Tracking\Mezzio\Factory\TrackingFactory;
use Contenir\Tracking\Mezzio\Tests\TestAsset\ArrayContainer;
use Contenir\Tracking\Mezzio\Tests\TestAsset\Settings;
use Contenir\Tracking\Mezzio\Tests\Trait\TempDirectoryTrait;
use Contenir\Tracking\Mezzio\Tracking;
use Override;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function chdir;
use function getcwd;
use function preg_match;
use function preg_match_all;

/**
 * The Tracking service built from a Mezzio site's merged config and its
 * extensions config files.
 */
#[Group('integration')]
final class TrackingFactoryTest extends TestCase
{
    use TempDirectoryTrait;

    #[Test]
    public function gatesGooglesTagsInBasicModeOrWhenTheSettingIsMissing(): void
    {
        $this->enable('tracking', 'consent');
        $basic                          = Settings::googleAndMeta();
        $basic['extensions']['consent'] = 'advanced';

        static::assertSame(
            '<script type="text/plain" data-category="analytics" src',
            $this->openingTags($this->tracking(settings: $basic)->head())[1],
        );
    }

    #[Test]
    public function gatesScriptsWhenTheConsentAddOnIsEnabled(): void
    {
        $this->enable('tracking', 'consent');

        static::assertSame(
            [
                '<script>',
                '<script type="text/plain" data-category="analytics" src',
                '<script type="text/plain" data-category="analytics">',
                '<script type="text/plain" data-category="marketing">',
            ],
            $this->openingTags($this->tracking()->head()),
        );
    }

    #[Test]
    public function gatesScriptsWhenTold(): void
    {
        $this->enable('tracking');

        static::assertSame(
            [
                '<script>',
                '<script type="text/plain" data-category="analytics" src',
                '<script type="text/plain" data-category="analytics">',
                '<script type="text/plain" data-category="marketing">',
            ],
            $this->openingTags($this->tracking(['gate' => true])->head()),
        );
    }

    #[Test]
    public function leavesGooglesTagsRunningInTheConsentAddOnsAdvancedMode(): void
    {
        $this->enable('tracking', 'consent');

        static::assertSame(
            [
                '<script>',
                '<script src',
                '<script>',
                '<script type="text/plain" data-category="marketing">',
            ],
            $this->openingTags($this->tracking(settings: $this->advanced())->head()),
        );
    }

    #[Test]
    public function letsTheSettingsKeyReplaceATopLevelSetting(): void
    {
        $this->enable('tracking');
        $local                               = Settings::trackHere();
        $local['google']['analytics']['key'] = 'G-LOCAL0001';

        static::assertSame(1, preg_match('/gtag\/js\?id=G-LOCAL0001"/', $this->tracking(settings: $local)->head()));
    }

    #[Test]
    public function readsAdvancedModeOnlyWhenTheConsentBannerGates(): void
    {
        $this->enable('tracking');

        static::assertSame(
            ['<script>', '<script src', '<script>', '<script>'],
            $this->openingTags($this->tracking(settings: $this->advanced())->head()),
        );
    }

    #[Test]
    public function readsTheDefaultDirectoryWhenTheConfiguredOneIsNotText(): void
    {
        $this->enable('tracking');
        $tracking = (new TrackingFactory())(new ArrayContainer([
            'config' => [
                'settings'          => Settings::googleAndMeta(),
                'contenir_tracking' => ['extensions_config' => ['not', 'a', 'path']],
            ],
        ]));

        static::assertSame('', $tracking->head());
    }

    #[Test]
    public function readsTheExtensionsConfigFromTheSitesConfigDirectoryByDefault(): void
    {
        $this->writeConfig('config/autoload/extensions.global.php', ['extensions' => ['enabled' => ['tracking']]]);
        $container = new ArrayContainer(['config' => ['settings' => Settings::googleAndMeta()]]);

        $cwd = (string) getcwd();
        chdir($this->directory);
        try {
            $head = (new TrackingFactory())($container)->head();
        } finally {
            chdir($cwd);
        }

        static::assertCount(4, $this->openingTags($head));
    }

    #[Test]
    public function readsTheTagsFromTheTopLevelAndTheEnvironmentFromTheSettingsKey(): void
    {
        $this->enable('tracking');

        static::assertSame(
            ['<script nonce="abc">', '<script src', '<script nonce="abc">', '<script nonce="abc">'],
            $this->openingTags($this->tracking()->head('abc')),
        );
    }

    #[Test]
    public function waitsForConsentBehindTheConsentBannerWhateverTheSettingsSay(): void
    {
        $this->enable('tracking', 'consent');

        $head = $this->tracking()->head();

        static::assertSame(
            [1, 0, 1],
            [
                preg_match('/"ad_storage":"denied"/', $head),
                preg_match('/"ad_storage":"granted"/', $head),
                preg_match("/state && state\\.ads \\? 'grant' : 'revoke'/", $head),
            ],
        );
    }

    #[Test]
    public function writesNothingWhenOnlyTheTopLevelSettingsExist(): void
    {
        $this->enable('tracking');
        $tracking = (new TrackingFactory())(new ArrayContainer([
            'config' => [
                ...Settings::tags(),
                'contenir_tracking' => ['extensions_config' => $this->directory],
            ],
        ]));

        static::assertSame('', $tracking->head());
    }

    #[Test]
    public function writesNothingWhenTheLocalListLeavesTrackingOut(): void
    {
        $this->enable('tracking');
        $this->writeConfig('extensions.local.php', ['extensions' => ['enabled' => ['seo']]]);

        static::assertSame('', $this->tracking()->head());
    }

    #[Test]
    public function writesNothingWhenTrackingIsNotEnabled(): void
    {
        static::assertSame('', $this->tracking()->head());
    }

    #[Test]
    public function writesNothingWithoutConfig(): void
    {
        static::assertSame('', (new TrackingFactory())(new ArrayContainer(['config' => null]))->head());
    }

    #[Test]
    public function writesNothingWithoutSettings(): void
    {
        $this->enable('tracking');
        $tracking = (new TrackingFactory())(new ArrayContainer([
            'config' => [
                'settings'          => 'not settings',
                'contenir_tracking' => ['extensions_config' => $this->directory, 'gate' => 'yes'],
            ],
        ]));

        static::assertSame('', $tracking->head());
    }

    #[Override]
    protected function setUp(): void
    {
        $this->makeTempDirectory();
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->removeTempDirectory();
    }

    /**
     * The environment's settings with the Consent add-on in advanced Google
     * Consent Mode.
     *
     * @return array<string, mixed>
     */
    private function advanced(): array
    {
        $settings                                                 = Settings::trackHere();
        $settings['extensions']['consent']['google_consent_mode'] = 'advanced';

        return $settings;
    }

    private function enable(string ...$handles): void
    {
        $this->writeConfig('extensions.global.php', ['extensions' => ['enabled' => $handles]]);
    }

    /**
     * Each script's opening tag, up to its src when it has one.
     *
     * @return list<string>
     */
    private function openingTags(string $html): array
    {
        preg_match_all('/<script[^>]*?(?: src|>)/', $html, $matches);

        return $matches[0];
    }

    /**
     * The service for a site whose settings.global.php holds its tags, with
     * $settings (by default, tracking this environment) under the settings
     * key, as the CMS writes settings.local.php.
     *
     * @param array<string, mixed>  $options
     * @param ?array<string, mixed> $settings
     */
    private function tracking(array $options = [], ?array $settings = null): Tracking
    {
        return (new TrackingFactory())(new ArrayContainer([
            'config' => [
                ...Settings::tags(),
                'settings'          => $settings ?? Settings::trackHere(),
                'contenir_tracking' => ['extensions_config' => $this->directory, ...$options],
            ],
        ]));
    }
}
