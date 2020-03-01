<?php

declare(strict_types=1);

namespace Toolkit4WP\Tests;

use Toolkit4WP\HookAnnotation as Hook;

class HookAnnotation
{
    use Hook;

    public function __construct()
    {
        $this->hookMethods(1);
    }

    /**
     * @hook init
     */
    public function init(): bool
    {
        return true;
    }
}
