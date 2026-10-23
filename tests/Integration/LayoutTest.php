<?php

declare(strict_types=1);

namespace Contenir\Tracking\Mezzio\Tests\Integration;

use Contenir\Tracking\Mezzio\ConfigProvider;
use Contenir\Tracking\Mezzio\Tests\TestAsset\Settings;
use Contenir\Tracking\Mezzio\Tests\Trait\TempDirectoryTrait;
use Contenir\Tracking\Mezzio\Tracking;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use Override;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function preg_match;

/**
 * A layout calling tracking() through laminas-view, with the services and
 * helpers the ConfigProvider registers in a laminas-servicemanager container,
 * as a Mezzio application with mezzio-laminasviewrenderer builds them.
 */
#[Group('integration')]
final class LayoutTest extends TestCase
{
    use TempDirectoryTrait;

    private ServiceManager $services;

    #[Test]
    public function servesTheApplicationsSharedTrackingService(): void
    {
        $tracking = $this->renderer()->plugin('tracking');

        static::assertSame($this->services->get(Tracking::class), $tracking());
    }

    #[Test]
    public function writesEachPagesOwnNonce(): void
    {
        $renderer = $this->renderer();
        $renderer->render('layout', ['nonce' => 'first']);
        $html = $renderer->render('layout', ['nonce' => 'second']);

        static::assertSame([0, 1], [preg_match('/nonce="first"/', $html), preg_match('/nonce="second"/', $html)]);
    }

    #[Override]
    protected function setUp(): void
    {
        $this->makeTempDirectory();
        $this->writeConfig('extensions.global.php', ['extensions' => ['enabled' => ['tracking']]]);

        $config         = (new ConfigProvider())();
        $this->services = new ServiceManager($config['dependencies']);
        $this->services->setService('config', [
            ...Settings::tags(),
            'settings'          => Settings::trackHere(),
            'contenir_tracking' => ['extensions_config' => $this->directory],
        ]);
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->removeTempDirectory();
    }

    private function renderer(): PhpRenderer
    {
        $renderer = new PhpRenderer();
        $renderer->setHelperPluginManager(new HelperPluginManager(
            $this->services,
            (new ConfigProvider())->getViewHelpers(),
        ));
        $renderer->setResolver(new TemplatePathStack(['script_paths' => [__DIR__ . '/../TestAsset/view']]));

        return $renderer;
    }
}
