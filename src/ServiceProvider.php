<?php
namespace HXC\LaravelGenerate;

use HXC\LaravelGenerate\Commands\Curd;
use HXC\LaravelGenerate\commands\MakeRepository;
use HXC\LaravelGenerate\Controller\GenerateController;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = true;

    /**
     * Boot the provider.
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/View','hxc');
        $this->loadRoutesFrom(__DIR__.'/Router/Router.php');
        $this->publishes([
            __DIR__.'/Config/Generate.php' => config_path('generate.php')
        ],'hxc-generate-config');
        $this->publishes([
            __DIR__.'/Helper/Functions.php' => app_path('Helper/Functions.php')
        ],'hxc-generate-functions');
    }

    /**
     * Register the provider.
     */
    public function register()
    {
        $this->app->make(GenerateController::class);
        $this->app->singleton(TestController::class,function($app){
            return new TestController();
        });
        $this->app->alias(TestController::class,'HXCLaravelGenerate');
        if($this->app->runningInConsole()){
            $this->commands([
                Curd::class,
                MakeRepository::class
            ]);
        }
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [TestController::class,'HXCLaravelGenerate'];
    }
}
