<?php

namespace App\Services\Cloud;

class FileManager {
    private const ROOT_PATH = "C:/Users/X181539/Documents";

    /**
     * Generate absolute file path from relative one.
     * 
     * @param path Relative file/directory path
     * @return string Absolute file path in the server. 
     */
    public function file_path($path) {
        return self::ROOT_PATH . (substr($path, -1) == '/'?'':'/') . $path;
    }

    /**
     * Generate relative file path from absolute one.
     * 
     * @param path Absolute file/directory path
     * @return string Relative file path in the server. 
     */
    public function relative_path($path) {
        return str_replace(self::ROOT_PATH, '', $path);
    }

    /**
     * Check if file/directory exists in the server.
     * 
     * @param path Relative file/directory path
     * @return boolean True if file exist, False otherwise
     */
    public function file_exists($path) {
        return file_exists(self::file_path($path));
    }

    /**
     * Check if path is a file.
     * 
     * @param path Relative file/directory path
     * @return boolean True if it is a file, False otherwise
     */
    public function is_file($path) {
        return is_file(self::file_path($path));
    }

    /**
     * Check if path is a directory.
     * 
     * @param path Relative file/directory path
     * @return boolean True if it is a directory, False otherwise
     */
    public function is_dir($path) {
        return is_dir(self::file_path($path));
    }

    /**
     * Create missing directories of a path.
     * 
     * @param path Relative path
     * @return ~ the number of bytes that were written to the file, or FALSE on failure
     */
    private function create_path($path) {
        $parts = explode('/', $path);
        $last = array_pop($parts);
        $dir = self::ROOT_PATH;
        foreach($parts as $part)
            if(!is_dir($dir .= "/$part")) mkdir($dir);
    }

    /**
     * Create new empty file in the server.
     * 
     * @param path Relative file path
     * @return ~ the number of bytes that were written to the file, or FALSE on failure
     */
    public function create_file($path) {
        // Create path to avoid error
        self::create_path($path);
        // Check if destination file already exist to avoid accidental overwrite
        if(self::file_exists($path))
            self::create_file_backup($path);
        // Create file
        return file_put_contents(self::file_path($path), null);
    }

    /**
     * Create new empty dir in the server.
     * 
     * @param path Relative directory path
     * @return boolean True on success, FALSE otherwise
     */
    public function create_dir($path) {
        // Create path to avoid error
        self::create_path($path);
        // Create file
        error_reporting(E_ERROR | E_PARSE);
        $result = mkdir(self::file_path($path));
        error_reporting(E_ALL ^ E_WARNING);
        return $result;
    }

    /**
     * Create backup of a file.
     * 
     * @param path Relative file/directory path
     * @return ~ the number of bytes that were written to the file, or FALSE on failure
     */
    public function create_file_backup($path) {
        if(!self::file_exists($path) || !self::is_file($path)) return false;
        $file_info = pathinfo($path);
        rename(self::file_path($path), self::file_path($file_info['dirname'] . '/' . $file_info['filename'] . '-' . date("YmdHis") . '.' . $file_info['extension']));
    }

    /**
     * Move uploded file in the server.
     * 
     * @param file Uploded file
     * @param path Relative directory path the file will be moved to
     * @return boolean True on success, False otherwise
     */
    public function move_uploded_file($file, $path) {
        // Check if $path end with /
        if(substr($path, - 1) !== '/') 
            $path .= '/';
        // Backup destination file if it already exist to avoid accidental overwrite
        if(self::file_exists($path . basename($file)))
            self::create_file_backup($path . basename($file));
        // Create path to avoid error
        self::create_path($path . basename($file));
        // Move uploaded file
        return move_uploaded_file($file, self::file_path($path) . basename($file));
    }

    /**
     * Rename file/directory in the server.
     * 
     * @param path File relative path
     * @param newpath File new relative path
     * @return boolean True on success, False otherwise
     */
    public function rename($path, $newpath) {
        // Backup destination file if it already exist to avoid accidental overwrite
        if(self::file_exists($newpath))
            self::create_file_backup($newpath);
        // Create path to avoid error
        self::create_path($newpath);
        // Move uploaded file
        return rename(self::file_path($path), self::file_path($newpath));
    }

    /**
     * Update file content in the server.
     * 
     * @param path Relative file path
     * @return ~ the number of bytes that were written to the file, or FALSE on failure
     */
    public function update_file($path, $content) {
        if(!self::file_exists($path) || !self::is_file($path)) return false;
        // Create path to avoid error
        self::create_path($path);
        // Update file content
        return file_put_contents(self::file_path($path), $content);
    }

    /**
     * Delete file in the server.
     * 
     * @param path Relative file path
     * @return boolean True on success, False otherwise
     */
    public function delete_file($path) {
        if(self::file_exists($path) && self::is_file($path))
            return unlink(self::file_path($path));
        return false;
    }

    /**
     * Delete dir in the server.
     * 
     * @param path Relative dir path
     * @return boolean True on success, False otherwise
     */
    public function delete_dir($path) {
        if(self::file_exists($path) && self::is_dir($path)){
            $objects = scandir(self::file_path($path));
            foreach ($objects as $object) {
              if ($object != "." && $object != "..") {
                if (self::is_dir($path."/".$object)) 
                    self::delete_dir($path."/".$object); 
                else 
                    self::delete_file($path."/".$object);
              }
            }
            reset($objects);
            rmdir(self::file_path($path)); 
        }
        return true;
    }

    /**
     * Get files and sub directories of path passed as param
     * 
     * @param path Relative directory path to scan
     * @return array 
     */
    public function scandir($path){
        // Check if path exist and is a dir and not a file
        if(!self::file_exists($path) || !self::is_dir($path))
            return false;

        // Check if $path end with /
        if(substr($path, -1) != "/")
            $path .= '/';
            
        // Get dir content
        $scan_result = scandir(self::file_path($path));
        
        $content  = array();
        
        error_reporting(E_ERROR | E_PARSE);
        foreach($scan_result as $file){
            if($file !== '.' && $file !== '..'){
                $content[] = array(
                    'type' => self::is_dir($path . $file) ? 'dir':'file',
                    'name' => $file,
                    'url' => $path.$file,
                    'size' => filesize(self::file_path($path . $file)),
                    'mtime' => filectime(self::file_path($path . $file)),
                );
            }
        }
        error_reporting(E_ALL ^ E_WARNING);
        
        return $content;
    }
}