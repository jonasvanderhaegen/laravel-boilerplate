<?php

declare(strict_types=1);

namespace Modules\Core\Concerns;

use Modules\ClassicAuth\Concerns\ClassicAuth;
use Modules\BanUser\Concerns\HasBanChecks;

trait HasConsolidatedModuleConcerns
{
    use ClassicAuth;
    use HasBanChecks;
}
