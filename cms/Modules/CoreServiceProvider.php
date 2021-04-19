<?php
namespace Cms\Modules;

use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'cms.guest' => \Cms\Modules\Core\Middleware\RedirectIfAuthenticated::class,
        'role' => \Zizaco\Entrust\Middleware\EntrustRole::class,
        'permission' => \Zizaco\Entrust\Middleware\EntrustPermission::class,
        'ability' => \Zizaco\Entrust\Middleware\EntrustAbility::class,
    ];

    /**
     * Bootstrap services.
     *
     * @param \Illuminate\Routing\Router $router
     *
     * @return void
     */

    public function register()
    {
        parent::register(); // TODO: Change the autogenerated stub
    }
}
