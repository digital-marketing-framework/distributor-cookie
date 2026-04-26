<?php

namespace DigitalMarketingFramework\Distributor\Cookie\DataDispatcher;

use DigitalMarketingFramework\Core\Context\ContextAwareInterface;
use DigitalMarketingFramework\Core\Context\ContextAwareTrait;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Model\Data\Value\ValueInterface;
use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcher;

class CookieDataDispatcher extends DataDispatcher implements ContextAwareInterface
{
    use ContextAwareTrait;

    protected string $path = '/';

    protected string $domain = '';

    protected bool $secure = true;

    protected bool $httpOnly = false;

    protected string $sameSite = 'Lax';

    protected int $expires = 0;

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }

    public function setSecure(bool $secure): void
    {
        $this->secure = $secure;
    }

    public function setHttpOnly(bool $httpOnly): void
    {
        $this->httpOnly = $httpOnly;
    }

    public function setSameSite(string $sameSite): void
    {
        $this->sameSite = $sameSite;
    }

    public function setExpires(int $expires): void
    {
        $this->expires = $expires;
    }

    /**
     * @param array<string,string|ValueInterface> $data
     */
    public function send(array $data): void
    {
        if (!isset($this->context)) {
            throw new DigitalMarketingFrameworkException('CookieDataDispatcher requires a context to set response cookies.');
        }

        if (!$this->context->isResponsive()) {
            throw new DigitalMarketingFrameworkException('CookieDataDispatcher requires a responsive context to set response cookies.');
        }

        foreach ($data as $name => $value) {
            $this->context->setResponseCookie(
                $name,
                (string)$value,
                $this->expires,
                $this->path,
                $this->domain,
                $this->secure,
                $this->httpOnly,
                $this->sameSite,
            );
        }
    }

    /**
     * @param array<string,string|ValueInterface> $data
     *
     * @return array<string,mixed>
     */
    public function preview(array $data): array
    {
        $preview = parent::preview($data);
        $preview['config'] = [
            'path' => $this->path,
            'domain' => $this->domain,
            'secure' => $this->secure,
            'httpOnly' => $this->httpOnly,
            'sameSite' => $this->sameSite,
            'expires' => $this->expires,
        ];

        return $preview;
    }
}
