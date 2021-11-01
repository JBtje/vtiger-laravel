<?php

namespace JBtje\VtigerLaravel;

use Illuminate\Support\ServiceProvider;

class VtigerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes( [
            __DIR__ . '/../config/vtiger.php' => config_path( 'vtiger.php' ),
        ], 'vtiger' );

        // use the vendor configuration file as fallback
        $this->mergeConfigFrom(
            __DIR__ . '/../config/vtiger.php', 'vtiger'
        );
    }

    public function register()
    {
        $this->app->bind( Vtiger::class, function() {
            return new Vtiger();
        } );

        config( [
            'config/vtiger.php',
        ] );
    }
}
