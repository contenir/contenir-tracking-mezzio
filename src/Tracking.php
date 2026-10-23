<?php

declare(strict_types=1);

namespace Contenir\Tracking\Mezzio;

use Contenir\Tracking\Snippet\HtmlRenderer;
use Contenir\Tracking\Snippet\Placement;
use Contenir\Tracking\Tags;

/**
 * The site's tags, written as HTML where each belongs, for any template
 * engine. Put head() as early in <head> as possible, since Consent Mode has
 * to be set before any tag loads, and bodyStart() straight after <body>.
 *
 * It is built once from the site's settings and shared across requests, so
 * it holds nothing about a request: the page's CSP nonce is passed to head().
 *
 * @api
 */
final readonly class Tracking
{
    public function __construct(
        private Tags $tags,
        private HtmlRenderer $renderer,
    ) {}

    /**
     * The noscript fallbacks for visitors without scripts, written only
     * when the tags don't wait for consent. They aren't scripts, so they
     * need no nonce.
     */
    public function bodyStart(): string
    {
        return $this->renderer->render($this->tags->at(Placement::BodyStart));
    }

    /**
     * Consent Mode and the contenir:consent bridge, then the Google tag,
     * Tag Manager, the Meta pixel and LinkedIn Insight, each script with
     * $nonce, the page's CSP nonce, when there is one.
     */
    public function head(?string $nonce = null): string
    {
        return $this->renderer->render($this->tags->at(Placement::Head), $nonce);
    }
}
