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
        if(substr($path, strlen($path) - 1, 1) !== '/') 
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
}