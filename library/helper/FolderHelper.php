<?php
namespace helper;
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

class FolderHelper {

    /**
     * Return files in a folder by type
     * @param $folderPath
     * @param $type
     * @param $removeExtension
     */
    public static function listFilesByType($folderPath, $type, $removeExtension = false) {
        $results = null;
        if (is_dir($folderPath)) {
            $results = array();
            if ($handle = opendir($folderPath)) {
                while (($file = readdir($handle)) !== false) {
                    if (StringHelper::endsWith($file, $type)) {
                        if ($removeExtension) {
                            $results[] = substr($file, 0, strlen($file) - 4);
                        } else {
                            $results[] = $file;
                        }
                    }
                }
                closedir($handle);
            }
        }
        return $results;
    }

}
?>