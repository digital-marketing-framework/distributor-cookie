<?php

namespace DigitalMarketingFramework\Distributor\Cookie\Route;

use DigitalMarketingFramework\Core\DataProcessor\ValueSource\ConstantValueSource;
use DigitalMarketingFramework\Core\GlobalConfiguration\GlobalConfigurationAwareInterface;
use DigitalMarketingFramework\Core\GlobalConfiguration\GlobalConfigurationAwareTrait;
use DigitalMarketingFramework\Core\Integration\IntegrationInfo;
use DigitalMarketingFramework\Core\Model\Data\Data;
use DigitalMarketingFramework\Core\Model\Data\DataInterface;
use DigitalMarketingFramework\Core\Model\Data\Value\DateTimeValue;
use DigitalMarketingFramework\Core\SchemaDocument\RenderingDefinition\RenderingDefinitionInterface;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\BooleanSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\Custom\InheritableBooleanSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\Custom\InheritableStringSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\Custom\ValueSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\CustomSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\ListSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\StringSchema;
use DigitalMarketingFramework\Core\Utility\GeneralUtility;
use DigitalMarketingFramework\Distributor\Cookie\DataDispatcher\CookieDataDispatcher;
use DigitalMarketingFramework\Distributor\Cookie\GlobalConfiguration\Settings\CookieDefaultSettings;
use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcherInterface;
use DigitalMarketingFramework\Distributor\Core\Model\Configuration\DistributorConfigurationInterface;
use DigitalMarketingFramework\Distributor\Core\Route\OutboundRoute;

class CookieOutboundRoute extends OutboundRoute implements GlobalConfigurationAwareInterface
{
    use GlobalConfigurationAwareTrait;

    public const DEFAULT_COOKIE_DOMAIN = '';

    public const DEFAULT_COOKIE_EXPIRES = '0';

    public const DEFAULT_COOKIE_HTTP_ONLY = false;

    public const DEFAULT_COOKIE_PATH = '/';

    public const DEFAULT_COOKIE_SAME_SITE = 'Lax';

    public const DEFAULT_COOKIE_SECURE = true;

    public const KEY_COOKIE_DOMAIN = 'domain';

    public const KEY_COOKIE_EXPIRES = 'expires';

    public const KEY_COOKIE_HTTP_ONLY = 'httpOnly';

    public const KEY_COOKIE_NAME = 'name';

    public const KEY_COOKIE_PATH = 'path';

    public const KEY_COOKIE_SAME_SITE = 'sameSite';

    public const KEY_COOKIE_SECURE = 'secure';

    public const KEY_COOKIE_VALUE = 'value';

    public const KEY_COOKIES = 'cookies';

    public const VALUE_COOKIE_SAME_SITE_INHERIT = 'inherit';

    public function async(): ?bool
    {
        return false;
    }

    public function enableStorage(): ?bool
    {
        return false;
    }

    public static function getDefaultIntegrationInfo(): IntegrationInfo
    {
        return new IntegrationInfo('system');
    }

    public static function getLabel(): ?string
    {
        return 'Cookie';
    }

    public static function getSchema(): SchemaInterface
    {
        /** @var ContainerSchema $schema */
        $schema = parent::getSchema();

        $schema->removeProperty(DistributorConfigurationInterface::KEY_ASYNC);
        $schema->removeProperty(DistributorConfigurationInterface::KEY_ENABLE_STORAGE);
        $schema->removeProperty(static::KEY_DATA);

        // Cookie entry schema
        $cookieEntrySchema = new ContainerSchema();
        $cookieEntrySchema->getRenderingDefinition()->setLabel('Cookie');

        $nameSchema = new CustomSchema(ValueSchema::TYPE, ValueSchema::createStandardValueConfiguration('constant', [ConstantValueSource::KEY_VALUE => '']));
        $nameSchema->getRenderingDefinition()->setLabel('Name');
        $cookieEntrySchema->addProperty(static::KEY_COOKIE_NAME, $nameSchema);

        $valueSchema = new CustomSchema(ValueSchema::TYPE, ValueSchema::createStandardValueConfiguration('constant', [ConstantValueSource::KEY_VALUE => '1']));
        $valueSchema->getRenderingDefinition()->setLabel('Value');
        $cookieEntrySchema->addProperty(static::KEY_COOKIE_VALUE, $valueSchema);

        $cookieListSchema = new ListSchema($cookieEntrySchema);
        $cookieListSchema->getRenderingDefinition()->setLabel('Cookies');
        $schema->addProperty(static::KEY_COOKIES, $cookieListSchema);

        // Route-level cookie settings (secondary group)
        $expiresSchema = new CustomSchema(ValueSchema::TYPE, ValueSchema::createStandardValueConfiguration('constant', [ConstantValueSource::KEY_VALUE => static::DEFAULT_COOKIE_EXPIRES]));
        $expiresSchema->getRenderingDefinition()->setLabel('Expires');
        $expiresSchema->getRenderingDefinition()->setHint('Empty or 0 = session cookie. Accepts duration (e.g. "+30 days"), absolute date (e.g. "2027-03-12"), Unix timestamp, or DateTime value.');
        $expiresSchema->getRenderingDefinition()->setGroup(RenderingDefinitionInterface::GROUP_SECONDARY);
        $schema->addProperty(static::KEY_COOKIE_EXPIRES, $expiresSchema);

        $pathSchema = new InheritableStringSchema();
        $pathSchema->setLabel('Path');
        $pathSchema->getRenderingDefinition()->setGroup(RenderingDefinitionInterface::GROUP_SECONDARY);
        $schema->addProperty(static::KEY_COOKIE_PATH, $pathSchema);

        $domainSchema = new InheritableStringSchema();
        $domainSchema->setLabel('Domain');
        $domainSchema->getRenderingDefinition()->setGroup(RenderingDefinitionInterface::GROUP_SECONDARY);
        $schema->addProperty(static::KEY_COOKIE_DOMAIN, $domainSchema);

        $secureSchema = new InheritableBooleanSchema();
        $secureSchema->getRenderingDefinition()->setLabel('Secure');
        $secureSchema->getRenderingDefinition()->setGroup(RenderingDefinitionInterface::GROUP_SECONDARY);
        $schema->addProperty(static::KEY_COOKIE_SECURE, $secureSchema);

        $httpOnlySchema = new BooleanSchema(static::DEFAULT_COOKIE_HTTP_ONLY);
        $httpOnlySchema->getRenderingDefinition()->setLabel('HTTP Only');
        $httpOnlySchema->getRenderingDefinition()->setGroup(RenderingDefinitionInterface::GROUP_SECONDARY);
        $schema->addProperty(static::KEY_COOKIE_HTTP_ONLY, $httpOnlySchema);

        $sameSiteSchema = new StringSchema(static::VALUE_COOKIE_SAME_SITE_INHERIT);
        $sameSiteSchema->getAllowedValues()->addValue(static::VALUE_COOKIE_SAME_SITE_INHERIT);
        $sameSiteSchema->getAllowedValues()->addValue('Lax');
        $sameSiteSchema->getAllowedValues()->addValue('Strict');
        $sameSiteSchema->getAllowedValues()->addValue('None');
        $sameSiteSchema->getRenderingDefinition()->setFormat(RenderingDefinitionInterface::FORMAT_SELECT);
        $sameSiteSchema->getRenderingDefinition()->setLabel('SameSite');
        $sameSiteSchema->getRenderingDefinition()->setGroup(RenderingDefinitionInterface::GROUP_SECONDARY);
        $schema->addProperty(static::KEY_COOKIE_SAME_SITE, $sameSiteSchema);

        return $schema;
    }

    public function buildData(): DataInterface
    {
        $context = $this->getDataProcessorContext();
        $cookieConfigs = $this->getListConfig(static::KEY_COOKIES);
        $data = [];

        foreach ($cookieConfigs as $cookieConfig) {
            $name = (string)($this->dataProcessor->processValue($cookieConfig[static::KEY_COOKIE_NAME], $context) ?? '');
            if ($name === '') {
                continue;
            }

            $value = (string)($this->dataProcessor->processValue($cookieConfig[static::KEY_COOKIE_VALUE], $context) ?? '');
            if ($value === '') {
                continue;
            }

            $data[$name] = $value;
        }

        return new Data($data);
    }

    protected function getDispatcher(): DataDispatcherInterface
    {
        /** @var CookieDataDispatcher $dispatcher */
        $dispatcher = $this->registry->getDataDispatcher('cookie');

        /** @var CookieDefaultSettings $defaults */
        $defaults = $this->globalConfiguration->getGlobalSettings(CookieDefaultSettings::class);

        $dispatcher->setContext($this->context);

        $path = InheritableStringSchema::convert($this->getArrayConfig(static::KEY_COOKIE_PATH));
        $dispatcher->setPath($path ?? $defaults->getDefaultPath());

        $domain = InheritableStringSchema::convert($this->getArrayConfig(static::KEY_COOKIE_DOMAIN));
        $dispatcher->setDomain($domain ?? $defaults->getDefaultDomain());

        $secure = InheritableBooleanSchema::convert($this->getStringConfig(static::KEY_COOKIE_SECURE));
        $dispatcher->setSecure($secure ?? $defaults->getDefaultSecure());

        $dispatcher->setHttpOnly($this->getBoolConfig(static::KEY_COOKIE_HTTP_ONLY));

        $sameSite = $this->getStringConfig(static::KEY_COOKIE_SAME_SITE);
        $dispatcher->setSameSite($sameSite === static::VALUE_COOKIE_SAME_SITE_INHERIT ? $defaults->getDefaultSameSite() : $sameSite);

        $expiresValue = $this->dataProcessor->processValue(
            $this->getConfig(static::KEY_COOKIE_EXPIRES),
            $this->getDataProcessorContext()
        );
        if (GeneralUtility::isFalse($expiresValue)) {
            $dispatcher->setExpires(0);
        } else {
            $expiresDate = GeneralUtility::castValueToDateTimeValue($expiresValue);
            $dispatcher->setExpires($expiresDate instanceof DateTimeValue ? $expiresDate->getDate()->getTimestamp() : 0);
        }

        return $dispatcher;
    }
}
