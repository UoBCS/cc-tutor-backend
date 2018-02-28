<?php

namespace App\Api\Assignments\Repositories;

use App\Api\Assignments\Models\Assignment;
use App\Api\Users\Models\User;
use App\Infrastructure\Http\Crud\Repository;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class AssignmentRepository extends Repository
{
    private $mainFilesPath   = 'cctutor/src/main/java/com/cctutor/app';
    private $testFilesPath   = 'cctutor/src/test/java/com/cctutor/app';
    private $rootPackage     = 'com.cctutor.app';
    private $assignmentsPath = 'assignments';

    public function getModel()
    {
        return new Assignment();
    }

    public function getFullPath(string $username, string $title)
    {
        $path = joinPaths($this->mainFilesPath, $username, $this->assignmentsPath, $title);
        return storage_path("app/$path");
    }

    public function getAssignmentContents(User $user, Assignment $assignment)
    {
        $title     = normalizeName($assignment->title);
        $username  = normalizeName($user->name);
        $type      = $assignment->type;

        if ($type === 'impl_general') {
            // If teacher return test directory
            $directory = joinPaths($user->teacher ? $this->testFilesPath : $this->mainFilesPath, $username, $this->assignmentsPath, $title);

            $files = Storage::allFiles($directory);
            $contents = [];

            foreach ($files as $file) {
                $contents[] = [
                    'name'    => basename($file),
                    'content' => Storage::get($file)
                ];
            }

            return $contents;
        } else {
            $filePath = joinPaths($this->mainFilesPath, $username, $this->assignmentsPath, $title, "$type.json");
            return Storage::get($filePath);
        }
    }

    public function createTeacherTestDirectory(array $data, User $user)
    {
        $type = $data['type'];
        $title = normalizeName($data['title']);
        $extra = $data['extra'];

        $username  = normalizeName($user->name);
        $directory = joinPaths(
            $type === 'impl_general' ? $this->testFilesPath : $this->mainFilesPath,
            $username,
            $this->assignmentsPath,
            $title
        );

        if (Storage::exists($directory)) {
            Storage::deleteDirectory($directory);
        }

        Storage::makeDirectory($directory);

        if ($type === 'impl_general') {
            $package = joinPackage($this->rootPackage, $username, $this->assignmentsPath, $title);

            foreach ($extra['files'] as $fileData) {
                $content = addPackage($fileData['content'], $package);
                $filePath = joinPaths($directory, $fileData['name']);

                Storage::put($filePath, $content);
            }
        } else {
            $filePath = joinPaths($directory, "$type.json");
            Storage::put($filePath, $extra['solution']);
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

            if (Storage::exists($directory)) {
                Storage::deleteDirectory($directory);
            }

            Storage::makeDirectory($directory);

            if ($type === 'impl_general') {
                foreach ($extra['files'] as $fileData) {
                    [$filename, $content] = $this->getImplementationFromTest($fileData['name'], $username, $title);

                    $filePath = joinPaths($directory, $filename);

                    Storage::put($filePath, $content);
                }
            } else {
                $filePath = joinPaths($directory, "$type.json");
                Storage::put($filePath, '{"breakpoints":[]}');
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

        foreach ($assignment->students as $student) {
            $username = normalizeName($student->name);
            $mainDirectory = joinPaths($this->mainFilesPath, $username, $this->assignmentsPath, $title);

            if (Storage::exists($mainDirectory)) {
                Storage::deleteDirectory($mainDirectory);
            }
        }
    }

    public function updateContents(User $user, Assignment $assignment, array $content)
    {
        $title = normalizeName($assignment->title);
        $username  = normalizeName($user->name);
        $type      = $assignment->type;
        $directory = joinPaths(
            $user->teacher && $assignment->type === 'impl_general'
                ? $this->testFilesPath
                : $this->mainFilesPath
            , $username
            , $this->assignmentsPath
            , $title
        );

        if ($type === 'impl_general') {
            $package  = joinPackage($this->rootPackage, $username, $this->assignmentsPath, $title);

            foreach ($content as $file) {
                $filePath = joinPaths($directory, $file['name']);

                Storage::put($filePath, addPackage($file['content'], $package));

                // Update students directories
                if ($user->teacher) {
                    foreach ($assignment->students as $student) {
                        $username = normalizeName($student->name);

                        [$filename, $fileContent] = $this->getImplementationFromTest($file['name'], $username, $title);

                        $filePath = joinPaths($this->mainFilesPath, $username, $this->assignmentsPath, $title, $filename);

                        if (!Storage::exists($filePath)) {
                            Storage::put($filePath, $fileContent);
                        }
                    }
                }
            }
        } else {
            $filePath = joinPaths($directory, "$type.json");
            Storage::put($filePath, json_encode($content));
        }
    }

    public function copySolutionToTeacher(Assignment $assignment, User $student, User $teacher)
    {
        $studentUsername = normalizeName($student->name);
        $teacherUsername = normalizeName($teacher->name);
        $title           = normalizeName($assignment->title);
        $teacherPackage  = joinPackage($this->rootPackage, $teacherUsername, $this->assignmentsPath, $title);

        $srcPath = joinPaths($this->mainFilesPath, $studentUsername, $this->assignmentsPath, $title);
        $destPath = joinPaths($this->mainFilesPath, $teacherUsername, $this->assignmentsPath, $title);

        File::copyDirectory(storage_path("app/$srcPath"), storage_path("app/$destPath"));

        $files = Storage::allFiles($destPath);

        foreach ($files as $file) {
            Storage::put($file, addPackage(Storage::get($file), $teacherPackage, true));
        }
    }

    private function getImplementationFromTest($testFilename, $username, $title)
    {
        $package  = joinPackage($this->rootPackage, $username, $this->assignmentsPath, $title);
        $filename = preg_replace("/Test.java$/", '.java', $testFilename);
        $class    = getClass($filename);
        $content  = "public class $class {\n\n}";
        $content  = addPackage($content, $package);

        return [$filename, $content];
    }
}
