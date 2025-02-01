<?php

namespace TenWebIO;

class Backup
{
    public $upload_dir;
    public $backup_dir;

    /**
     * @param $type
     */
    public function __construct($type = 'bulk')
    {
        $upload_dir = wp_get_upload_dir();
        $this->upload_dir = $upload_dir['basedir'];
        $this->backup_dir = $this->upload_dir . "/tenweb_io_backup/" . $type;
    }

    /**
     * @param $original_img
     * @param $wp_folder
     *
     * @return void
     */
    public function backupBeforeReplace($original_img, $wp_folder)
    {
        if (!is_dir($this->backup_dir . $wp_folder) &&
            !mkdir($this->backup_dir . $wp_folder, 0777, true)) {
            Logs::setLog("backup:error", 'Error creating folder. Backup dir: ' . $this->backup_dir, 'error');
        }

        if (file_exists($original_img)) {
            rename($original_img, $this->backup_dir . "/" . $original_img);
            Logs::setLog("backup:log", 'Keep original file. Destination: ' . $this->backup_dir . "/" . $original_img);
        }
    }

    /**
     * @param $source
     * @param $destination
     * @param $subDir
     *
     * @return void
     */
    public function restoreOriginalImages($source = '', $destination = '', $subDir = '')
    {
        if (empty($source)) {
            $source = $this->backup_dir;
        }
        if (empty($destination)) {
            $destination = $this->upload_dir;
        }
        $directory = opendir($source);

        if (is_dir($destination) === false) {
            mkdir($destination);
        }

        if ($subDir !== '') {
            if (is_dir("$destination/$subDir") === false) {
                mkdir("$destination/$subDir");
            }

            while (($file = readdir($directory)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                if (is_dir("$source/$file") === true) {
                    $this->restoreOriginalImages("$source/$file", "$destination/$subDir/$file");
                } else {
                    rename("$source/$file", "$destination/$subDir/$file");
                }
            }

            closedir($directory);

            return;
        }

        while (($file = readdir($directory)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (is_dir("$source/$file") === true) {
                $this->restoreOriginalImages("$source/$file", "$destination/$file");
            } else {
                rename("$source/$file", "$destination/$file");
            }
        }

        closedir($directory);
    }

    public function deleteBackup()
    {
        foreach (glob("{$this->backup_dir}/*") as $file) {
            if (is_dir($file)) {
                $this->deleteBackup($file);
            } else {
                unlink($file);
            }
        }
        rmdir($this->backup_dir);
    }

}
