<?php

namespace RoyalSortKv\SmartyFacesCore\composer;

use Composer\Script\Event;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ScriptHandler
{
    public static function buildAssets(Event $event)
    {
        $io = $event->getIO();

        // Run npm install or yarn install
        $io->write("Installing Node.js dependencies...");
        self::runCommand('npm install', __DIR__ . '/../..'); // Change path if needed

        // Run gulp to build and copy assets
        $io->write("Running Gulp script...");
        self::runCommand('./gulp', __DIR__ . '/../../node_modules/.bin/'); // Adjust this path to where gulpfile.js is located

        $io->write("Assets built successfully.");

        self::copyFiles($event);

        self::delete_directory(__DIR__ . '/../../public');
        self::delete_directory(__DIR__ . '/../../node_modules');
    }

    private static function runCommand($command, $workingDir)
    {
        $process = new Process(explode(' ', $command), $workingDir);
        $process->setTimeout(3600); // You can adjust the timeout as necessary
        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            throw new \RuntimeException('Command failed: ' . $command . '. ' . $exception->getMessage());
        }
    }

    public static function copyFiles(Event $event)
    {
        $io = $event->getIO();

        // Run your build logic here (e.g., compile or generate files)
        $io->write("Copying files...");

        // Example: Copy a folder from the library to the parent project
        $sourceDir = __DIR__ . '/../../public/lib'; // Adjust this path
        $targetDir = getcwd() . '/public/lib'; // Path in parent project

        $io->write("Files have been copied to $targetDir.");

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        self::recursiveCopy($sourceDir, $targetDir);

        $io->write("Files have been copied.");
    }

    private static function recursiveCopy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    self::recursiveCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    private static function delete_directory($dirname) {
        $dir_handle=false;
        if (is_dir($dirname)) {
            $dir_handle = opendir($dirname);
        }
        if (!$dir_handle) {
            return false;
        }
        while($file = readdir($dir_handle)) {
            if ($file != "." && $file != "..") {
                if (!is_dir($dirname."/".$file))
                    unlink($dirname."/".$file);
                else
                    self::delete_directory($dirname.'/'.$file);
            }
        }
        closedir($dir_handle);
        rmdir($dirname);
        return true;
    }
}