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

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;
use WpSpaghetti\WonologHandler\Handler\WonologHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that is utilized to write
    | messages to your logs. The value provided here should match one of
    | the channels present in the list of "channels" configured below.
    |
    */

    'default' => \env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => \env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => \env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Laravel
    | utilizes the Monolog PHP logging library, which includes a variety
    | of powerful log handlers and formatters that you're free to use.
    |
    | Available drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog", "custom", "stack"
    |
    */

    'channels' => [
        /*
        |----------------------------------------------------------------------
        | Stack Channel - Recommended for Production
        |----------------------------------------------------------------------
        |
        | Combines Wonolog forwarding with a local file backup.
        | If Wonolog mu-plugin is not active, logs still work via 'single'.
        |
        | Usage: Log::error('message') - uses this by default
        |
        */
        'stack' => [
            'driver' => 'stack',
            'channels' => ['wonolog', 'single'],
            'ignore_exceptions' => false,
        ],

        /*
        |----------------------------------------------------------------------
        | Wonolog Channel
        |----------------------------------------------------------------------
        |
        | This channel uses WonologHandler to forward all logs to Wonolog.
        | If Wonolog is not active, the handler gracefully degrades.
        |
        | Wonolog features:
        | - Email notifications for errors
        | - Sensitive data filtering (passwords, tokens, etc.)
        | - Ignore patterns to filter noise
        | - Database logging
        |
        | Channel behavior:
        | - User provides 'channel' in context: extracted and used
        |   Log::error('msg', ['channel' => 'SECURITY']) → Channel = SECURITY
        |
        | - No channel provided: Wonolog uses its default (DEBUG)
        |   Log::error('msg') → Channel = DEBUG
        |   Log::channel('stack')->error('msg') → Channel = DEBUG (Monolog channel ignored)
        |
        | - Channel is never duplicated in context
        |   After extraction, 'channel' key is removed from context
        |
        | - Monolog channels (stack, single, development) are NOT sent to Wonolog
        |   They only determine routing in Laravel's logging system
        |
        | - Custom channels don't need pre-configuration
        |   Any string can be used: SECURITY, HTTP, PAYMENT, etc.
        |
        | Usage: Log::channel('wonolog')->error('message')
        | With custom channel: Log::error('msg', ['channel' => 'SECURITY'])
        |
        */
        'wonolog' => [
            'driver' => 'monolog',
            'handler' => WonologHandler::class,
            'level' => \env('LOG_LEVEL', 'debug'),
            'processors' => [PsrLogMessageProcessor::class],
        ],

        /*
        |----------------------------------------------------------------------
        | File Channels
        |----------------------------------------------------------------------
        |
        | Local file backup for logs. Useful when Wonolog is temporarily
        | unavailable or for debugging.
        |
        */
        'single' => [
            'driver' => 'single',
            'path' => \storage_path('logs/laravel.log'),
            'level' => \env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => \storage_path('logs/laravel.log'),
            'level' => \env('LOG_LEVEL', 'debug'),
            'days' => \env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        /*
        |----------------------------------------------------------------------
        | External Service Channels
        |----------------------------------------------------------------------
        */
        'slack' => [
            'driver' => 'slack',
            'url' => \env('LOG_SLACK_WEBHOOK_URL'),
            'username' => \env('LOG_SLACK_USERNAME', 'Laravel Log'),
            'emoji' => \env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => \env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => \env('LOG_LEVEL', 'debug'),
            'handler' => \env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => \env('PAPERTRAIL_URL'),
                'port' => \env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.\env('PAPERTRAIL_URL').':'.\env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        /*
        |----------------------------------------------------------------------
        | System Channels
        |----------------------------------------------------------------------
        */
        'stderr' => [
            'driver' => 'monolog',
            'level' => \env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => \env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => \env('LOG_LEVEL', 'debug'),
            'facility' => \env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => \env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => \storage_path('logs/laravel.log'),
        ],
    ],

];
