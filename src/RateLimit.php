<?php

namespace Bredala\Cache;

class RateLimit
{
    private string $name;
    private int $max;
    private int $period;
    private CacheManager $cache;

    /**
     * @param string $name
     * @param integer $max
     * @param integer $period seconds
     * @param CacheManager $cache
     */
    public function __construct(string $name, int $max, int $period, CacheManager $cache)
    {
        $this->name = $name;
        $this->max = $max;
        $this->period = $period;
        $this->cache = $cache;
    }

    /**
     * Rate limit
     * https://en.wikipedia.org/wiki/Token_bucket
     *
     * @param string $id
     * @param integer $use
     * @return boolean
     */
    public function check(string $id, int $use = 1): bool
    {
        $time = time();

        $prev = $this->get($id);

        // First hit
        if (!$prev) {
            $this->set($id, $time, ($this->max - $use));
            return true;
        }

        $prev_time = $prev[0];
        $prev_stock = (float) $prev[1];

        $rate = $this->max / $this->period;
        $elapsed_time = $time  - $prev_time;

        // Get remaining stock from the previous hit
        $stock = $prev_stock + $elapsed_time * $rate;
        if ($stock > $this->max) $stock = $this->max;

        // The rate limit is reached
        if ($stock < $use) {
            $this->set($id, $time, $stock);
            return false;
        }

        // stock decrease
        $this->set($id, $time, ($stock - $use));
        return true;
    }

    /**
     * Get remnant hits
     *
     * @param string $id
     * @return integer
     */
    public function remnant(string $id): int
    {
        $this->check($id, 0);
        $prev = $this->get($id);
        return !$prev ? $this->max : max(0, (int) $prev[1]);
    }

    /**
     * Purge all
     *
     * @param string $id
     * @return void
     */
    public function purge(string $id)
    {
        $this->cache->delete($this->key($id));
    }

    /**
     * @param string $id
     * @return string
     */
    private function key(string $id): string
    {
        return $this->name . ":" . $id;
    }

    /**
     * @param string $id
     * @param integer $time
     * @param float $stock
     */
    private function set(string $id, int $time, float $stock)
    {
        $key = $this->key($id);
        $value = $time . '|' . $stock;
        $this->cache->set($key, $value, $this->period);
    }

    /**
     * @param string $id
     */
    private function get(string $id): array
    {
        $key = $this->key($id);
        $value = $this->cache->get($key);
        return $value ? explode('|', $value) : [];
    }
}
