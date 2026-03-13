<?php
namespace Sosupp\SlimerMetrics;

use Illuminate\Support\ServiceProvider;

class SlimerMetricsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/slimermetrics.php',
            'slimer-metrics'
        );
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/slimermetrics.php'
            => config_path('slimermetrics.php')
        ], 'slimer-metrics-config');
    }
}