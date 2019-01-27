<?php

namespace Intersect\Storage;

class CookieStorage {

    /**
     * @param $key
     * @return null
     */
    public function read($key)
    {
        if (isset($_COOKIE[$key]))
        {
            return $_COOKIE[$key];
        }

        return null;
    }

    /**
     * @param $key
     * @param $value
     * @param int $expires
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     */
    public function write($key, $value, $expires = 0, $path = '', $domain = '', $secure = false, $httpOnly = false)
    {
        setcookie($key, $value, $expires, $path, $domain, $secure, $httpOnly);
    }

    /**
     * @param $key
     */
    public function clear($key)
    {
        if (isset($_COOKIE[$key]))
        {
            $this->write($key, null, time() - 3600, '/');
        }
    }

    /**
     *
     */
    public function clearAll()
    {
        foreach ($_COOKIE as $cookieKey => $cookieValue)
        {
            $this->clear($cookieKey);
        }
    }

}