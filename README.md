![PHP Version](https://img.shields.io/packagist/php-v/wp-spaghetti/wonolog-handler)
![Packagist Downloads](https://img.shields.io/packagist/dt/wp-spaghetti/wonolog-handler)
![Packagist Stars](https://img.shields.io/packagist/stars/wp-spaghetti/wonolog-handler)
![GitHub Actions Workflow Status](https://github.com/wp-spaghetti/wonolog-handler/actions/workflows/release.yml/badge.svg)
![Coverage Status](https://img.shields.io/codecov/c/github/wp-spaghetti/wonolog-handler)
![Known Vulnerabilities](https://snyk.io/test/github/wp-spaghetti/wonolog-handler/badge.svg)
![GitHub Issues](https://img.shields.io/github/issues/wp-spaghetti/wonolog-handler)

![GitHub Release](https://img.shields.io/github/v/release/wp-spaghetti/wonolog-handler)
![License](https://img.shields.io/github/license/wp-spaghetti/wonolog-handler)
<!--
Qlty @see https://github.com/badges/shields/issues/11192
![GitHub Downloads (all assets, all releases)](https://img.shields.io/github/downloads/wp-spaghetti/wonolog-handler/total)
![Code Climate](https://img.shields.io/codeclimate/maintainability/wp-spaghetti/wonolog-handler)
![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen)
-->

# Wonolog Handler

Monolog handler that forwards Laravel logs to [Inpsyde's Wonolog](https://github.com/inpsyde/Wonolog) - the professional WordPress logging solution.

Works with any Laravel + WordPress setup: [Acorn](https://github.com/roots/acorn) (w/ or w/o [Sage](https://github.com/roots/sage)), [WP Starter](https://wpstarter.dev/), [Corcel](https://github.com/corcel/corcel), or custom integrations.

## Features

- **Clean Laravel Syntax** - Use `Log::error()`, `Log::info()`, etc. anywhere in your code
- **Graceful Degradation** - Works with or without Wonolog active
- **wpify/scoper Support** - Automatically detects scoped Wonolog namespace
- **Zero Configuration** - Works out of the box with sensible defaults
- **Flexible Propagation** - Control whether to stop at Wonolog or continue to other handlers

## Requirements

- PHP >= 8.2
- WordPress >= 6.0
- Laravel Illuminate/Support ^10.0|^11.0|^12.0
- Monolog ^2.0|^3.0

**Note:** Inpsyde's Wonolog is **not** required for the package to work. Without Wonolog, logs gracefully pass through to other handlers in your stack (e.g., file logging).

## Installation

### 1. Install the handler package

In your Laravel + WordPress project (Sage theme, WP Starter, etc.):

```bash
composer require wp-spaghetti/wonolog-handler
```

The package auto-registers via service provider discovery.

### 2. Install WP Spaghetti Wonolog mu-plugin (optional)

For email notifications, sensitive data filtering, and advanced logging features, install the [WP Spaghetti Wonolog mu-plugin](https://github.com/wp-spaghetti/wonolog), that provides a complete logging solution with production-ready configuration.

See the [WP Spaghetti Wonolog documentation](https://github.com/wp-spaghetti/wonolog) for setup and configuration options.

### 3. Configure logging

Update your `config/logging.php`:

```php
<?php

use WpSpaghetti\WonologHandler\Handler\WonologHandler;

return [
    'default' => env('LOG_CHANNEL', 'stack'),

    'channels' => [
        // Recommended: Stack with Wonolog + file backup
        'stack' => [
            'driver' => 'stack',
            'channels' => ['wonolog', 'single'],
            'ignore_exceptions' => false,
        ],

        // Wonolog channel
        'wonolog' => [
            'driver' => 'monolog',
            'handler' => WonologHandler::class,
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        // File backup (optional but recommended)
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
    ],
];
```

See [`examples/logging.php`](examples/logging.php) for a complete configuration example.

## Usage

### Basic Logging

```php
use Illuminate\Support\Facades\Log;

// Anywhere in your Laravel + WordPress code
Log::debug('Debugging information');
Log::info('Informational message');
Log::notice('Normal but significant event');
Log::warning('Warning condition');
Log::error('Error condition');
Log::critical('Critical condition');
Log::alert('Action must be taken immediately');
Log::emergency('System is unusable');
```

### With Context

```php
Log::error('Payment failed', [
    'user_id' => $userId,
    'amount' => $amount,
    'error' => $exception->getMessage(),
]);

// Wonolog channels - use UPPERCASE by convention
Log::error('Security breach', [
    'channel' => 'SECURITY',  // ✅ Correct
    'ip' => $ipAddress,
]);

// Avoid lowercase - may not be tracked
// Log::error('Breach', ['channel' => 'security']); // ❌ May not work
```

### Channel Selection

```php
// Use only Wonolog (no file backup)
Log::channel('wonolog')->error('Critical error');

// Use only file logging
Log::channel('single')->debug('Debug info');

// Use stack (Wonolog + file) - recommended
Log::channel('stack')->warning('Warning message');
```

## Framework-Specific Examples

### Sage Themes (Acorn)

```php
// In app/Controllers/App.php or any controller
use Illuminate\Support\Facades\Log;

public function index()
{
    Log::info('Page viewed', ['url' => request()->url()]);
    return $this->view;
}
```

### WP Starter

```php
// In your custom plugins or theme
use Illuminate\Support\Facades\Log;

add_action('init', function() {
    Log::info('WordPress initialized');
});
```

### Corcel

```php
use Corcel\Model\Post;
use Illuminate\Support\Facades\Log;

$posts = Post::published()->get();
Log::info('Fetched posts', ['count' => $posts->count()]);
```

See [`examples/usage.php`](examples/usage.php) for more real-world examples including WordPress hooks, WooCommerce integration, API logging, and performance monitoring.

## Advanced Configuration

### Publish Configuration

To customize settings, publish the config:

```bash
# Sage/Acorn
wp acorn vendor:publish --tag=wonolog-handler-config

# WP Starter (using Laravel's artisan)
php vendor/bin/wp-starter vendor:publish --tag=wonolog-handler-config
```

This creates `config/wonolog.php` in your project:

```php
<?php

return [
    // Custom Wonolog namespace (for wpify/scoper)
    'namespace' => env('WONOLOG_NAMESPACE', 'Inpsyde\\Wonolog'),
    
    // Custom action hook (for wpify/scoper or custom naming)
    'action' => env('WONOLOG_ACTION', 'wonolog.log'),
    
    // Stop propagation when Wonolog is active?
    'stop_propagation' => env('WONOLOG_STOP_PROPAGATION', false),
];
```

### Custom Wonolog Namespace (for wpify/scoper)

If Wonolog is scoped, override the namespace:

**Via config:**
```php
// config/wonolog.php
'namespace' => 'WpSpaghetti\\Deps\\Inpsyde\\Wonolog',
```

**Via filter:**
```php
add_filter('wonolog_handler.namespace', function () {
    return 'WpSpaghetti\\Deps\\Inpsyde\\Wonolog';
});
```

**Via environment:**
```env
WONOLOG_NAMESPACE="WpSpaghetti\\Deps\\Inpsyde\\Wonolog"
```

### Custom Action Hook (for wpify/scoper)

If Wonolog uses a custom action hook name:

**Via config:**
```php
// config/wonolog.php
'action' => 'custom_wonolog.log',
```

**Via filter:**
```php
add_filter('wonolog_handler.action', function () {
    return 'custom_wonolog.log';
});
```

**Via environment:**
```env
WONOLOG_ACTION="custom_wonolog.log"
```

### Control Log Propagation

By default, logs continue to other handlers in the stack after Wonolog (allowing file backup). You can change this:

**Stop at Wonolog (no file backup):**
```php
// config/wonolog.php
'stop_propagation' => true,
```

Or via environment:
```env
WONOLOG_STOP_PROPAGATION=true
```

**Use cases:**
- `false` (default): Wonolog + file backup - recommended for production
- `true`: Only Wonolog - if you don't want file logs and trust Wonolog completely

## How It Works

### Architecture

```
Laravel Log::error()
    ↓
Monolog LogRecord
    ↓
WonologHandler
    ↓ (if Wonolog active)
do_action('wonolog.log')
    ↓
Wonolog Processing
    ├─ Email notifications
    ├─ WordPress database
    ├─ Custom handlers
    └─ Filtering/redaction
    
    ↓ (if stop_propagation=false)
Continue to next handler (file, Slack, etc.)
```

### Technical Details

**Channel Handling:**
- Wonolog expects `channel` at the **top level** of the action array, not inside `context`
- If user provides `'channel' => 'SECURITY'` in context, it's extracted and moved to top level
- The channel is removed from context after extraction to avoid duplication
- If no channel is provided, it's NOT passed to Wonolog (Wonolog uses its default: DEBUG)
- Monolog's channel (e.g., `stack`, `development`) is NEVER used - it has nothing to do with Wonolog

**Example behavior:**
```php
// User specifies Wonolog channel
Log::error('Error', ['channel' => 'SECURITY', 'ip' => '1.2.3.4']);
// Result: Channel = SECURITY, Context = ['ip' => '1.2.3.4'] (no 'channel' key)

// No channel specified
Log::error('Error');
// Result: Channel = DEBUG (Wonolog's default), Context = [] (empty except datetime/extra)

// Monolog channel is ignored
Log::channel('stack')->error('Error');
// Result: Channel = DEBUG (Wonolog's default), NOT 'stack'
```

**PSR-3 Placeholder Compatibility:**
- The handler uses array format when calling `do_action('wonolog.log', [...])`
- This forces Wonolog's `HookLogFactory::fromArray()` method instead of `fromString()`
- Fixes PSR-3 placeholder substitution (e.g., `{url}`, `{handle}`) which breaks in `fromString()`
- Compatible with all Wonolog v2.x and v3.x versions

**Extra Data:**
- Monolog's `extra` data is passed as `$context['extra']` (following Wonolog's convention)
- Datetime is passed as `$context['datetime']` for full compatibility

### Graceful Degradation

**Without Wonolog mu-plugin:**
- WonologHandler detects Wonolog is not active
- Handler does nothing and returns `false`
- Logs continue to other handlers (files, etc.)
- No errors or warnings

**With Wonolog mu-plugin:**
- Handler forwards logs to Wonolog
- Wonolog processes with email, filtering, etc.
- Logs optionally continue to file backup (based on `stop_propagation`)

### Namespace Detection

The handler automatically detects Wonolog's namespace:

1. Checks default `Inpsyde\Wonolog`
2. Applies filter `wonolog_handler_namespace`
3. Supports scoped namespaces from wpify/scoper
4. Verifies `Configurator::ACTION_SETUP` was triggered
5. Caches result for performance

## Troubleshooting

### Logs not appearing in Wonolog

Check if Wonolog is active:

```php
use WpSpaghetti\WonologHandler\Support\WonologDetector;

$detector = app(WonologDetector::class);

if (!$detector->isActive()) {
    echo "Wonolog is not active!";
    echo "Namespace: " . $detector->getNamespace();
    echo "Action: " . $detector->getAction();
}
```

### Wrong namespace or action detected

Override via filter or config (see [Advanced Configuration](#advanced-configuration)).

### Channel-related issues

Understanding channel behavior:

1. **Custom Wonolog channel** (when explicitly provided):
   ```php
   Log::error('Security breach', ['channel' => 'SECURITY', 'ip' => '1.2.3.4']);
   // Email: Channel = SECURITY, Context = ['ip' => '1.2.3.4'] (no 'channel' key)
   ```

2. **Default Wonolog channel** (when not provided):
   ```php
   Log::error('Error');
   // Email: Channel = DEBUG (Wonolog's default)
   
   Log::channel('stack')->error('Error');
   // Email: Channel = DEBUG (Wonolog's default - Monolog channel is ignored)
   
   Log::channel('single')->error('Error');
   // Email: Channel = DEBUG (Wonolog's default - Monolog channel is ignored)
   ```

3. **Channel extraction**:
   - If `'channel'` is in context, it's extracted and passed to Wonolog at top level
   - The `'channel'` key is removed from context to avoid duplication
   - This ensures channel appears only once in emails (as "Channel: XXX", not in context)

4. **Monolog vs Wonolog channels**:
   - Monolog channels (`development`, `stack`, `single`) route logs in Laravel
   - Wonolog channels (`DEBUG`, `SECURITY`, `HTTP`) categorize logs in Wonolog
   - They are completely separate - Monolog channels are NOT sent to Wonolog
   - To set a Wonolog channel: `Log::error('msg', ['channel' => 'SECURITY'])`

5. **Channel naming conventions** ⚠️:
   - **IMPORTANT**: Wonolog uses UPPERCASE channel names by convention
   - Standard Wonolog channels: `DEBUG`, `SECURITY`, `HTTP`, `DB`, `PHP-ERROR`, `CRON`, etc.
   - Using lowercase (e.g., `'security'` instead of `'SECURITY'`) may cause logs not to be tracked
   - Using non-configured channels (e.g., `'FOO'`) may also not be tracked
   - This behavior depends on your Wonolog configuration and filters
   - **Best practice**: Always use UPPERCASE for channel names
   
   ```php
   // ✅ Correct - uppercase
   Log::error('Breach', ['channel' => 'SECURITY']);
   
   // ❌ May not work - lowercase
   Log::error('Breach', ['channel' => 'security']);
   
   // ❌ May not work - non-configured channel
   Log::error('Error', ['channel' => 'FOO']);
   ```

6. **Custom channel names**:
   - You can use custom channel names if configured in Wonolog
   - Examples: `PAYMENT`, `API`, `WOOCOMMERCE`, etc.
   - Make sure they're configured in your Wonolog setup
   - Always use UPPERCASE for consistency

### Logs not in file backup

Ensure `'single'` channel is in the stack:

```php
'stack' => [
    'driver' => 'stack',
    'channels' => ['wonolog', 'single'], // ← Check this
],
```

And ensure `stop_propagation` is `false` (default).

## Testing

```bash
composer test
```

## More info

See [LINKS](docs/LINKS.md) file.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for a detailed list of changes for each release.

We follow [Semantic Versioning](https://semver.org/) and use [Conventional Commits](https://www.conventionalcommits.org/) to automatically generate our changelog.

### Release Process

- **Major versions** (1.0.0 → 2.0.0): Breaking changes
- **Minor versions** (1.0.0 → 1.1.0): New features, backward compatible
- **Patch versions** (1.0.0 → 1.0.1): Bug fixes, backward compatible

All releases are automatically created when changes are pushed to the `main` branch, based on commit message conventions.

## Contributing

For your contributions please use:

- [Conventional Commits](https://www.conventionalcommits.org)
- [Pull request workflow](https://docs.github.com/en/get-started/exploring-projects-on-github/contributing-to-a-project)

See [CONTRIBUTING](.github/CONTRIBUTING.md) for detailed guidelines.

## Sponsor

[<img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" width="200" alt="Buy Me A Coffee">](https://buymeacoff.ee/frugan)

## License

(ɔ) Copyleft 2026 [Frugan](https://frugan.it).  
[GNU GPLv3](https://choosealicense.com/licenses/gpl-3.0/), see [LICENSE](LICENSE) file.
