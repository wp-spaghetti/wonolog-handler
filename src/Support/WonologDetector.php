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

namespace WpSpaghetti\WonologHandler\Support;

/**
 * Detects Wonolog availability and namespace (supports wpify/scoper).
 *
 * Based on wp-spaghetti/wp-logger implementation
 *
 * @see https://github.com/wp-spaghetti/wp-logger/blob/main/src/Logger.php#L628
 */
class WonologDetector
{
    /**
     * Cache for isActive check
     */
    private ?bool $activeCache = null;

    /**
     * Cache for Wonolog namespace
     */
    private ?string $namespaceCache = null;

    /**
     * Cache for action hook
     */
    private ?string $actionCache = null;

    /**
     * Get Wonolog namespace (supports wpify/scoper).
     *
     * This method detects the actual namespace used by Wonolog, which may be
     * different from the default if wpify/scoper is used in the mu-plugin.
     */
    public function getNamespace(): string
    {
        if ($this->namespaceCache !== null) {
            return $this->namespaceCache;
        }

        // Get default from config if available, fallback to Inpsyde\Wonolog
        $default = \function_exists('config')
            ? \config('wonolog.namespace', 'Inpsyde\\Wonolog')
            : 'Inpsyde\\Wonolog';

        // Allow custom namespace via filter (useful for scoped builds)
        // Filter has priority over config
        $namespace = \function_exists('apply_filters')
            ? \apply_filters('wonolog_handler.namespace', $default)
            : $default;

        $this->namespaceCache = $namespace;

        return $this->namespaceCache;
    }

    /**
     * Get Wonolog action hook name (supports custom/scoped action names).
     *
     * This method detects the actual action hook used by Wonolog, which may be
     * different from the default if Wonolog is scoped or custom naming is used.
     */
    public function getAction(): string
    {
        if ($this->actionCache !== null) {
            return $this->actionCache;
        }

        // Get default from config if available, fallback to wonolog.log
        $default = \function_exists('config')
            ? \config('wonolog.action', 'wonolog.log')
            : 'wonolog.log';

        // Allow custom action via filter (useful for scoped builds)
        // Filter has priority over config
        $action = \function_exists('apply_filters')
            ? \apply_filters('wonolog_handler.action', $default)
            : $default;

        $this->actionCache = $action;

        return $this->actionCache;
    }

    /**
     * Check if Wonolog is active and ready.
     *
     * Supports wpify/scoper by dynamically detecting namespace.
     */
    public function isActive(): bool
    {
        if ($this->activeCache !== null) {
            return $this->activeCache;
        }

        // Check if required WordPress functions exist
        if (! \function_exists('did_action')) {
            $this->activeCache = false;

            return false;
        }

        $wonologNamespace = $this->getNamespace();
        $configuratorClass = $wonologNamespace.'\\Configurator';

        // Check if Wonolog classes exist
        if (! \class_exists($configuratorClass)) {
            $this->activeCache = false;

            return false;
        }

        $actionSetupConstant = $configuratorClass.'::ACTION_SETUP';

        // Check if the setup constant is defined
        if (! \defined($actionSetupConstant)) {
            $this->activeCache = false;

            return false;
        }

        // Check if Wonolog setup action was triggered
        $actionName = \constant($actionSetupConstant);
        $actionTriggered = \did_action($actionName) > 0;

        $this->activeCache = $actionTriggered;

        return $this->activeCache;
    }

    /**
     * Reset caches (useful for testing)
     */
    public function resetCache(): void
    {
        $this->activeCache = null;
        $this->namespaceCache = null;
        $this->actionCache = null;
    }
}
