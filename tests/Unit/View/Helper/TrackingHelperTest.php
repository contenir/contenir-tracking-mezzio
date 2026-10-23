<?php

declare(strict_types=1);

namespace Contenir\Tracking\Mezzio\Tests\Unit\View\Helper;

use Contenir\Tracking\Mezzio\Tracking;
use Contenir\Tracking\Mezzio\View\Helper\TrackingHelper;
use Contenir\Tracking\Snippet\HtmlRenderer;
use Contenir\Tracking\Tags;
use Contenir\Tracking\TrackingConfig;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class TrackingHelperTest extends TestCase
{
    #[Test]
    public function givesTheTemplateTheTrackingService(): void
    {
        $tracking = new Tracking(new Tags(new TrackingConfig()), new HtmlRenderer());

        static::assertSame($tracking, (new TrackingHelper($tracking))());
    }
}
