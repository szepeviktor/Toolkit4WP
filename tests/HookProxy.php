<?php

declare(strict_types=1);

namespace Toolkit4WP\Tests;

use Toolkit4WP\HookProxy as Hook;

class HookAnnotation
{
    use Hook;

    public function __construct()
    {
        $this->lazyHookMethod('init', [$this, 'init'], 10, 0);
    }

    public function init(): bool
    {
        return true;
    }
}
