<?php
/**
 * InnoCraft Ltd - We are the makers of Piwik Analytics, the leading open source analytics platform.
 *
 * @link https://www.innocraft.com
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html LGPL v3.0
 */
namespace InnoCraft\Experiments\Storage;

/**
 * Stores the values in a cookie to make sure on subsequent requests the same variation will be activated.
 */
class Cookie implements StorageInterface {

    /**
     * This is static in case the same experiment is created several times during one http request
     * to make sure we always activate the same variation even within one request
     * @var array
     */
    private static $data = [];

    /**
     * When the cookie should expire (in seconds), defaults to 2 years
     * @var integer
     */
    private $cookieExpiry = 63072000;

    /**
     * Constructor
     * @param integer $expiry When the cookie should expire (in seconds), defaults to 2 years
     */
    public function __construct($cookieExpiry = 63072000)
    {
        $this->cookieExpiry = $cookieExpiry;
    }

    public function get($namespace, $key)
    {
        $name = $this->toName($namespace, $key);

        if (isset(self::$data[$name])) {
            return self::$data[$name];
        }

        if (isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
        }
    }

    public function set($namespace, $key, $value)
    {
        $name = $this->toName($namespace, $key);
        self::$data[$name] = $value;

        if (!headers_sent()) {
            // we do not throw an exception for now when headers already sent to not break the application
            // but could do later to make users aware there is an error
            // 0 = expire after session
            // negative value = force expiration
            $expireTime = $this->cookieExpiry == 0 ? 0 : time() + $this->cookieExpiry;
            setcookie($name, $value, $expireTime, $path = '/', "", false, $httpOnly = true);
        }
    }

    private function toName($namespace, $key)
    {
        return $namespace . '_' . $key;
    }

}