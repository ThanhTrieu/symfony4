<?php
/**
 * Created by PhpStorm.
 * User: Do Tien Thanh
 * Date: 1/9/2016
 * Time: 4:44 PM
 */

namespace App\Service;

use App\Utils\Lib;
use Symfony\Component\HttpFoundation\Request;

class RedisUtils
{
    private $allowCacheData;
    private $allowRefreshCache;
    private $allowManualCache;
    public $redis;
    private $server;
    private $port;
    private $db;
    private $timeout;

    public function __construct(Request $request, $serverCache, $cacheParams)
    {
        global $cacheConfig;
        // Get redis config from global
        if (isset($cacheConfig)) {
            $this->allowCacheData = $cacheConfig['allowCacheData'];
            /*if (!$this->allowCacheData) {
                return;
            }*/
            $this->allowRefreshCache = $cacheConfig['allowRefreshCache'];
            $this->allowManualCache = $cacheConfig['allowManualCache'];
        } else {
            $this->allowCacheData = $cacheParams['allow_cache_data'] == "1";
            /*if (!$this->allowCacheData) {
                return;
            }*/
            if ($request !== null) {
                $this->allowRefreshCache = $request->headers->get('X-Refresh-Cache') == $cacheParams['refresh_cache'];
                $this->allowManualCache = $request->headers->get('X-Manual-Cache') == $cacheParams['manual_cache'];
            } else {
                $this->allowRefreshCache = false;
                $this->allowManualCache = false;
            }
            $cacheConfig['allowCacheData'] = $this->allowCacheData;
            $cacheConfig['allowRefreshCache'] = $this->allowRefreshCache;
            $cacheConfig['allowManualCache'] = $this->allowManualCache;
        }
        if (isset($cacheConfig[$serverCache])) {
            $this->server = $cacheConfig[$serverCache]['server'];
            $this->port = $cacheConfig[$serverCache]['port'];
            $this->db = $cacheConfig[$serverCache]['db'];
            $this->timeout = $cacheConfig[$serverCache]['timeout'];
        } else {
            $serverDns = isset($cacheParams[$serverCache]) ? $cacheParams[$serverCache] : '';
            if (!$serverDns) {
                throw new \Exception("Server redis not exist");
                return;
            }
            $serverInfo = explode(':', $serverDns);
            if (count($serverInfo) < 3) {
                throw new \Exception("redis config is invalid");
                return;
            }
            $this->server = $serverInfo[0];
            $this->port = $serverInfo[1];
            $this->db = $serverInfo[2];
            $this->timeout = isset($serverInfo[3]) ? $serverInfo[3] : 1;

            //::createConnection($container->getParameter($server)); //$container->get($server);
            //$request = $requestStack->getCurrentRequest();

            $cacheConfig[$serverCache]['server'] = $this->server;
            $cacheConfig[$serverCache]['port'] = $this->port;
            $cacheConfig[$serverCache]['db'] = $this->db;
            $cacheConfig[$serverCache]['timeout'] = $this->timeout;
        }

        $this->redis = new \Redis(); //$container->get($server);
        try {
            $this->redis->connect($this->server, $this->port, $this->timeout);
        } catch (\Exception $ex) {
            $cacheConfig['allowCacheData'] = false;
            $cacheConfig['allowRefreshCache'] = false;
            $cacheConfig['allowManualCache'] = false;
            return;
        }
        //var_dump($this->redis->info());die;
        $this->redis->select($this->db);
    }

    /*
     * Get from redis with key by Unzip data
     * Author: ThanhDT
     * Date: 2016-01-12 4:24 PM
     * */
    public function get($key)
    {
        if (!$this->allowCacheData) {
            return false;
        }
        if ($this->allowRefreshCache) {
            $this->redis->del($key);
            return false;
        }
        $value = $this->redis->get($key);
        return $value == null ? false : Lib::cacheDecode($value);
    }

    /*
     * Set Zip data to redis
     * Author: ThanhDT
     * Date: 2016-01-12 4:24 PM
     * */
    public function set($key, $value, $expire = 0, $flag = 0)
    {
        if (!$this->allowCacheData) {
            return false;
        }
        $value = Lib::cacheEncode($value);
        if ($expire == 0) {
            $this->redis->set($key, $value);
        } else {
            $this->redis->setex($key, $expire, $value);
        }
    }

    public function setex($key, $value, $expire)
    {
        if (!$this->allowCacheData) {
            return false;
        }
        $value = Lib::cacheEncode($value);
        $this->redis->setex($key, $expire, $value);
    }

    public function delete($key)
    {
        $this->redis->del($key);
    }

    #region Get cache with Gzip

    /*
     * Get from redis with key by Unzip data
     * Author: ThanhDT
     * Date: 2016-01-12 4:24 PM
     * */
    public function getString($key)
    {
        if (!$this->allowCacheData) {
            return false;
        }
        if ($this->allowRefreshCache) {
            $this->redis->del($key);
            return false;
        }
        $value = $this->redis->get($key);
        return $value;
    }

    public function setString($key, $value, $expire)
    {
        if (!$this->allowCacheData) {
            return false;
        }
        $this->redis->setex($key, $expire, $value);
    }

    public function setStringEx($key, $value, $expire)
    {
        if (!$this->allowCacheData) {
            return false;
        }
        $this->redis->setex($key, $expire, $value);
    }

    #enregion

    public function exists($key)
    {
        if (!$this->allowCacheData) {
            return false;
        }
        if ($this->allowRefreshCache) {
            $this->redis->del($key);
            return false;
        }

        if ($this->redis->exists($key)) {
            return true;
        }
        return false;
    }

    /* Manual cache*/
    /**
     * Get no clear cache
     * @param $key
     * @return bool
     */
    public function persistentGet($key)
    {
        if (!$this->allowCacheData) {
            return false;
        }

        $value = $this->redis->get($key);
        return $value == null ? false : Lib::cacheDecode($value);
    }

    /**
     * Get manual cache
     * @param $key
     * @return bool
     */
    public function manualGet($key)
    {
        if (!$this->allowCacheData) {
            return false;
        }
        if ($this->allowManualCache) {
            $this->redis->del($key);
            return false;
        }
        $value = $this->redis->get($key);
        return $value == null ? false : Lib::cacheDecode($value);
    }

    /**
     * Set manual cache
     * @param $key
     * @param $value
     * @param int $exp
     * @param int $flag
     * @return bool
     */
    public function manualSet($key, $value, $exp = 0)
    {
        if (!$this->allowCacheData) {
            return false;
        }
        $value = Lib::cacheEncode($value);
        if ($exp == 0) {
            $this->redis->set($key, $value);
        } else {
            $this->redis->setex($key, $exp, $value);
        }
    }

    /**
     * Check exist manual cache
     * @param $key
     * @return bool
     */
    public function manualExists($key)
    {
        if (!$this->allowCacheData) {
            return false;
        }
        if ($this->allowManualCache) {
            $this->redis->del($key);
            return false;
        }

        if ($this->redis->exists($key)) {
            return true;
        }
        return false;
    }

    // Only Redis function

    /**
     * Check hash exist
     * author: ThanhDT
     * date:   2017-11-09 08:35 AM
     * @param $key
     * @param $hashKey
     * @return bool
     */
    public function hexists($key, $hashKey)
    {
        if (!$this->allowCacheData) {
            return false;
        }
        return $this->redis->hExists($key, $hashKey);
    }

    /**
     * Get all item in hash
     * author: ThanhDT
     * date:   2017-11-09 08:40 AM
     * @param $key
     * @return array
     */
    public function hgetall($key)
    {
        if (!$this->allowCacheData) {
            return false;
        }
        return $this->redis->hgetall($key);
    }

    /**
     * Set item to hash
     * author: ThanhDT
     * date:   2017-11-09 08:43 AM
     * @param $key
     * @param $hashKey
     * @param $value
     * @return int
     */
    public function hset($key, $hashKey, $value)
    {
        if (!$this->allowCacheData) {
            return false;
        }
        return $this->redis->hSet($key, $hashKey, $value);
    }

    /**
     * Delete item in hash
     * author: ThanhDT
     * date:   2017-11-09 08:41 AM
     * @param $key
     * @param $item
     * @return int
     */
    public function hdel($key, $item)
    {
        if (!$this->allowCacheData) {
            return false;
        }
        return $this->redis->hDel($key, $item);
    }

    /**
     * Get revert sorted set by score
     * author: ThanhDT
     * date:   2017-11-09 09:08 AM
     * @param $key
     * @param $start
     * @param $end
     * @param array $options
     * @return array
     */
    public function zRevRangeByScore($key, $start, $end, array $options = array())
    {
        if (!$this->allowCacheData) {
            return false;
        }
        return $this->redis->zRevRangeByScore($key, $start, $end, $options);
    }

    /**
     * Increment item in sorted set by value
     * author: ThanhDT
     * date:   2017-11-09 08:45 AM
     * @param $key
     * @param $value
     * @param $member
     * @return float
     */
    public function zincrby($key, $value, $member)
    {
        if (!$this->allowCacheData) {
            return false;
        }
        return $this->redis->zIncrBy($key, $value, $member);
    }

    /**
     * Left push value to list
     * author: ThanhDT
     * date:   2017-11-10 11:55 AM
     * @param $key
     * @param $value
     * @return int
     */
    public function lpush($key, $value)
    {
        return $this->redis->lPush($key, $value);
    }

    // End Only Redis function

    public function __destruct()
    {
        if ($this->redis) {
            $this->redis->close();
        }
    }
}
