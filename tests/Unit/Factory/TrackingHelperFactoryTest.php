<?php

declare(strict_types=1);

namespace Contenir\Tracking\Mezzio\Tests\Unit\Factory;

use Contenir\Tracking\Mezzio\Factory\TrackingHelperFactory;
use Contenir\Tracking\Mezzio\Tests\TestAsset\ArrayContainer;
use Contenir\Tracking\Mezzio\Tracking;
use Contenir\Tracking\Snippet\HtmlRenderer;
use Contenir\Tracking\Tags;
use Contenir\Tracking\TrackingConfig;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class TrackingHelperFactoryTest extends TestCase
{
    #[Test]
    public function servesTheApplicationsSharedTrackingService(): void
    {
        $tracking = new Tracking(new Tags(new TrackingConfig()), new HtmlRenderer());
        $helper   = (new TrackingHelperFactory())(new ArrayContainer([Tracking::class => $tracking]));

        static::assertSame($tracking, $helper());
    }
}
