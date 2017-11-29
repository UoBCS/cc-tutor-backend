<?php

namespace App\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RemoveComponentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'components:remove {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes an API component';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $componentName = ucfirst($this->argument('name'));
        $componentNamePlural = str_plural($componentName);

        $rootPath = app_path("Api/$componentNamePlural");

        if (!File::deleteDirectory($rootPath)) {
            $this->error('The component does not exist');
            return 1;
        }

        $this->info('Component removed successfully');
        return 0;
    }
}
