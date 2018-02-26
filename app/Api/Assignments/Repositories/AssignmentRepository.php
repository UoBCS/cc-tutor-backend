<?php

namespace App\Api\Assignments\Repositories;

use App\Api\Assignments\Models\Assignment;
use App\Api\Users\Models\User;
use App\Infrastructure\Http\Crud\Repository;
use Illuminate\Support\Facades\Storage;

class AssignmentRepository extends Repository
{
    private $mainFilesPath = 'cctutor/src/main/java/com/cctutor/app';
    private $testFilesPath = 'cctutor/src/test/java/com/cctutor/app';
    private $rootPackage = 'com.cctutor.app';
    private $assignmentsPath = 'assignments';

    public function getModel()
    {
        return new Assignment();
    }

    public function createTeacherTestDirectory(array $data, User $user)
    {
        $type = $data['type'];
        $title = normalizeName($data['title']);
        $extra = $data['extra'];

        $username  = normalizeName($user->name);
        $directory = joinPaths($this->testFilesPath, $username, $this->assignmentsPath, $title);
        $package   = joinPackage($this->rootPackage, $username, $this->assignmentsPath, $title);

        if (Storage::exists($directory)) {
            Storage::deleteDirectory($directory);
        }

        Storage::makeDirectory($directory);

        if ($type === 'impl_general') {
            foreach ($extra['files'] as $fileData) {
                $content = addPackage($fileData['content'], $package);
                $filePath = joinPaths($directory, $fileData['name']);

                Storage::put($filePath, $content);
            }
        }
    }

    public function createStudentSolutionsDirectories(array $data, $students)
    {
        $type = $data['type'];
        $title = normalizeName($data['title']);
        $extra = $data['extra'];

        foreach ($students as $student) {
            $username  = normalizeName($student->name);
            $directory = joinPaths($this->mainFilesPath, $username, $this->assignmentsPath, $title);
            $package   = joinPackage($this->rootPackage, $username, $this->assignmentsPath, $title);

            if (Storage::exists($directory)) {
                Storage::deleteDirectory($directory);
            }

            Storage::makeDirectory($directory);

            if ($type === 'impl_general') {
                foreach ($extra['files'] as $fileData) {
                    $filename = preg_replace("/Test.java$/", '.java', $fileData['name']);

                    $class = getClass($filename);
                    $content = "public class $class {\n\n}";
                    $content = addPackage($content, $package);
                    $filePath = joinPaths($directory, $filename);

                    Storage::put($filePath, $content);
                }
            }
        }
    }

    public function deleteTeacherDirectories(Assignment $assignment)
    {
        $username  = normalizeName($assignment->teacher->name);
        $title     = normalizeName($assignment->title);

        $testDirectory = joinPaths($this->testFilesPath, $username, $this->assignmentsPath, $title);
        if (Storage::exists($testDirectory)) {
            Storage::deleteDirectory($testDirectory);
        }

        $mainDirectory = joinPaths($this->mainFilesPath, $username, $this->assignmentsPath, $title);
        if (Storage::exists($mainDirectory)) {
            Storage::deleteDirectory($mainDirectory);
        }
    }

    public function deleteStudentsDirectories(Assignment $assignment)
    {
        $title = normalizeName($assignment->title);

        var_dump($assignment->students);
        foreach ($assignment->students as $student) {
            $username = normalizeName($student->name);

            $mainDirectory = joinPaths($this->mainFilesPath, $username, $this->assignmentsPath, $title);
            var_dump($mainDirectory);
            if (Storage::exists($mainDirectory)) {
                var_dump('asdaisod');
                Storage::deleteDirectory($mainDirectory);
            }
        }
    }
}
