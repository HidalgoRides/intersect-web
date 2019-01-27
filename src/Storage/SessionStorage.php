<?php

namespace Intersect\Storage;

class SessionStorage {

    public function __construct() {}

    /**
     * @param array $sessionOptions
     */
    public function start($sessionOptions = array())
    {
        if (session_status() == PHP_SESSION_NONE)
        {
            session_start($sessionOptions);
        }
    }

    public function destroy()
    {
        if (session_status() == PHP_SESSION_ACTIVE)
        {
            session_destroy();
        }
    }

    /**
     * @param $key
     * @param null $defaultValue
     * @return null
     */
    public function read($key, $defaultValue = null)
    {
        if (isset($_SESSION[$key]))
        {
            return $_SESSION[$key];
        }

        return $defaultValue;
    }

    /**
     * @param $key
     * @param null $defaultValue
     * @return null
     */
    public function readAndClear($key, $defaultValue = null)
    {
        $value = $this->read($key, $defaultValue);
        $this->clear($key);

        return $value;
    }

    /**
     * @param $key
     * @param $value
     */
    public function write($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * @param $key
     */
    public function clear($key)
    {
        if (isset($_SESSION[$key]))
        {
            unset($_SESSION[$key]);
        }
    }

}