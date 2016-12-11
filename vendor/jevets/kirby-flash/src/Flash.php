<?php 

namespace Jevets\Kirby;

use \s as Session;

class Flash
{
    /**
     * A Session key identifier
     *
     * @var constant
     */
    private static $session_key;

    /**
     * The singleton instance
     *
     * @var Jevets\Kirby\Flash
     */
    private static $instance;

    /**
     * Container for the flashed data
     *
     * @var array
     */
    private static $data;

    /**
     * Get the singleton instance
     *
     * @return Jevets\Kirby\Flash
     */
    public static function getInstance()
    {
        if (null === static::$instance)
        {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Instantiate the singleton
     *
     * @return void
     */
    private function __construct($session_key = '_flash')
    {
        self::setSessionKey($session_key);

        static::$data = Session::get(self::sessionKey(), []);

        Session::remove(self::sessionKey());
    }

    /**
     * Get the session key
     *
     * @return string
     */
    public static function sessionKey()
    {
        return static::$session_key;
    }

    /**
     * Set the session key
     *
     * @param string
     * @return void
     */
    public static function setSessionKey($session_key)
    {
        static::$session_key = $session_key;
    }

    /**
     * Set flash data
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public static function set($key, $value)
    {
        if (!isset($data[$key]))
            static::$data[$key] = $value;

        Session::set(self::sessionKey(), static::$data);
    }

    /**
     * Get an item from the flash data by key
     *
     * @param  string  $key
     * @param  mixed  $default value to return if flash key doesn't exist
     * @return  mixed  $value or null
     */
    public static function get($key, $default = '')
    {
        return isset(self::$data[$key]) ? self::$data[$key] : null;
    }

    /**
     * Get all data from the flash
     *
     * @return  array
     */
    public static function all()
    {
        return self::$data;
    }
}