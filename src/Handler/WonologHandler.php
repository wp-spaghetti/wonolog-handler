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

namespace WpSpaghetti\WonologHandler\Handler;

use Monolog\Handler\AbstractHandler;
use Monolog\LogRecord;
use WpSpaghetti\WonologHandler\Support\WonologDetector;

/**
 * Forwards Laravel/Monolog logs to Wonolog's action hook.
 *
 * This handler bridges Laravel's Log facade with Wonolog, allowing you to use
 * clean Laravel syntax (Log::error()) while benefiting from Wonolog's features
 * like email notifications, ignore patterns, and sensitive data filtering.
 *
 * Supports wpify/scoper in mu-plugin by dynamically detecting Wonolog namespace.
 * Gracefully degrades if Wonolog is not active - logs will still work through
 * other handlers in the stack.
 */
class WonologHandler extends AbstractHandler
{
    private WonologDetector $detector;

    private bool $stopPropagation;

    private string $action;

    public function __construct(
        int|string|\Monolog\Level $level = \Monolog\Level::Debug,
        bool $bubble = true,
        ?WonologDetector $detector = null
    ) {
        parent::__construct($level, $bubble);
        $this->detector = $detector ?? new WonologDetector;

        // Read stop_propagation config (defaults to false = continue propagation)
        $this->stopPropagation = \config('wonolog.stop_propagation', false);

        // Get action hook name (supports custom/scoped action names)
        $this->action = $this->detector->getAction();
    }

    /**
     * Handle the log record
     */
    public function handle(LogRecord $record): bool
    {
        if (! $this->isHandling($record)) {
            return false;
        }

        // Graceful degradation: if Wonolog is not active, skip forwarding
        // Other handlers in the stack will handle the log
        if (! $this->detector->isActive()) {
            return false; // Always propagate when Wonolog is not active
        }

        // Prepare context
        $context = $record->context;

        // Extract Wonolog channel from context if explicitly provided by user
        // Remove it from context to avoid duplication
        //
        // NOTE: Wonolog uses UPPERCASE channel names by convention (DEBUG, SECURITY, HTTP, etc.)
        // Using lowercase or non-configured channels may cause logs not to be tracked
        // depending on Wonolog configuration. Always use UPPERCASE for channel names.
        $wonologChannel = null;
        if (isset($context['channel'])) {
            $wonologChannel = $context['channel'];
            unset($context['channel']);
        }

        // NOTE: We do NOT use Monolog's $record->channel as fallback.
        // Monolog channels (e.g., 'development', 'stack', 'single') are Laravel's
        // logging channels and have nothing to do with Wonolog's channels
        // (e.g., 'DEBUG', 'SECURITY', 'HTTP').
        //
        // If user doesn't provide a channel, Wonolog will use its default (DEBUG).

        // Add extra data (following Wonolog's convention)
        // @see HookLogFactory::maybeRaiseLevel() - uses 'extra' not '_extra'
        if (! empty($record->extra)) {
            $context['extra'] = $record->extra;
        }

        // Add datetime (following Wonolog's convention)
        $context['datetime'] = $record->datetime;

        // BUGFIX: Wonolog v2.x/v3.x PSR-3 placeholder substitution compatibility
        //
        // HookLogFactory::fromString() wraps context as $arguments = [0 => $context],
        // breaking PsrLogMessageProcessor placeholders like {handle} and {url}.
        //
        // Using array format forces fromArray() method which preserves correct
        // context structure. This works with all current Wonolog versions and
        // has no negative side effects.
        //
        // @see https://github.com/wp-spaghetti/wp-logger/blob/main/src/Logger.php#L628

        // Prepare action data
        $actionData = [
            'level' => $record->level->value,
            'message' => $record->message,
            'context' => $context,
        ];

        // Only add channel if user explicitly provided it
        // If not provided, Wonolog will use its default channel (DEBUG)
        if ($wonologChannel !== null) {
            $actionData['channel'] = $wonologChannel;
        }

        // Forward to Wonolog using array format
        // Monolog Level values match Wonolog LogLevel constants (100=DEBUG, 200=INFO, etc.)
        \do_action($this->action, $actionData);

        // Return based on stop_propagation config
        // true = stop propagation (no other handlers)
        // false = continue propagation (default, allows file backup)
        return $this->stopPropagation;
    }
}
