<?php
namespace helper;
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

class FileHelper {

    /**
     * Put content into file, create a new file if it not exists
     * @param $fileName Name of the file
     * @param $path Path to the file
     * @param $content Content of the file
     * @throws BusinessException
     */
    public static function putContent($fileName, $path, $content) {
        try {
            // Check if path has trailling dashes
            if (!StringHelper::EndsWith($path, "/")) {
                $path .= "/";
            }

            // Create file
            $completePath = $path.$fileName;
            $fd = fopen($completePath, "a");
            fwrite($fd, $content."\n");
            fclose($fd);

            // Check if file was created
            if (!file_exists($completePath)) {
                throw new \Exception("Arquivo não foi criado corretamente");
            }

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get content from a file in form of string
     * @param $fileName
     * @param $path
     * @throws Exception
     */
    public static function getContent($fileName, $path) {
        try {
            // Check if path has trailling dashes
            if (!StringHelper::EndsWith($path, "/")) {
                $path .= "/";
            }

            // Get content
            $completePath = $path.$fileName;
            $content = file_get_contents($completePath);

            // Check if file was created
            if (!$content) {
                throw new \Exception("Não foi possível retornar os dados do arquivo");
            }

            return $content;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete file
     * @param $fileName
     * @param $path
     * @throws Exception
     */
    public static function deleteFile($fileName, $path) {
        try {
            // Check if path has trailling dashes
            if (!StringHelper::EndsWith($path, "/")) {
                $path .= "/";
            }
            $completePath = $path.$fileName;

            if (file_exists($completePath)) {
                unlink($completePath);
            }

        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function createFolder($folderName, $path) {
        try {
            // Check if path has trailling dashes
            if (!StringHelper::EndsWith($path, "/")) {
                $path .= "/";
            }
            $completePath = $path.$folderName;

            if (!file_exists($completePath) || !is_dir($completePath)) {
                mkdir($completePath, 0705);
                chmod($completePath, 0705);
            }

            // Check if folder was created
            if (!file_exists($completePath)) {
                throw new \Exception("Pasta não foi criada corretamente");
            }

            return $completePath;

        } catch (\Exception $e) {
            throw $e;
        }
    }
}
?>