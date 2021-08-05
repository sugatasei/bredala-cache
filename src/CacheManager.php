<?php

namespace Bredala\Cache;

use Bredala\Cache\Drivers\DriverInterface;
use Bredala\Cache\Drivers\Failover;

/**
 * CacheManager
 */
final class CacheManager
{
    private ?DriverInterface $instance = null;
    private Failover $failover;
    private bool $is_connected = false;
    private string $prefix = '';

    // -------------------------------------------------------------------------
    // Config
    // -------------------------------------------------------------------------

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->failover = new Failover();
    }

    /**
     * @param DriverInterface $driver
     * @return $this
     */
    public static function factory(DriverInterface $driver): CacheManager
    {
        $instance = new static();
        return $instance->setDriver($driver);
    }

    /**
     * Set the driver
     *
     * @param DriverInterface $driver
     * @return $this
     */
    public function setDriver(DriverInterface $driver): CacheManager
    {
        $this->instance = $driver;

        return $this;
    }

    // -------------------------------------------------------------------------
    // Connexion
    // -------------------------------------------------------------------------

    /**
     * Returns if the connexion is active
     *
     * @param bool $refresh
     * @return bool
     */
    public function isConnected(): bool
    {
        $this->is_connected = $this->instance && $this->instance->isConnected();


        return $this->is_connected;
    }

    // -------------------------------------------------------------------------
    // Data
    // -------------------------------------------------------------------------

    /**
     * Returns a data
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->getConnectedDriver()->get($this->getKey($key));
    }

    /**
     * Set a data
     *
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     * @return $this
     */
    public function set(string $key, $value, int $expiration = 30): CacheManager
    {
        $this->getConnectedDriver()->set($this->getKey($key), $value, $expiration);

        return $this;
    }

    /**
     * Delete a data
     *
     * @param string $key
     * @return $this
     */
    public function delete(string $key): CacheManager
    {
        $this->getConnectedDriver()->delete($this->getKey($key));

        return $this;
    }

    /**
     * Increment a data
     *
     * @param string $key
     * @param int $expiration
     * @param int $offset
     * @return $this
     */
    public function increment(string $key, int $expiration = 0, int $offset = 1): CacheManager
    {
        $this->getConnectedDriver()->increment($this->getKey($key), $expiration, $offset);

        return $this;
    }

    /**
     * Decrement a data
     *
     * @param string $key
     * @param int $expiration
     * @param int $offset
     * @return $this
     */
    public function decrement(string $key, int $expiration = 0, int $offset = 1): CacheManager
    {
        $this->getConnectedDriver()->decrement($this->getKey($key), $expiration, $offset);

        return $this;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * @return DriverInterface
     */
    private function getConnectedDriver(): Drivers\DriverInterface
    {
        return $this->is_connected ? $this->instance : $this->failover;
    }

    /**
     * @param string $key
     * @return string
     */
    private function getKey(string $key): string
    {
        return $this->prefix . $key;
    }

    // -------------------------------------------------------------------------
}
