<?php

declare(strict_types=1);

namespace Contenir\Tracking\Mezzio\Tests\Unit;

use Contenir\Tracking\Mezzio\ConfigProvider;
use Contenir\Tracking\Mezzio\Factory\TrackingFactory;
use Contenir\Tracking\Mezzio\Factory\TrackingHelperFactory;
use Contenir\Tracking\Mezzio\Tracking;
use Contenir\Tracking\Mezzio\View\Helper\TrackingHelper;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class ConfigProviderTest extends TestCase
{
    #[Test]
    public function registersTheTrackingServiceAndTheViewHelperAsTracking(): void
    {
        static::assertSame(
            [
                'dependencies' => [
                    'factories' => [Tracking::class => TrackingFactory::class],
                ],
                'view_helpers' => [
                    'aliases'   => ['tracking' => TrackingHelper::class, 'Tracking' => TrackingHelper::class],
                    'factories' => [TrackingHelper::class => TrackingHelperFactory::class],
                ],
            ],
            (new ConfigProvider())(),
        );
    }

    #[Test]
    public function servesEachSectionOnItsOwn(): void
    {
        $provider = new ConfigProvider();

        static::assertSame(
            $provider(),
            ['dependencies' => $provider->getDependencies(), 'view_helpers' => $provider->getViewHelpers()],
        );
    }
}
