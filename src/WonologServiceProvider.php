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

namespace WpSpaghetti\WonologHandler;

use Illuminate\Support\ServiceProvider;
use WpSpaghetti\WonologHandler\Handler\WonologHandler;
use WpSpaghetti\WonologHandler\Support\WonologDetector;

class WonologServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register detector as singleton
        $this->app->singleton(WonologDetector::class, fn () => new WonologDetector);

        // Register handler factory (optional - ensures detector singleton is injected)
        $this->app->bind(WonologHandler::class, fn ($app) => new WonologHandler(
            detector: $app->make(WonologDetector::class)
        ));
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config (optional)
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/wonolog.php' => \config_path('wonolog.php'),
            ], 'wonolog-handler-config');
        }
    }
}
