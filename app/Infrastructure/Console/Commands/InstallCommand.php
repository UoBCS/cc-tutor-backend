<?php

namespace App\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cctutor:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs the CC Tutor components (e.g. compiler construction assistant).';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Cloning repository...');
        $repo = \Cz\Git\GitRepository::cloneRepository('https://github.com/UoBCS/cc-tutor-cca-assignments.git', storage_path('app/just-a-test'));
        $this->info('Cloning completed.');

        return 0;
    }
}
