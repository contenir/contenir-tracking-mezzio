<?php

declare(strict_types=1);

namespace Contenir\Tracking\Mezzio\View\Helper;

use Contenir\Tracking\Mezzio\Tracking;

/**
 * `$this->tracking()` in a laminas-view layout: the shared Tracking service.
 *
 *     <?= $this->tracking()->head($nonce) ?>
 *     …
 *     <body>
 *     <?= $this->tracking()->bodyStart() ?>
 *
 * laminas-view's helper manager accepts any callable, so this needs nothing
 * from laminas-view.
 *
 * @api
 */
final readonly class TrackingHelper
{
    public function __construct(
        private Tracking $tracking,
    ) {}

    public function __invoke(): Tracking
    {
        return $this->tracking;
    }
}
