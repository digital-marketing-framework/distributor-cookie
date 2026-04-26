<?php

namespace DigitalMarketingFramework\Distributor\Cookie\GlobalConfiguration\Settings;

use DigitalMarketingFramework\Core\GlobalConfiguration\Settings\GlobalSettings;
use DigitalMarketingFramework\Distributor\Cookie\GlobalConfiguration\Schema\DistributorCookieGlobalConfigurationSchema;
use DigitalMarketingFramework\Distributor\Cookie\Route\CookieOutboundRoute;

class CookieDefaultSettings extends GlobalSettings
{
    public function __construct()
    {
        parent::__construct('distributor-cookie');
    }

    public function getDefaultDomain(): string
    {
        return (string)$this->get(DistributorCookieGlobalConfigurationSchema::KEY_DEFAULT_DOMAIN, CookieOutboundRoute::DEFAULT_COOKIE_DOMAIN);
    }

    public function getDefaultPath(): string
    {
        return (string)$this->get(DistributorCookieGlobalConfigurationSchema::KEY_DEFAULT_PATH, CookieOutboundRoute::DEFAULT_COOKIE_PATH);
    }

    public function getDefaultSameSite(): string
    {
        return (string)$this->get(DistributorCookieGlobalConfigurationSchema::KEY_DEFAULT_SAME_SITE, CookieOutboundRoute::DEFAULT_COOKIE_SAME_SITE);
    }

    public function getDefaultSecure(): bool
    {
        return (bool)$this->get(DistributorCookieGlobalConfigurationSchema::KEY_DEFAULT_SECURE, CookieOutboundRoute::DEFAULT_COOKIE_SECURE);
    }
}
