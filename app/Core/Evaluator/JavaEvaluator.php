<?php

namespace App\Core\Evaluator;

class JavaEvaluator implements Evaluator
{
    private $files;

    public function __construct(string $projectDirectory)
    {
        $this->files = File::allFiles($projectDirectory);
        // Storage::disk('local')
    }

    public static function saveProject()
    {
        // Save files is Storage::put('file', 'contents');
    }
}
