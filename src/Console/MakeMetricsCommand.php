<?php

namespace Sosupp\SlimerMetrics\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;


class MakeMetricsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'slimer:make-metrics {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add metrics to utilize the slimer metrics package';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get argument provided
        $name = $this->argument('name');
        $fileName = str($name)->pascal()->value;

        // Create class using stub
        $stubPath = __DIR__ . "/Stubs/MetricsTemplate.stub";
        $stub = file_get_contents($stubPath);

        $targetDir = app_path("Services/Slimer/Metrics");
        $targetFile = "{$targetDir}/{$fileName}.php";

        if (!File::exists($stubPath)) {
            $this->error("Invalid source file: {$fileName}");
            return;
        }

        if (File::exists($targetFile) && ! $this->option('force')) {
            $this->warn("File {$fileName} already exists. Use --force to overwrite.");
            return;
        }

        File::ensureDirectoryExists($targetDir);

        $content = str_replace(
            ['{{ namespace }}', '{{ class }}'],
            ["App\\Services\\Slimer\\Metrics", $fileName],
            $stub
        );

        file_put_contents($targetFile, $content);
        $this->info("{$fileName} created successfully.");

    }
}
