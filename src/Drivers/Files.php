<?php

namespace Bredala\Cache\Drivers;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Files
 */
class Files implements DriverInterface
{

    const ALWAYS = 4102444800;

    /**
     * @var string
     */
    protected $path = '';

    /**
     * @var int
     */
    protected $folder_len = 4;

    /**
     * @var bool
     */
    protected $is_connected = false;

    // -------------------------------------------------------------------------
    // Connection
    // -------------------------------------------------------------------------

    public function __construct(string $path = '')
    {

        if ($path && (is_dir($path))) {
            $this->path = $path;
        } elseif (!$this->path) {
            $this->path = sys_get_temp_dir();
        }

        $this->path = rtrim(realpath($this->path), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (is_writable($this->path)) {
            $this->is_connected = true;
        }
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->is_connected;
    }

    // -------------------------------------------------------------------------
    // Data
    // -------------------------------------------------------------------------

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        $file = $this->keyToFile($key);

        if (is_file($file)) {
            $data = unserialize(file_get_contents($file));

            if (isset($data['d'], $data['t'])) {
                if ($data['t'] > time()) {
                    $this->cache[$key] = $data['d'];
                    return $data['d'];
                } else {
                    unlink($file);
                }
            }
        }

        return null;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     * @return $this
     */
    public function set(string $key, $value, int $expiration = 0): DriverInterface
    {
        // Storage
        $file = $this->keyToFile($key, true);
        $data = ['d' => $value, 't' => self::exp($expiration)];
        $dir  = dirname($file);

        // Create dir
        if (!is_dir($dir)) {
            mkdir($dir, 0666, true);
        }

        file_put_contents($file, serialize($data));

        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function delete(string $key): DriverInterface
    {
        // Storage
        $file = $this->keyToFile($key);

        if (is_file($file)) {
            unlink($file);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function clean(): DriverInterface
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            $todo = $file->isDir() ? 'rmdir' : 'unlink';
            $todo($file->getRealPath());
        }

        return $this;
    }

    /**
     * @param string $key
     * @param int $expiration
     * @param int $offset
     * @return $this
     */
    public function increment(string $key, int $offset = 1): DriverInterface
    {
        $val = (int) $this->get($key);
        $this->set($key, $val + $offset, self::ALWAYS);

        return $this;
    }

    /**
     * @param string $key
     * @param int $expiration
     * @param int $offset
     * @return $this
     */
    public function decrement(string $key, int $offset = 1): DriverInterface
    {
        $val = (int) $this->get($key);
        $this->set($key, $val - $offset, self::ALWAYS);

        return $this;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * @param int $exp
     * @return int
     */
    protected static function exp($exp)
    {
        $now = time();

        // A delay is given and not a timestamp
        if ($exp < $now) {
            $exp = $now + $exp;
        }

        return $exp;
    }

    /**
     * @param string $path
     * @param string $key
     * @param bool $mkdir
     * @return type
     */
    protected function keyToFile(string $key)
    {
        return $this->path . join('/', mb_str_split(sha1($key), $this->folder_len));
    }

    // -------------------------------------------------------------------------
}
