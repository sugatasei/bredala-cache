<?php

namespace Bredala\Cache;

/**
 * SessionHandler
 *
 * A Session handler using Bredala\Cache
 */
class SessionHandler implements \SessionHandlerInterface
{

    /**
     * @var CacheManager
     */
    private $cache;

    /**
     * @var array
     */
    private $config = [
        'expiration' => 3600,
        'prefix'     => 'sess_'
    ];

    // -------------------------------------------------------------------------

    /**
     * @param CacheManager $cache
     * @param array $options
     */
    public function __construct(CacheManager $cache, array $options = [])
    {
        $this->cache = $cache;

        if ($options) {
            $this->config = array_merge($this->config, $options);
        }
    }

    // -------------------------------------------------------------------------

    /**
     * Open data
     *
     * @param string $save_path
     * @param string $name
     * @return bool
     */
    public function open($save_path, $name)
    {
        return true;
    }

    // -------------------------------------------------------------------------

    /**
     * Read data
     *
     * @param string $session_id
     * @return string
     */
    public function read($session_id)
    {
        $data = $this->cache->get($this->config['prefix'] . $session_id);

        return $data ? $data : '';
    }

    // -------------------------------------------------------------------------

    /**
     * Save all
     *
     * @param string $session_id
     * @param string $session_data
     * @return bool
     */
    public function write($session_id, $session_data)
    {
        $this->cache->set($this->config['prefix'] . $session_id, $session_data, $this->config['expiration']);

        return true;
    }

    // -------------------------------------------------------------------------

    /**
     * Destroy current session
     *
     * @param string $session_id
     * @return bool
     */
    public function destroy($session_id)
    {
        $this->cache->delete($this->config['prefix'] . $session_id);

        return true;
    }

    // -------------------------------------------------------------------------

    /**
     * Close
     *
     * @return bool
     */
    public function close()
    {
        return true;
    }

    // -------------------------------------------------------------------------

    /**
     * Garbage collector
     *
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        return true;
    }

    // -------------------------------------------------------------------------
}

/* End of file */
