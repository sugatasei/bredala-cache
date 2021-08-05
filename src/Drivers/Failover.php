<?php

namespace Bredala\Cache\Drivers;

/**
 * Failover
 */
class Failover implements DriverInterface
{

    protected $data = [];

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return true;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->data[$key] ?? false;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     * @return $this
     */
    public function set(string $key, $value, int $expiration = 0): DriverInterface
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function delete(string $key): DriverInterface
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function clean(): DriverInterface
    {
        $this->data = [];

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
        if (!isset($this->data[$key])) {
            $this->data[$key] = 0;
        } else {
            $this->data[$key] += $offset;
        }

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
        if (!isset($this->data[$key])) {
            $this->data[$key] = 0;
        } else {
            $this->data[$key] -= $offset;
        }

        return $this;
    }
}
