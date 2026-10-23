<?php

declare(strict_types=1);

namespace Contenir\Tracking\Mezzio;

use Contenir\Tracking\Mezzio\Factory\TrackingFactory;
use Contenir\Tracking\Mezzio\Factory\TrackingHelperFactory;
use Contenir\Tracking\Mezzio\View\Helper\TrackingHelper;

/**
 * The Tracking service, and the view helper that serves it to laminas-view
 * layouts as tracking() and Tracking().
 *
 * @api
 */
final class ConfigProvider
{
    /**
     * @return array<string, array<string, string>>
     */
    public function getDependencies(): array
    {
        return [
            'factories' => [
                Tracking::class => TrackingFactory::class,
            ],
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function getViewHelpers(): array
    {
        return [
            'aliases'   => [
                'tracking' => TrackingHelper::class,
                'Tracking' => TrackingHelper::class,
            ],
            'factories' => [
                TrackingHelper::class => TrackingHelperFactory::class,
            ],
        ];
    }

    /**
     * @return array<string, array<string, array<string, string>>>
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'view_helpers' => $this->getViewHelpers(),
        ];
    }
}
