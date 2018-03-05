<?php

namespace App\Api\Phases\Services;

use App\Core\Lexer\Lexer;
use App\Core\Parser\DeterministicParser;
use App\Core\Parser\LL1;
use Illuminate\Support\Facades\Storage;

class SemanticAnalysisService
{
    private $filesPath = 'cctutor/src/main/java/com/cctutor/app';
    private $astPath = 'ast';
    private $rootPackage = 'com.cctutor.app';

    public function ast(array $data, $user)
    {
        $username  = normalizeName($user->name);
        $directory = joinPaths($this->filesPath, $username, $this->astPath);
        $package   = joinPackage($this->rootPackage, $username, $this->astPath);
        $parseTree = null;

        if ($data['input_type'] === 'parsing') {
            $lexer = new Lexer($data['content'], $data['token_types']);
            $parser = new LL1($lexer, $data['grammar']);

            $parser->parse();

            $parseTree = $parser->getParseTree('root');
        } else {
            $parseTree = DeterministicParser::parseTreeFromJson($data['parse_tree']);
        }

        if (Storage::exists($directory)) {
            Storage::deleteDirectory($directory);
        }

        Storage::makeDirectory($directory);

        foreach ($data['files'] as $fileData) {
            $content = addPackage($fileData['content'], $package);
            $filePath = joinPaths($directory, $fileData['name']);

            Storage::put($filePath, $content);
        }

        $parseTreeStr = json_encode(DeterministicParser::parseTreeToJson($parseTree));
        Storage::put(joinPaths($directory, 'parseTree.json'), $parseTreeStr);

        $currentDir = getcwd();
        $parseTreeFilePath = storage_path(joinPaths('app', $this->filesPath, "$username/ast/parseTree.json"));
        $nodeClass = joinPackage($package, 'Node');
        chdir(storage_path('app/cctutor'));
        [$output, $exitCode] = mvnCompile();
        [$output, $exitCode] = mvnExecJava('com.cctutor.app.ast.AstProgram', [$parseTreeFilePath, $nodeClass]);
        chdir($currentDir);

        return [
            'output'    => $output,
            'exit_code' => $exitCode
        ];
    }
}
