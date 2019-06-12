<?php
/**
 * Created by PhpStorm.
 * User: Thinkpad
 * Date: 2017/1/10
 * Time: 11:49
 */

namespace Lib;


class Session
{
    /**
     * Session 内部状态
     * 取值范围: inactive | active | expired | destroyed | error
     *
     * @var String
     * @see Session::getState()
    */
    protected $_state = 'inactive';

    /**
     * Session 过期时间，秒
    */
    protected $_expire = 900;

    /**
     * Session 对象
     *
    */
    protected static $instance;

    /**
     * Session 内部存储的数据
     *
     * @var Registry
    */
    protected $data;

    /**
     * 构造函数
     *
     * @param string $store session的存储类型
     * @param array  $options session可选配置
    */
    public function __contruct($store='none', array $options = array()){

        $this->data = new Registry();
        $this->_setOptions($options);

        $this->_state = 'inactive';
    }
    /**
     * 魔术方法，get
    */
    public function __get($name)
    {
        if ($name === 'storeName')
        {
            return $this->$name;
        }

        if ($name === 'state' || $name === 'expire')
        {
            $property = '_' . $name;

            return $this->$property;
        }
    }

    public static function getInstance($store, $options)
    {
        if (!is_object(self::$instance))
        {
            self::$instance = new Session($store, $options);
        }

        return self::$instance;
    }

    public function getState()
    {
        return $this->_state;
    }

    public function getExpire()
    {
        return $this->_expire;
    }

    public function getToken($forceNew = false)
    {
        $token = $this->get('session.token');

        // Create a token
        if ($token === null || $forceNew)
        {
            $token = $this->_createToken();
            $this->set('session.token', $token);
        }

        return $token;
    }

    public function hasToken($tCheck, $forceExpire = true)
    {
        // Check if a token exists in the session
        $tStored = $this->get('session.token');

        // Check token
        if (($tStored !== $tCheck))
        {
            if ($forceExpire)
            {
                $this->_state = 'expired';
            }

            return false;
        }

        return true;
    }

    public static function checkToken()
    {

        $session = Factory::getSession();

        if ($session->isNew())
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Returns a clone of the internal data pointer
     *
     * @return  Registry 注册表结构数据
     */
    public function getData()
    {
        return clone $this->data;
    }

    /**
     * Shorthand to check if the session is active
     *
     * @return  boolean
     */

    public function isActive()
    {
        return (bool) ($this->_state == 'active');
    }

    /**
     * 检查session是否为刚在当前环境创建的
    */
    public function isNew()
    {
        $counter = $this->get('session.counter');

        return (bool) ($counter === 1);
    }

    public function get($name, $default = null, $namespace = 'default')
    {
        // Add prefix to namespace to avoid collisions
        $namespace = '__' . $namespace;

        if ($this->_state === 'destroyed')
        {
            // @TODO :: generated error here
            $error = null;

            return $error;
        }

        return $this->data->get($namespace . '.' . $name, $default);
    }

    public function set($name, $value = null, $namespace = 'default')
    {
        // Add prefix to namespace to avoid collisions
        $namespace = '__' . $namespace;

        if ($this->_state !== 'active')
        {
            // @TODO :: generated error here
            return;
        }

        $prev = $this->data->get($namespace . '.' . $name, null);
        $this->data->set($namespace . '.' . $name, $value);

        return $prev;
    }

    public function has($name, $namespace = 'default')
    {
        // Add prefix to namespace to avoid collisions.
        $namespace = '__' . $namespace;

        if ($this->_state !== 'active')
        {
            // @TODO :: generated error here
            return;
        }

        return !is_null($this->data->get($namespace . '.' . $name, null));
    }

    public function clear($name, $namespace = 'default')
    {
        // Add prefix to namespace to avoid collisions
        $namespace = '__' . $namespace;

        if ($this->_state !== 'active')
        {
            // @TODO :: generated error here
            return;
        }

        return $this->data->set($namespace . '.' . $name, null);
    }

    /**
     * Session 启动
     *
     * @return  void
     */
    public function start()
    {
        if ($this->_state === 'active')
        {
            return;
        }

        $this->_start();

        $this->_state = 'active';

        // Initialise the session
        $this->_setCounter();
        $this->_setTimers();

        // Perform security checks
        if (!$this->_validate())
        {
            // If the session isn't valid because it expired try to restart it
            // else destroy it.
            if ($this->_state === 'expired')
            {
                $this->restart();
            }
            else
            {
                $this->destroy();
            }
        }

    }
    /**
     * session启动.
     *
     * 创建一个session或者根据session状态回复一个session
     * Creates a session (or resumes the current one based on the state of the session)
     *
     * @return  boolean  true on success
     *
     */
    protected function _start()
    {

        // Ok let's unserialize the whole thing
        // Try loading data from the session
        if (isset($_SESSION['jinan']) && !empty($_SESSION['jinan']))
        {
            $data = $_SESSION['jinan'];

            $data = base64_decode($data);

            $this->data = unserialize($data);
        }

        return true;
    }

    /**
     * 销毁一个session
     *
    */
    public function destroy()
    {
        // Session was already destroyed
        if ($this->_state === 'destroyed')
        {
            return true;
        }

        // Create new data storage
        $this->data = new Registry;

        $this->_state = 'destroyed';

        return true;
    }

    /**
     * 重启过期的session或者锁定session
     * Restart an expired or locked session.
     *
     * @return  boolean  True on success
     *
     * @see     Session::destroy()
     */
    public function restart()
    {
        $this->destroy();

        if ($this->_state !== 'destroyed')
        {
            // @TODO :: generated error here
            return false;
        }


        $this->_state = 'restart';

        // Regenerate session id
        $this->_start();

        $this->_state = 'active';

        if (!$this->_validate())
        {
            /**
             * Destroy the session if it's not valid - we can't restart the session here unlike in the start method
             * else we risk recursion.
             */
            $this->destroy();
        }

        $this->_setCounter();

        return true;
    }

    public function close(){

        $session = Factory::getSession();
        $data    = $session->getData();

        // Before storing it, let's serialize and encode the Registry object
        $_SESSION['jinan'] = base64_encode(serialize($data));
    }

    /**
     * 创建一个token
    */
    protected function _createToken($length = 32)
    {
        $salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $base = strlen($salt);
        $makepass = '';

        /*
         * Start with a cryptographic strength random string, then convert it to
         * a string with the numeric base of the salt.
         * Shift the base conversion on each character so the character
         * distribution is even, and randomize the start shift so it's not
         * predictable.
         */
        $random = $this->random_bytes($length + 1);
        $shift = ord($random[0]);

        for ($i = 1; $i <= $length; ++$i)
        {
            $makepass .= $salt[($shift + ord($random[$i])) % $base];
            $shift += ord($random[$i]);
        }

        return $makepass;
    }

    protected function _setCounter()
    {
        $counter = $this->get('session.counter', 0);
        ++$counter;

        $this->set('session.counter', $counter);

        return true;
    }


    protected function _setTimers()
    {
        if (!$this->has('session.timer.start'))
        {
            $start = time();

            $this->set('session.timer.start', $start);
            $this->set('session.timer.last', $start);
            $this->set('session.timer.now', $start);
        }

        $this->set('session.timer.last', $this->get('session.timer.now'));
        $this->set('session.timer.now', time());

        return true;
    }

    protected function _setOptions(array $options)
    {

        // Set expire time
        if (isset($options['expire']))
        {
            $this->_expire = $options['expire'];
        }

        // Get security options
        if (isset($options['security']))
        {
            $this->_security = explode(',', $options['security']);
        }

        // Sync the session maxlifetime
        //ini_set('session.gc_maxlifetime', $this->_expire);

        return true;
    }

    public function random_bytes($bytes)
    {
        try {
            $bytes = $this->RandomCompat_intval($bytes);
        } catch (\Exception $ex) {
            throw new \Exception(
                'random_bytes(): $bytes must be an integer'
            );
        }

        if ($bytes < 1) {
            throw new \Exception(
                'Length must be greater than 0'
            );
        }

        $buf = @mcrypt_create_iv($bytes, MCRYPT_DEV_URANDOM);
        if (
            $buf !== false
            &&
            $this->RandomCompat_intval($buf) === $bytes
        ) {
            /**
             * Return our random entropy buffer here:
             */
            return $buf;
        }

        /**
         * If we reach here, PHP has failed us.
         */
        throw new \Exception(
            'Could not gather sufficient random data'
        );
    }

    public function RandomCompat_intval($number, $fail_open = false)
    {
        if (is_numeric($number)) {
            $number += 0;
        }

        if (
            is_float($number)
            &&
            $number > ~PHP_INT_MAX
            &&
            $number < PHP_INT_MAX
        ) {
            $number = (int) $number;
        }

        if (is_int($number) || $fail_open) {
            return $number;
        }

        throw new \Exception(
            'Expected an integer.'
        );
    }

    protected function _validate($restart = false)
    {
        // Allow to restart a session
        if ($restart)
        {
            $this->_state = 'active';

            $this->set('session.client.address', null);
            $this->set('session.client.forwarded', null);
            $this->set('session.client.browser', null);
            $this->set('session.token', null);
        }

        // Check if session has expired
        if ($this->_expire)
        {
            $curTime = $this->get('session.timer.now', 0);
            $maxTime = $this->get('session.timer.last', 0) + $this->_expire;

            // Empty session variables
            if ($maxTime < $curTime)
            {
                $this->_state = 'expired';

                return false;
            }
        }

        // Check for client address
        if (in_array('fix_adress', $this->_security) && isset($_SERVER['REMOTE_ADDR'])
            && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) !== false)
        {
            $ip = $this->get('session.client.address');

            if ($ip === null)
            {
                $this->set('session.client.address', $_SERVER['REMOTE_ADDR']);
            }
            elseif ($_SERVER['REMOTE_ADDR'] !== $ip)
            {
                $this->_state = 'error';

                return false;
            }
        }

        // Record proxy forwarded for in the session in case we need it later
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP) !== false)
        {
            $this->set('session.client.forwarded', $_SERVER['HTTP_X_FORWARDED_FOR']);
        }

        return true;
    }
}