<?php

declare(strict_types=1);

namespace Contenir\Tracking\Mezzio\Tests\Unit;

use Contenir\Tracking\Mezzio\Tests\TestAsset\Settings;
use Contenir\Tracking\Mezzio\Tracking;
use Contenir\Tracking\Snippet\HtmlRenderer;
use Contenir\Tracking\Snippet\Placement;
use Contenir\Tracking\Tags;
use Contenir\Tracking\TrackingConfig;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class TrackingTest extends TestCase
{
    #[Test]
    public function writesTheFallbacksAtTheStartOfTheBody(): void
    {
        $tracking = new Tracking($this->tags(), new HtmlRenderer());

        static::assertSame(
            '<noscript><img height="1" width="1" style="display:none" alt="" '
                . 'src="https://www.facebook.com/tr?ev=PageView&amp;noscript=1&amp;id=123456789012345"></noscript>',
            $tracking->bodyStart(),
        );
    }

    #[Test]
    public function writesTheHeadTagsWithoutANonceWhenThereIsNone(): void
    {
        $tracking = new Tracking($this->tags(), new HtmlRenderer(gate: true));

        static::assertSame(
            (new HtmlRenderer(gate: true))->render($this->tags()->at(Placement::Head)),
            $tracking->head(),
        );
    }

    #[Test]
    public function writesTheHeadTagsWithTheNonceItIsGiven(): void
    {
        $tracking = new Tracking($this->tags(), new HtmlRenderer());

        static::assertSame(
            (new HtmlRenderer())->render($this->tags()->at(Placement::Head), 'n0nce'),
            $tracking->head('n0nce'),
        );
    }

    private function tags(): Tags
    {
        return new Tags(TrackingConfig::fromSettings(Settings::googleAndMeta()));
    }
}
