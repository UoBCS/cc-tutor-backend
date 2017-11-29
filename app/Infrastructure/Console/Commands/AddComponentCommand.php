<?php

namespace App\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class AddComponentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'components:add {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds a new API component';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $componentName = strtolower($this->argument('name'));

        $templateData = [
            'singular'              => $componentName,
            'plural'                => str_plural($componentName),
            'singularCapitalized'   => ucfirst($componentName),
            'pluralCapitalized'     => ucfirst(str_plural($componentName))
        ];

        $rootPath = app_path('Api/' . $templateData['pluralCapitalized']);

        $templateRootPath = app_path('Infrastructure/Api/ComponentExample');

        $loader = new \Twig_Loader_Filesystem($templateRootPath);

        $twig = new \Twig_Environment($loader);

        // Create root directory
        // -----------------------------------------------------------------------------------------
        if (File::exists($rootPath)) {
            $this->error("The directory $rootPath already exists");
            return 1;
        }

        $result = File::makeDirectory($rootPath);

        if (!$result) {
            $this->error("Could not create directory $rootPath");
            return 1;
        }

        // Create files
        // -----------------------------------------------------------------------------------------
        $files = File::allFiles($templateRootPath);

        foreach ($files as $file) {
            $fileDir = dirname($file->getRelativePathname());

            if (!File::exists($rootPath . '/' . $fileDir)) {
                File::makeDirectory($rootPath . '/' . $fileDir);
            }

            $newFileName = str_replace('Component', $templateData['singularCapitalized'], $file->getFilename());
            File::put($rootPath . '/' . $fileDir . '/' . $newFileName, $twig->render($file->getRelativePathname(), $templateData));
        }

        $this->info('Component generated successfully in app/Api/' . $templateData['pluralCapitalized']);

        // Make migration
        // -----------------------------------------------------------------------------------------
        Artisan::call('make:migration', [
            'name' => 'create_' . $templateData['plural'] . '_table'
        ]);

        // Create tests
        // -----------------------------------------------------------------------------------------

        return 0;
    }
}
