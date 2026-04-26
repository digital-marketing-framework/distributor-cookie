<?php

namespace DigitalMarketingFramework\Distributor\Cookie\GlobalConfiguration\Schema;

use DigitalMarketingFramework\Core\GlobalConfiguration\Schema\GlobalConfigurationSchema;
use DigitalMarketingFramework\Core\SchemaDocument\RenderingDefinition\RenderingDefinitionInterface;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\BooleanSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\StringSchema;
use DigitalMarketingFramework\Distributor\Cookie\Route\CookieOutboundRoute;

class DistributorCookieGlobalConfigurationSchema extends GlobalConfigurationSchema
{
    public const KEY_DEFAULT_DOMAIN = 'defaultDomain';

    public const KEY_DEFAULT_PATH = 'defaultPath';

    public const KEY_DEFAULT_SAME_SITE = 'defaultSameSite';

    public const KEY_DEFAULT_SECURE = 'defaultSecure';

    public function __construct()
    {
        parent::__construct();
        $this->getRenderingDefinition()->setLabel('Cookie (Distributor)');

        $defaultPathSchema = new StringSchema(CookieOutboundRoute::DEFAULT_COOKIE_PATH);
        $defaultPathSchema->getRenderingDefinition()->setLabel('Default Path');
        $this->addProperty(static::KEY_DEFAULT_PATH, $defaultPathSchema);

        $defaultDomainSchema = new StringSchema(CookieOutboundRoute::DEFAULT_COOKIE_DOMAIN);
        $defaultDomainSchema->getRenderingDefinition()->setLabel('Default Domain');
        $defaultDomainSchema->getRenderingDefinition()->setHint('Leave empty to use the current domain.');
        $this->addProperty(static::KEY_DEFAULT_DOMAIN, $defaultDomainSchema);

        $defaultSecureSchema = new BooleanSchema(CookieOutboundRoute::DEFAULT_COOKIE_SECURE);
        $defaultSecureSchema->getRenderingDefinition()->setLabel('Default Secure');
        $this->addProperty(static::KEY_DEFAULT_SECURE, $defaultSecureSchema);

        $defaultSameSiteSchema = new StringSchema(CookieOutboundRoute::DEFAULT_COOKIE_SAME_SITE);
        $defaultSameSiteSchema->getAllowedValues()->addValue('Lax');
        $defaultSameSiteSchema->getAllowedValues()->addValue('Strict');
        $defaultSameSiteSchema->getAllowedValues()->addValue('None');
        $defaultSameSiteSchema->getRenderingDefinition()->setFormat(RenderingDefinitionInterface::FORMAT_SELECT);
        $defaultSameSiteSchema->getRenderingDefinition()->setLabel('Default SameSite');
        $this->addProperty(static::KEY_DEFAULT_SAME_SITE, $defaultSameSiteSchema);
    }
}
