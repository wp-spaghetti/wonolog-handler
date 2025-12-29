<?php

/**
 * Wonolog Handler - Usage Examples
 *
 * This file demonstrates various ways to use the package in your Sage theme.
 */

declare(strict_types=1);

/*
 * This file is part of the Wonolog Handler package.
 *
 * (ɔ) Frugan <dev@frugan.it>
 *
 * This source file is subject to the GNU GPLv3 license that is bundled
 * with this source code in the file COPYING.
 */

namespace App\Examples;

use Illuminate\Support\Facades\Log;

class LoggingExamples
{
    /**
     * Basic logging examples
     */
    public function basicLogging(): void
    {
        // Different log levels
        Log::debug('Debug information for development');
        Log::info('User logged in successfully');
        Log::notice('Disk space running low');
        Log::warning('Deprecated function called');
        Log::error('Failed to connect to API');
        Log::critical('Database connection lost');
        Log::alert('Website is down!');
        Log::emergency('Server is on fire!');
    }

    /**
     * Logging with context
     */
    public function contextualLogging(): void
    {
        $userId = 123;
        $amount = 99.99;

        Log::info('Payment processed', [
            'user_id' => $userId,
            'amount' => $amount,
            'currency' => 'EUR',
            'gateway' => 'stripe',
        ]);

        // Exception logging
        try {
            throw new \Exception('Something went wrong');
        } catch (\Exception $e) {
            Log::error('Operation failed', [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Channel-specific logging
     */
    public function channelLogging(): void
    {
        // Use only Wonolog
        Log::channel('wonolog')->error('Critical error');

        // Use only file
        Log::channel('single')->debug('Debug info');

        // Use stack (default - both Wonolog and file)
        Log::channel('stack')->warning('Warning message');
    }

    /**
     * WordPress integration examples
     */
    public function wordpressIntegration(): void
    {
        // Log in WordPress hooks
        \add_action('wp_login', function ($userLogin, $user): void {
            Log::info('User login', [
                'username' => $userLogin,
                'user_id' => $user->ID,
                'ip' => $_SERVER['REMOTE_ADDR'],
            ]);
        }, 10, 2);

        // Log failed login attempts
        \add_action('wp_login_failed', function ($username): void {
            Log::warning('Failed login attempt', [
                'username' => $username,
                'ip' => $_SERVER['REMOTE_ADDR'],
            ]);
        });

        // Log post updates
        \add_action('save_post', function ($postId, $post): void {
            if (\wp_is_post_revision($postId)) {
                return;
            }

            Log::info('Post updated', [
                'post_id' => $postId,
                'post_title' => $post->post_title,
                'post_type' => $post->post_type,
                'author_id' => $post->post_author,
            ]);
        }, 10, 2);
    }

    /**
     * WooCommerce integration examples
     */
    public function woocommerceIntegration(): void
    {
        // Log order creation
        \add_action('woocommerce_new_order', function ($orderId): void {
            $order = \wc_get_order($orderId);

            Log::info('New order created', [
                'order_id' => $orderId,
                'total' => $order->get_total(),
                'status' => $order->get_status(),
                'customer_id' => $order->get_customer_id(),
            ]);
        });

        // Log payment failures
        \add_action('woocommerce_order_status_failed', function ($orderId): void {
            $order = \wc_get_order($orderId);

            Log::error('Order payment failed', [
                'order_id' => $orderId,
                'total' => $order->get_total(),
                'payment_method' => $order->get_payment_method(),
            ]);
        });

        // Log refunds
        \add_action('woocommerce_order_refunded', function ($orderId, $refundId): void {
            Log::notice('Order refunded', [
                'order_id' => $orderId,
                'refund_id' => $refundId,
            ]);
        }, 10, 2);
    }

    /**
     * API integration logging
     */
    public function apiIntegration(): void
    {
        // Log API requests
        $response = \wp_remote_get('https://api.example.com/data');

        if (\is_wp_error($response)) {
            Log::error('API request failed', [
                'url' => 'https://api.example.com/data',
                'error' => $response->get_error_message(),
            ]);
        } else {
            Log::debug('API request successful', [
                'url' => 'https://api.example.com/data',
                'status_code' => \wp_remote_retrieve_response_code($response),
            ]);
        }
    }

    /**
     * Performance monitoring
     */
    public function performanceMonitoring(): void
    {
        $startTime = \microtime(true);

        // Some operation
        \sleep(2);

        $duration = \microtime(true) - $startTime;

        if ($duration > 1.0) {
            Log::warning('Slow operation detected', [
                'duration' => $duration,
                'operation' => 'database_query',
            ]);
        }
    }

    /**
     * Security event logging
     */
    public function securityLogging(): void
    {
        // Log suspicious activity
        $failedAttempts = \get_transient('failed_login_attempts_'.$_SERVER['REMOTE_ADDR']);

        if ($failedAttempts > 5) {
            Log::alert('Multiple failed login attempts', [
                'ip' => $_SERVER['REMOTE_ADDR'],
                'attempts' => $failedAttempts,
            ]);
        }

        // Log file upload
        \add_filter('wp_handle_upload_prefilter', function ($file) {
            Log::info('File upload', [
                'filename' => $file['name'],
                'type' => $file['type'],
                'size' => $file['size'],
                'user_id' => \get_current_user_id(),
            ]);

            return $file;
        });
    }

    /**
     * Custom formatting
     */
    public function customFormatting(): void
    {
        // JSON context
        Log::info('User data', [
            'user' => [
                'id' => 123,
                'email' => 'user@example.com',
                'roles' => ['customer'],
            ],
        ]);

        // Array data
        Log::debug('Cart contents', [
            'items' => [
                ['id' => 1, 'quantity' => 2],
                ['id' => 5, 'quantity' => 1],
            ],
            'total_items' => 3,
        ]);
    }
}
