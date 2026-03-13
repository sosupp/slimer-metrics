<?php
namespace Sosupp\SlimerMetrics;

use Illuminate\Support\ServiceProvider;
use Sosupp\SlimerMetrics\Console\MakeMetricsCommand;

class SlimerMetricsServiceProvider extends ServiceProvider
{
    public function register()
    {
        
    }

    public function boot()
    {
        if($this->app->runningInConsole()){
            $this->publishes([
                __DIR__.'/../config/slimermetrics.php'
                => config_path('slimermetrics.php')
            ], 'slimer-metrics-config');

            // Commands
            $this->customCommands();
        }
    }

    protected function customCommands()
    {
        $this->commands([
            MakeMetricsCommand::class,
        ]);
    }


}