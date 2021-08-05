<?php

namespace Bredala\Cache\Drivers;

/**
 * DriverInterface
 */
interface DriverInterface
{

    /**
     * @return bool
     */
    public function isConnected(): bool;

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key);

    /**
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     * @return $this
     */
    public function set(string $key, $value, int $expiration = 0): DriverInterface;

    /**
     * @param string $key
     * @return DriverInterface
     */
    public function delete(string $key): DriverInterface;

    /**
     * @return DriverInterface
     */
    public function clean(): DriverInterface;

    /**
     * @param string $key
     * @param int $expiration
     * @param int $offset
     * @return DriverInterface
     */
    public function increment(string $key, int $offset = 1): DriverInterface;

    /**
     * @param string $key
     * @param int $expiration
     * @param int $offset
     * @return DriverInterface
     */
    public function decrement(string $key, int $offset = 1): DriverInterface;
}
