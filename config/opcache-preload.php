<?php

/**
 * OPcache Preload Configuration for Laravel
 * 
 * This file preloads commonly used Laravel classes and dependencies
 * to improve performance by keeping them in memory.
 */

if (!function_exists('opcache_compile_file') || !ini_get('opcache.enable')) {
    return;
}

// Set error reporting to avoid issues during preloading
$errorReporting = error_reporting();
error_reporting(E_ERROR);

try {
    // Base Laravel paths
    $baseDir = dirname(__DIR__);
    $vendorDir = $baseDir . '/vendor';
    
    // Check if vendor directory exists
    if (!is_dir($vendorDir)) {
        return;
    }
    
    // Autoloader
    require_once $vendorDir . '/autoload.php';
    
    // Core Laravel classes to preload
    $laravelClasses = [
        // Framework Core
        'Illuminate\Foundation\Application',
        'Illuminate\Container\Container',
        'Illuminate\Contracts\Foundation\Application',
        'Illuminate\Support\ServiceProvider',
        'Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables',
        'Illuminate\Foundation\Bootstrap\LoadConfiguration',
        'Illuminate\Foundation\Bootstrap\HandleExceptions',
        'Illuminate\Foundation\Bootstrap\RegisterFacades',
        'Illuminate\Foundation\Bootstrap\RegisterProviders',
        'Illuminate\Foundation\Bootstrap\BootProviders',
        
        // HTTP & Routing
        'Illuminate\Http\Request',
        'Illuminate\Http\Response',
        'Illuminate\Routing\Router',
        'Illuminate\Routing\Route',
        'Illuminate\Routing\RouteCollection',
        'Illuminate\Routing\Controller',
        'Illuminate\Foundation\Http\Kernel',
        
        // Database
        'Illuminate\Database\Eloquent\Model',
        'Illuminate\Database\Query\Builder',
        'Illuminate\Database\Eloquent\Builder',
        'Illuminate\Database\Schema\Blueprint',
        'Illuminate\Database\Migrations\Migration',
        
        // Cache & Session
        'Illuminate\Cache\CacheManager',
        'Illuminate\Session\SessionManager',
        'Illuminate\Redis\RedisManager',
        
        // Views & Blade
        'Illuminate\View\Factory',
        'Illuminate\View\View',
        'Illuminate\View\Compilers\BladeCompiler',
        
        // Validation
        'Illuminate\Validation\Validator',
        'Illuminate\Validation\Factory',
        
        // Support Classes
        'Illuminate\Support\Collection',
        'Illuminate\Support\Str',
        'Illuminate\Support\Arr',
        'Illuminate\Support\Carbon',
        'Illuminate\Support\Facades\Facade',
        
        // Configuration
        'Illuminate\Config\Repository',
        
        // Events
        'Illuminate\Events\Dispatcher',
        
        // Logging
        'Illuminate\Log\LogManager',
        
        // Authentication
        'Illuminate\Auth\AuthManager',
        'Illuminate\Auth\SessionGuard',
        
        // Mail
        'Illuminate\Mail\MailManager',
        
        // Queue
        'Illuminate\Queue\QueueManager',
        
        // File System
        'Illuminate\Filesystem\FilesystemManager',
    ];
    
    // Inertia.js classes
    $inertiaClasses = [
        'Inertia\Inertia',
        'Inertia\Response',
        'Inertia\ServiceProvider',
    ];
    
    // JWT classes
    $jwtClasses = [
        'PHPOpenSourceSaver\JWTAuth\JWTAuth',
        'PHPOpenSourceSaver\JWTAuth\Manager',
        'PHPOpenSourceSaver\JWTAuth\Factory',
    ];
    
    // Combine all classes
    $classesToPreload = array_merge($laravelClasses, $inertiaClasses, $jwtClasses);
    
    // Preload classes
    foreach ($classesToPreload as $class) {
        try {
            if (class_exists($class)) {
                opcache_compile_file((new ReflectionClass($class))->getFileName());
            }
        } catch (Exception $e) {
            // Silently ignore classes that can't be preloaded
            continue;
        }
    }
    
    // Preload commonly used files
    $filesToPreload = [
        $vendorDir . '/laravel/framework/src/Illuminate/Foundation/helpers.php',
        $vendorDir . '/laravel/framework/src/Illuminate/Support/helpers.php',
        $baseDir . '/app/Http/Kernel.php',
        $baseDir . '/app/Providers/AppServiceProvider.php',
        $baseDir . '/app/Providers/RouteServiceProvider.php',
        $baseDir . '/bootstrap/app.php',
    ];
    
    foreach ($filesToPreload as $file) {
        if (file_exists($file)) {
            try {
                opcache_compile_file($file);
            } catch (Exception $e) {
                // Silently ignore files that can't be preloaded
                continue;
            }
        }
    }
    
    // Preload application models (if they exist)
    $modelsDir = $baseDir . '/app/Models';
    if (is_dir($modelsDir)) {
        $modelFiles = glob($modelsDir . '/*.php');
        foreach ($modelFiles as $file) {
            try {
                opcache_compile_file($file);
            } catch (Exception $e) {
                continue;
            }
        }
    }
    
    // Preload application controllers
    $controllersDir = $baseDir . '/app/Http/Controllers';
    if (is_dir($controllersDir)) {
        $controllerFiles = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($controllersDir)
        );
        
        foreach ($controllerFiles as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                try {
                    opcache_compile_file($file->getRealPath());
                } catch (Exception $e) {
                    continue;
                }
            }
        }
    }
    
} catch (Exception $e) {
    // Log preload errors if needed
    error_log('OPcache preload error: ' . $e->getMessage());
} finally {
    // Restore error reporting
    error_reporting($errorReporting);
} 