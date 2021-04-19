<?php
namespace Cms;

use Cms\Modules\CoreServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use File;

class CmsServiceProvider extends ServiceProvider
{
    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [];

    /**
     * Bootstrap services.
     *
     * @param \Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function boot(Router $router)
    {
        // SPECIFIED KEY WAS TOO LONG ERROR OLD MYSQL SERVER
        Schema::defaultStringLength(191);

        // FORCE SSL
        if(config('app.force_ssl')) {
            URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS','on');
        }

        // CORE HELPER AUTOLOAD
        if (file_exists(__DIR__ . '/helpers.php'))
            include __DIR__ . '/helpers.php';

        // CMS
        $modulesDIR = __DIR__ . '/Modules';
        $modules = array_map('basename', File::directories($modulesDIR));
        foreach ($modules as $module) {
            $routePath = $modulesDIR  . '/' . $module . '/routes.php';
            $viewPath = $modulesDIR . '/' . $module . '/Views';
            $migrationPath = $modulesDIR . '/' . $module . '/Database/Migrations';
            $configPath = $modulesDIR . '/' . $module . '/Config';

            // MODULE HELPER AUTOLOAD
            if (file_exists($modulesDIR . '/' . $module . '/helpers.php'))
                include $modulesDIR . '/' . $module . '/helpers.php';

            // LOAD MODULES ROUTES
            if (file_exists($routePath))
                $this->loadRoutesFrom($routePath);

            // LOAD MODULES VIEW
            if (is_dir($viewPath))
                $this->loadViewsFrom($viewPath, $module);

            // LOAD MODULES MIGRATION
            if (is_dir($migrationPath))
                $this->loadMigrationsFrom($migrationPath);

            // LOAD MODULES CONFIG (WILL BE OVERRIDE LARAVEL CONFIG)
            if (is_dir($configPath)) {
                $configFiles = scandir($configPath);
                foreach ($configFiles as $config) {
                    if (pathinfo($config, PATHINFO_EXTENSION) === 'php') {
                        $key  = basename($config, '.php');
                        $path = $configPath . '/' . $config;

                        if (!$this->app->runningInConsole()) {
                            if (!$this->app->configurationIsCached()) {
                                $this->app['config']->set($key, require $path);
                            }
                        } else {
                            if (\Request::server('argv')[1] === 'config:cache') {
                                $this->app['config']->set($key, require $path);
                            }
                        }
                    }
                }
            }
        }

        // CMS MIDDLEWARE REGISTER
        foreach($this->routeMiddleware as $name => $class) {
            $router->aliasMiddleware($name, $class);
        }

        // MODULE SCHEDULE REGISTER
//        $this->app->booted(function () {
//            $schedule = app(Schedule::class);
//            $schedule->command('subscription:cron')->dailyAt('00:00')->when(function () {
//                return \Carbon\Carbon::now()->endOfMonth()->isToday();
//            });
//            $schedule->command('transaction:delete:cron')->dailyAt('03:00');
//            $schedule->command('zoom:delete:account')->dailyAt('4:00');
//        });

        //EXCEPTIONS HANDLER
//        $this->app->singleton(
//            ExceptionHandler::class
//        );
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(CoreServiceProvider::class);
    }
}
