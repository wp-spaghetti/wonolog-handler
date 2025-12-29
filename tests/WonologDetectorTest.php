<?php

declare(strict_types=1);

/*
 * This file is part of the Wonolog Handler package.
 *
 * (ɔ) Frugan <dev@frugan.it>
 *
 * This source file is subject to the GNU GPLv3 license that is bundled
 * with this source code in the file COPYING.
 */

namespace WpSpaghetti\WonologHandler\Tests;

use PHPUnit\Framework\TestCase;
use WpSpaghetti\WonologHandler\Support\WonologDetector;

class WonologDetectorTest extends TestCase
{
    public function test_get_default_namespace(): void
    {
        $detector = new WonologDetector;
        $this->assertEquals('Inpsyde\\Wonolog', $detector->getNamespace());
    }

    public function test_namespace_caching(): void
    {
        $detector = new WonologDetector;

        $namespace1 = $detector->getNamespace();
        $namespace2 = $detector->getNamespace();

        $this->assertSame($namespace1, $namespace2);
    }

    public function test_reset_cache(): void
    {
        $detector = new WonologDetector;

        $detector->getNamespace();
        $detector->resetCache();

        // After reset, namespace should be recalculated
        $this->assertEquals('Inpsyde\\Wonolog', $detector->getNamespace());
    }

    public function test_is_active_returns_false_when_did_action_not_exists(): void
    {
        $detector = new WonologDetector;

        // In unit tests, WordPress functions don't exist
        $this->assertFalse($detector->isActive());
    }
}
