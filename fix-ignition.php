<?php

/**
 * Fix for Spatie Laravel Ignition Service Provider Issue
 * 
 * This script fixes the "Class Spatie\LaravelIgnition\IgnitionServiceProvider not found" error
 * by ensuring the package is properly installed and configured.
 */

echo "🔧 إصلاح مشكلة Spatie Laravel Ignition...\n\n";

// Check if composer.json exists
if (!file_exists('composer.json')) {
    echo "❌ ملف composer.json غير موجود!\n";
    exit(1);
}

// Read composer.json
$composerJson = json_decode(file_get_contents('composer.json'), true);

// Check if spatie/laravel-ignition is in require-dev
$hasIgnition = isset($composerJson['require-dev']['spatie/laravel-ignition']);

if (!$hasIgnition) {
    echo "📦 إضافة spatie/laravel-ignition إلى composer.json...\n";
    
    // Add spatie/laravel-ignition to require-dev
    $composerJson['require-dev']['spatie/laravel-ignition'] = '^2.8';
    
    // Write back to composer.json
    file_put_contents('composer.json', json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    echo "✅ تم إضافة spatie/laravel-ignition إلى composer.json\n";
} else {
    echo "✅ spatie/laravel-ignition موجود في composer.json\n";
}

// Check if vendor directory exists
if (!is_dir('vendor')) {
    echo "📦 مجلد vendor غير موجود، سيتم إنشاؤه عند تشغيل composer install\n";
} else {
    echo "✅ مجلد vendor موجود\n";
}

// Check if ignition config exists
if (!file_exists('config/ignition.php')) {
    echo "⚙️ إنشاء ملف config/ignition.php...\n";
    
    $ignitionConfig = <<<'PHP'
<?php

return [
    'enabled' => env('IGNITION_ENABLED', env('APP_DEBUG', false)),
    'editor' => env('IGNITION_EDITOR', 'vscode'),
    'theme' => env('IGNITION_THEME', 'auto'),
    'sharing' => [
        'enabled' => env('IGNITION_SHARING_ENABLED', false),
        'anonymize_ips' => env('IGNITION_ANONYMIZE_IPS', true),
    ],
    'register_commands' => env('IGNITION_REGISTER_COMMANDS', app()->environment('local')),
    'ignored_solution_providers' => [],
    'enable_runnable_solutions' => env('IGNITION_ENABLE_RUNNABLE_SOLUTIONS', null),
    'remote_sites_path' => env('IGNITION_REMOTE_SITES_PATH', ''),
    'local_sites_path' => env('IGNITION_LOCAL_SITES_PATH', ''),
    'housekeeping_endpoint_prefix' => '_ignition',
    'settings_file_path' => '',
    'open_ai_key' => env('IGNITION_OPEN_AI_KEY'),
    'with_stack_trace' => env('IGNITION_WITH_STACK_TRACE', true),
    'hide_solutions' => env('IGNITION_HIDE_SOLUTIONS', false),
];
PHP;
    
    file_put_contents('config/ignition.php', $ignitionConfig);
    echo "✅ تم إنشاء ملف config/ignition.php\n";
} else {
    echo "✅ ملف config/ignition.php موجود\n";
}

// Check bootstrap/providers.php
if (file_exists('bootstrap/providers.php')) {
    $providers = file_get_contents('bootstrap/providers.php');
    
    if (strpos($providers, 'IgnitionServiceProvider') !== false) {
        echo "🔧 إزالة IgnitionServiceProvider من bootstrap/providers.php...\n";
        
        // Remove any reference to IgnitionServiceProvider
        $providers = preg_replace('/.*IgnitionServiceProvider.*\n?/', '', $providers);
        
        // Clean up the array format
        $providers = str_replace([
            "    \n    /*",
            "     */\n    ...(app()->environment('local') ? [\n    ] : []),\n"
        ], [
            "    /*",
            "     */\n"
        ], $providers);
        
        file_put_contents('bootstrap/providers.php', $providers);
        echo "✅ تم تنظيف bootstrap/providers.php\n";
    } else {
        echo "✅ bootstrap/providers.php نظيف\n";
    }
}

// Create a simple service provider registration
echo "🔧 إنشاء مقدم خدمة مخصص لـ Ignition...\n";

$customProvider = <<<'PHP'
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class IgnitionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Only register Ignition in local environment
        if ($this->app->environment('local') && class_exists(\Spatie\LaravelIgnition\IgnitionServiceProvider::class)) {
            $this->app->register(\Spatie\LaravelIgnition\IgnitionServiceProvider::class);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
PHP;

if (!file_exists('app/Providers/IgnitionServiceProvider.php')) {
    file_put_contents('app/Providers/IgnitionServiceProvider.php', $customProvider);
    echo "✅ تم إنشاء App\Providers\IgnitionServiceProvider\n";
}

// Update bootstrap/providers.php to use our custom provider
$providersContent = <<<'PHP'
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\IgnitionServiceProvider::class,
];
PHP;

file_put_contents('bootstrap/providers.php', $providersContent);
echo "✅ تم تحديث bootstrap/providers.php\n";

// Instructions
echo "\n🎯 الخطوات التالية:\n";
echo "1. قم بتشغيل: composer install\n";
echo "2. قم بتشغيل: php artisan config:clear\n";
echo "3. قم بتشغيل: php artisan cache:clear\n";
echo "4. اختبر التطبيق: php artisan serve\n\n";

echo "✅ تم إصلاح مشكلة Ignition بنجاح!\n";
echo "💡 ملاحظة: Ignition سيعمل فقط في البيئة المحلية (local)\n";
