<?php

declare(strict_types=1);

namespace Contenir\Tracking\Mezzio\Factory;

use Contenir\Tracking\Mezzio\Tracking;
use Contenir\Tracking\Mezzio\View\Helper\TrackingHelper;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * Builds tracking() around the application's shared Tracking service.
 *
 * @api
 */
final class TrackingHelperFactory
{
    /**
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): TrackingHelper
    {
        return new TrackingHelper($container->get(Tracking::class));
    }
}
