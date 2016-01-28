<?php

defined('FUN_CACHE') and define('CACHE_PATH',FUN_CACHE);
defined('CACHE_PATH') or (is_writable('/dev/shm') && define('CACHE_PATH','/dev/shm/cached/'));
defined('CACHE_PATH') or define('CACHE_PATH',sys_get_temp_dir() . '/cached/');

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of cache
 *
 * @author 13011908
 */
class Cached {

    const DEFAULT_EXPIRE = 3600;

    protected static $default = 'file';
    protected static $instances = array();
    protected $_cache_dir;

    public static function instance($group = NULL) {
        // If there is no group supplied
        if ($group === NULL) {
            // Use the default setting
            $group = self::$default;
        }

        if (isset(self::$instances[$group])) {
            // Return the current group if initiated already
            return self::$instances[$group];
        }
        // Create a new cache type instance
        self::$instances[$group] = new self();
        // Return the instance
        return self::$instances[$group];
    }

    public function __construct() {
        try {
            $directory = CACHE_PATH;
            $this->_cache_dir = new SplFileInfo($directory);
        }
        // PHP < 5.3 exception handle
        catch (ErrorException $e) {
            $this->_cache_dir = $this->_make_directory($directory, 0777, TRUE);
        }
        // PHP >= 5.3 exception handle
        catch (UnexpectedValueException $e) {
            $this->_cache_dir = $this->_make_directory($directory, 0777, TRUE);
        }

        // If the defined directory is a file, get outta here
        if ($this->_cache_dir->isFile()) {
            throw new Exception('Unable to create cache directory as a file already exists : ' . $this->_cache_dir->getRealPath());
        }

        if (!$this->_cache_dir->isDir()) {
            $this->_cache_dir = $this->_make_directory($directory, 0777, TRUE);
        }

        // Check the read status of the directory
        if (!$this->_cache_dir->isReadable()) {
            throw new Exception('Unable to read from the cache directory ' . $this->_cache_dir->getRealPath());
        }

        // Check the write status of the directory
        if (!$this->_cache_dir->isWritable()) {
            throw new Exception('Unable to write to the cache directory ' . $this->_cache_dir->getRealPath());
        }
    }

    /**
     * Retrieve a cached value entry by id.
     * 
     *     // Retrieve cache entry from file group
     *     $data = self::instance('file')->get('foo');
     * 
     *     // Retrieve cache entry from file group and return 'bar' if miss
     *     $data = self::instance('file')->get('foo', 'bar');
     *
     * @param   string   id of cache to entry
     * @param   string   default value to return if cache miss
     * @return  mixed
     * @throws  Cache_Exception
     */
    public function get($id, $default = NULL) {
        $filename = self::filename($this->_sanitize_id($id));
        $directory = $this->_resolve_directory($filename);

        // Wrap operations in try/catch to handle notices
        try {
            // Open file
            $file = new SplFileInfo($directory . $filename);

            // If file does not exist
            if (!$file->isFile()) {
                // Return default value
                return $default;
            } else {
                // Open the file and parse data
                $created = $file->getMTime();
                $data = $file->openFile();
                $lifetime = $data->fgets();

                // If we're at the EOF at this point, corrupted!
                if ($data->eof()) {
                    return false;
                    throw new Exception(__METHOD__ . ' corrupted cache file!');
                }

                $cache = '';

                while ($data->eof() === FALSE) {
                    $cache .= $data->fgets();
                }

                // Test the expiry
                if (($created + (int) $lifetime) < time()) {
                    // Delete the file
                    $this->_delete_file($file, NULL, TRUE);
                    return $default;
                } else {
                    return unserialize($cache);
                }
            }
        } catch (ErrorException $e) {
            // Handle ErrorException caused by failed unserialization
            if ($e->getCode() === E_NOTICE) {
                throw new Exception(__METHOD__ . ' failed to unserialize cached object with message : ' . $e->getMessage());
            }

            // Otherwise throw the exception
            throw $e;
        }
    }

    /**
     * Set a value to cache with id and lifetime
     * 
     *     $data = 'bar';
     * 
     *     // Set 'bar' to 'foo' in file group, using default expiry
     *     self::instance('file')->set('foo', $data);
     * 
     *     // Set 'bar' to 'foo' in file group for 30 seconds
     *     self::instance('file')->set('foo', $data, 30);
     *
     * @param   string   id of cache entry
     * @param   string   data to set to cache
     * @param   integer  lifetime in seconds
     * @return  boolean
     */
    public function set($id, $data, $lifetime = NULL) {
        $filename = self::filename($this->_sanitize_id($id));
        $directory = $this->_resolve_directory($filename);

        // If lifetime is NULL
        if ($lifetime === NULL) {
            // Set to the default expiry
            $lifetime = self::DEFAULT_EXPIRE;
        }

        // Open directory
        $dir = new SplFileInfo($directory);

        // If the directory path is not a directory
        if (!$dir->isDir()) {
            // Create the directory 
            if (!mkdir($directory, 0777, TRUE)) {
                throw new Exception(__METHOD__ . ' unable to create directory : ' . $directory);
            }

            // chmod to solve potential umask issues
            chmod($directory, 0777);
        }

        // Open file to inspect
        $resouce = new SplFileInfo($directory . $filename);
        $file = $resouce->openFile('w');

        try {
            $data = $lifetime . "\n" . serialize($data);
            $file->fwrite($data, strlen($data));
            return (bool) $file->fflush();
        } catch (ErrorException $e) {
            // If serialize through an error exception
            if ($e->getCode() === E_NOTICE) {
                // Throw a caching error
                throw new Exception(__METHOD__ . ' failed to serialize data for caching with message : ' . $e->getMessage());
            }

            // Else rethrow the error exception
            throw $e;
        }
    }

    protected static function filename($string) {
        return sha1($string) . '.cache';
    }

    /**
     * Delete a cache entry based on id
     * 
     *     // Delete 'foo' entry from the file group
     *     self::instance('file')->delete('foo');
     *
     * @param   string   id to remove from cache
     * @return  boolean
     */
    public function delete($id) {
        $filename = self::filename($this->_sanitize_id($id));
        $directory = $this->_resolve_directory($filename);

        return $this->_delete_file(new SplFileInfo($directory . $filename), NULL, TRUE);
    }

    /**
     * Delete all cache entries.
     * 
     * Beware of using this method when
     * using shared memory cache systems, as it will wipe every
     * entry within the system for all clients.
     * 
     *     // Delete all cache entries in the file group
     *     self::instance('file')->delete_all();
     *
     * @return  boolean
     */
    public function delete_all() {
        return $this->_delete_file($this->_cache_dir, TRUE);
    }

    protected function _delete_file(SplFileInfo $file, $retain_parent_directory = FALSE, $ignore_errors = FALSE, $only_expired = FALSE) {
        // Allow graceful error handling
        try {
            // If is file
            if ($file->isFile()) {
                try {
                    if ($only_expired === FALSE) {
                        // We want to delete the file
                        $delete = TRUE;
                    }
                    // Otherwise...
                    else {
                        // Assess the file expiry to flag it for deletion
                        $json = $file->openFile('r')->current();
                        $data = json_decode($json);
                        $delete = $data->expiry < time();
                    }

                    // If the delete flag is set delete file
                    if ($delete === TRUE)
                        return @unlink($file->getRealPath());
                    else
                        return FALSE;
                } catch (ErrorException $e) {
                    // Catch any delete file warnings
                    if ($e->getCode() === E_WARNING) {
                        throw new Exception(__METHOD__ . ' failed to delete file : ' . $file->getRealPath());
                    }
                }
            }
            // Else, is directory
            elseif ($file->isDir()) {
                // Create new DirectoryIterator
                $files = new DirectoryIterator($file->getPathname());

                // Iterate over each entry
                while ($files->valid()) {
                    // Extract the entry name
                    $name = $files->getFilename();

                    // If the name is not a dot
                    if ($name != '.' AND $name != '..') {
                        // Create new file resource
                        $fp = new SplFileInfo($files->getRealPath());
                        // Delete the file
                        $this->_delete_file($fp);
                    }

                    // Move the file pointer on
                    $files->next();
                }

                // If set to retain parent directory, return now
                if ($retain_parent_directory) {
                    return TRUE;
                }

                try {
                    // Remove the files iterator
                    // (fixes Windows PHP which has permission issues with open iterators)
                    unset($files);

                    // Try to remove the parent directory
                    return rmdir($file->getRealPath());
                } catch (ErrorException $e) {
                    // Catch any delete directory warnings
                    if ($e->getCode() === E_WARNING) {
                        throw new Exception(__METHOD__ . ' failed to delete directory : ' . $file->getRealPath());
                    }
                    throw $e;
                }
            } else {
                // We get here if a file has already been deleted
                return FALSE;
            }
        }
        // Catch all exceptions
        catch (Exception $e) {
            // If ignore_errors is on
            if ($ignore_errors === TRUE) {
                // Return
                return FALSE;
            }
            // Throw exception
            throw $e;
        }
    }

    protected function _resolve_directory($filename) {
        return $this->_cache_dir->getRealPath() . DIRECTORY_SEPARATOR . $filename[0] . $filename[1] . DIRECTORY_SEPARATOR;
    }

    protected function _sanitize_id($id) {
        // Change slashes and spaces to underscores
        return str_replace(array('/', '\\', ' '), '_', $id);
    }

    /**
     * Makes the cache directory if it doesn't exist. Simply a wrapper for
     * `mkdir` to ensure DRY principles
     *
     * @see     http://php.net/manual/en/function.mkdir.php
     * @param   string   directory 
     * @param   string   mode 
     * @param   string   recursive 
     * @param   string   context 
     * @return  SplFileInfo
     * @throws  Cache_Exception
     */
    protected function _make_directory($directory, $mode = 0777, $recursive = FALSE, $context = NULL) {
        if (!mkdir($directory, $mode, $recursive)) {
            throw new Exception('Failed to create the defined cache directory : ' . $directory);
        }
        chmod($directory, $mode);

        return new SplFileInfo($directory);
        ;
    }

    /**
     * Garbage collection method that cleans any expired
     * cache entries from the cache.
     *
     * @return  void
     */
    public function garbage_collect() {
        $this->_delete_file($this->_cache_dir, TRUE, FALSE, TRUE);
        return;
    }

}

?>