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

return [
    /*
    |--------------------------------------------------------------------------
    | Wonolog Namespace
    |--------------------------------------------------------------------------
    |
    | The namespace used by Wonolog. This is automatically detected, but you
    | can override it here if needed. Useful when Wonolog is scoped with
    | wpify/scoper.
    |
    | Default: 'Inpsyde\\Wonolog'
    |
    */
    'namespace' => \env('WONOLOG_NAMESPACE', 'Inpsyde\\Wonolog'),

    /*
    |--------------------------------------------------------------------------
    | Action Hook
    |--------------------------------------------------------------------------
    |
    | The WordPress action hook used to send logs to Wonolog. This is useful
    | if Wonolog is scoped or if you want to use a custom action name.
    |
    | Default: 'wonolog.log'
    |
    */
    'action' => \env('WONOLOG_ACTION', 'wonolog.log'),

    /*
    |--------------------------------------------------------------------------
    | Stop Propagation
    |--------------------------------------------------------------------------
    |
    | When true, stops log propagation after Wonolog (no other handlers will
    | receive the log). When false (default), logs continue to other handlers
    | in the stack (e.g., file backup).
    |
    | Set to true if you only want Wonolog to handle logs.
    | Set to false if you want both Wonolog and file backup.
    |
    */
    'stop_propagation' => \env('WONOLOG_STOP_PROPAGATION', false),
];
