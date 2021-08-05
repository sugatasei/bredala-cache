<?php

namespace Bredala\Cache\Drivers;

/**
 * Redis
 */
class Redis implements DriverInterface
{

    /**
     * @var \Redis
     */
    protected $redis;

    /**
     * @var bool
     */
    protected $is_connected = false;

    // -------------------------------------------------------------------------
    // Connection
    // -------------------------------------------------------------------------

    public function __construct(array $config = [])
    {
        $this->redis = new \Redis();

        // Connection
        if (isset($config['socket'])) {
            $this->is_connected = $this->redis->connect($config['socket']);
        } else {
            $host    = $config['host'] ?? '127.0.0.1';
            $port    = $config['port'] ?? 6379;
            $timeout = $config['timeout'] ?? 0;

            $this->is_connected = $this->redis->connect($host, $port, $timeout);
        }

        // Password
        if ($this->is_connected && isset($config['password'])) {
            $this->is_connected = $this->redis->auth($config['password']);
        }

        // Database
        if ($this->is_connected && isset($config['database']) && $config['database'] > 0) {
            $this->is_connected = $this->redis->select($config['database']);
        }

        // Options
        if ($this->is_connected && isset($config['options'])) {
            foreach ($config['options'] as $k => $v) {
                $this->redis->setOption($k, $v);
            }
        }
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->is_connected && $this->redis->ping() === '+PONG';
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
        $value = $this->redis->get($key);

        return $value !== false ? $value : null;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     * @return $this
     */
    public function set(string $key, $value, int $expiration = 0): DriverInterface
    {
        $this->redis->setex($key, self::exp($expiration), $value);

        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function delete(string $key): DriverInterface
    {
        $this->redis->delete($key);

        return $this;
    }

    /**
     * @return $this
     */
    public function clean(): DriverInterface
    {
        $this->redis->flushDB();

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
        if ($offset === 1) {
            $this->redis->incr($key);
        } else {
            $this->redis->incrBy($key, $offset, 0);
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
        $this->redis->decrement($key, $offset, 0, self::exp($expiration));

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

        // Memcached use the UNIX time
        // when the expiration is greater than 30 days
        if ($exp < $now && $exp > 2592000) {
            $exp = $now + $exp;
        }

        return $exp;
    }

    // -------------------------------------------------------------------------
}
