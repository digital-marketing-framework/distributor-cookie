<?php

namespace DigitalMarketingFramework\Distributor\Cookie;

use DigitalMarketingFramework\Core\Initialization;
use DigitalMarketingFramework\Core\Registry\RegistryDomain;
use DigitalMarketingFramework\Distributor\Cookie\DataDispatcher\CookieDataDispatcher;
use DigitalMarketingFramework\Distributor\Cookie\GlobalConfiguration\Schema\DistributorCookieGlobalConfigurationSchema;
use DigitalMarketingFramework\Distributor\Cookie\Route\CookieOutboundRoute;
use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcherInterface;
use DigitalMarketingFramework\Distributor\Core\Route\OutboundRouteInterface;

class DistributorCookieInitialization extends Initialization
{
    protected const PLUGINS = [
        RegistryDomain::DISTRIBUTOR => [
            OutboundRouteInterface::class => [
                CookieOutboundRoute::class,
            ],
            DataDispatcherInterface::class => [
                CookieDataDispatcher::class,
            ],
        ],
    ];

    protected const SCHEMA_MIGRATIONS = [];

    public function __construct(string $packageAlias = '')
    {
        parent::__construct(
            'distributor-cookie',
            '1.0.0',
            $packageAlias,
            new DistributorCookieGlobalConfigurationSchema()
        );
    }
}
