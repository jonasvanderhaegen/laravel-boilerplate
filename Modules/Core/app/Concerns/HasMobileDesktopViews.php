<?php

declare(strict_types=1);

namespace Modules\Core\Concerns;

trait HasMobileDesktopViews
{
    public function addTo(string $page): string
    {
        return "{$this->getString()}.{$page}";
    }

    public function isMobile(): bool
    {
        // pull it back off the Request
        return request()->attributes->get('isMobile', false);
    }

    protected function getString(): string
    {
        return $this->isMobile() ? 'mobile' : 'desktop';
    }
}
