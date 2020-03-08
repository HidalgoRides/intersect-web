<?php

namespace Intersect\Services;

use Intersect\Services\AbstractService;
use Predis\Client;

/**
 * Prerequisites:
 * - Predis Library (tested up to v1.1.1)
 *   - https://github.com/nrk/predis
 *   - composer require predis/predis:1.1
 * 
 * - Required Configuration
 *   - Place the following configuration structure at the root level of your config.php file (replace values where needed)
 *      'redis' => [
 *          'scheme' => 'tcp',
 *          'host' => '127.0.0.1',
 *          'port' => 6379
 *      ]
 */
class RedisService extends AbstractService {

    /** @var Client */
    private $client;

    /**
     * @param array|string $keys
     * @return int
     */
    public function delete($keys)
    {
        if (!is_array($keys))
        {
            $keys = [$keys];
        }

        return $this->getClient()->del($keys);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key) 
    {
        $value = $this->getClient()->get($key);

        if (!is_null($value)) {
            $value = $this->deserialize($value);
        }

        return $value;
    }

    /**
     * @param string $key
     * @param callable $callback
     * @param int|null $ttl
     * @return mixed
     */
    public function getOrSet($key, callable $callback, $ttl = null)
    {
        $value = $this->get($key);

        if (is_null($value)) {
            $value = $callback();
            $this->set($key, $value, $ttl);
        }

        return $value;
    }

    /**
     * @param string $channel
     * @param string $message
     * @return int
     */
    public function publish($channel, $message)
    {
        return $this->getClient()->publish($channel, $message);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl
     * @return mixed
     */
    public function set($key, $value, $ttl = null)
    {
        $value = $this->serialize($value);

        if (!is_null($ttl)) {
            return $this->getClient()->setex($key, $ttl, $value);
        }

        return $this->getClient()->set($key, $value);
    }

    /**
     * @param mixed
     * @return string
     */
    protected function serialize($object)
    {
        return serialize($object);
    }

    /**
     * @param string
     * @return mixed
     */
    protected function deserialize($object)
    {
        return unserialize($object);
    }

    /**
     * @return Client
     */
    private function getClient()
    {
        if (is_null($this->client)) {
            $redisConfigs = $this->getApplication()->getRegisteredConfigs('redis');

            $this->client = new Client([
                'scheme' => $redisConfigs['scheme'],
                'host'   => $redisConfigs['host'],
                'port'   => $redisConfigs['port'],
            ]);
        }

        return $this->client;
    }

}