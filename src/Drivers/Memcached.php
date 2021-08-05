<?php

namespace Bredala\Cache\Drivers;

/**
 * Memcached
 */
class Memcached implements DriverInterface
{

    const ALWAYS = 4102444800;

    /**
     * @var \Memcached
     */
    protected $memcached;

    // -------------------------------------------------------------------------
    // Connection
    // -------------------------------------------------------------------------

    public function __construct(array $config = [])
    {
        $this->memcached = new \Memcached();

        // Set options
        if (isset($config['options'])) {
            foreach ($config['options'] as $k => $v) {
                $this->memcached->setOption($k, $v);
            }
        }

        // Connextion
        if (isset($config['servers'])) {
            $this->memcached->addServers($config['servers']);
        } elseif (isset($config['host'], $config['port'])) {
            $this->memcached->addServer($config['host'], $config['port']);
        } else {
            $this->memcached->addServer('127.0.0.1', 11211);
        }

        // SASL authentication
        if (isset($config['user'], $config['password'])) {
            $this->memcached->setSaslAuthData($config['user'], $config['password']);
        }
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->memcached && $this->memcached->getVersion() !== false;
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
        $value = $this->memcached->get($key);

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
        $this->memcached->set($key, $value, self::exp($expiration));

        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function delete(string $key): DriverInterface
    {
        $this->memcached->delete($key);

        return $this;
    }

    /**
     * @return $this
     */
    public function clean(): DriverInterface
    {
        $this->memcached->flush();

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
        $this->memcached->increment($key, $offset, 0, self::ALWAYS);

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
        $this->memcached->decrement($key, $offset, 0, self::ALWAYS);

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
