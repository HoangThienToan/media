<?php

namespace Edu2work\Media;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Edu2work\Media\Client;
use Edu2work\Media\Config;

class MediaEdServiceProvider extends ServiceProvider
{

    protected $key ='';

    public function __construct(Config $Config)
    {
        $this->key = $Config->EduKey();
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Client::class, function ($app) {
            return new Client($this->key);
        });
    }

    /**
     * Boot the instance, add macros for datatable engines.
     *
     * @return void
     */
    public function boot()
    {
        $engines = (array) config('datatables.engines');
        foreach ($engines as $engine => $class) {
            $engine = Str::camel($engine);

            if (!method_exists(DataTables::class, $engine) && !DataTables::hasMacro($engine)) {
                DataTables::macro($engine, function () use ($class) {
                    if (!call_user_func_array([$class, 'canCreate'], func_get_args())) {
                        throw new \InvalidArgumentException();
                    }

                    return call_user_func_array([$class, 'create'], func_get_args());
                });
            }
        }
    }

    /**
     * Setup package assets.
     *
     * @return void
     */
    protected function setupAssets()
    {
        $this->mergeConfigFrom($config = __DIR__ . '/config/datatables.php', 'datatables');

        if ($this->app->runningInConsole()) {
            $this->publishes([$config => config_path('datatables.php')], 'datatables');
        }
    }

    /**
     * Check if app uses Lumen.
     *
     * @return bool
     */
    protected function isLumen()
    {
        return Str::contains($this->app->version(), 'Lumen');
    }
}

//demo with
//'providers' => [
//     // ...
//     edu2work\media\MediaHelperServiceProvider::class,
// ],
// 'aliases' => [
//     // ...
//     'Media' => edu2work\media\MediaHelper::class,
// ],
// {!! MediaHelper::renderMedia($imageUrl, $altText) !!}