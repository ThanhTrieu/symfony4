<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

//use Symfony\Component\HttpFoundation\RequestStack;

class Memcache
{
    private $memcache;
    private $allowCacheData;
    private $allowRefreshCache;
    private $allowManualCache;
    private $server;
    private $port;
    private $timeout;

    //public function __construct(ContainerInterface $container, Request $request, $serverCache)
    public function __construct(Request $request, $serverCache, $cacheParams)
    {
        global $cacheConfig;
        // Get redis config from global
        if (isset($cacheConfig)) {
            $this->allowCacheData = $cacheConfig['allowCacheData'];
            if (!$this->allowCacheData) {
                return;
            }
            $this->allowRefreshCache = $cacheConfig['allowRefreshCache'];
            $this->allowManualCache = $cacheConfig['allowManualCache'];
        } else {
            $this->allowCacheData = $cacheParams['allow_cache_data'] == "1";
            if (!$this->allowCacheData) {
                return;
            }
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
            $this->timeout = $cacheConfig[$serverCache]['timeout'];
        } else {
            $serverDns = isset($cacheParams[$serverCache]) ? $cacheParams[$serverCache] : '';
            if (!$serverDns) {
                throw new \Exception("Server redis not exist");
                return;
            }
            $serverInfo = explode(':', $serverDns);
            if (count($serverInfo) < 2) {
                throw new \Exception("memcached config is invalid");
                return;
            }
            $this->server = $serverInfo[0];
            $this->port = $serverInfo[1];
            $this->timeout = isset($serverInfo[2]) ? $serverInfo[2] : 1;

            //::createConnection($container->getParameter($server)); //$container->get($server);
            //$request = $requestStack->getCurrentRequest();

            $cacheConfig[$serverCache]['server'] = $this->server;
            $cacheConfig[$serverCache]['port'] = $this->port;
            $cacheConfig[$serverCache]['timeout'] = $this->timeout;
        }

        $this->memcache = new \Memcache(); //$container->get($server);
        $this->memcache->connect($this->server, $this->port, $this->timeout);
    }

    public function get($key)
    {
        if (!$this->allowCacheData) {
            return false;
        }
        if ($this->allowRefreshCache) {
            $this->memcache->delete($key);
            return false;
        }
        return $this->memcache->get($key);
    }

    public function set($key, $value, $exp = 0, $flag = 0)
    {
        if (!$this->allowCacheData) {
            return false;
        }
        $this->memcache->set($key, $value, $flag, $exp);
    }

    #enregion
    public function exists($key)
    {
        if (!$this->allowCacheData) {
            return false;
        }
        if ($this->allowRefreshCache) {
            $this->memcache->delete($key);
            return false;
        }

        if ($this->memcache->get($key) !== false) {
            return true;
        }
        return false;
    }

    public function delete($key)
    {
        if (!$this->allowCacheData) {
            return false;
        }
        $this->memcache->delete($key);
    }

    /* Manual cache*/
    /**
     * Get cache with no delete
     * author: ThanhDT
     * date:   2018-02-07 09:11 AM
     * @param $key
     * @return bool
     */
    public function persistentGet($key)
    {
        if (!$this->allowCacheData) {
            return false;
        }

        $value = $this->memcache->get($key);
        return $value;
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
            $this->memcache->delete($key);
            return false;
        }
        return $this->memcache->get($key);
    }

    /**
     * Set manual cache
     * @param $key
     * @param $value
     * @param int $exp
     * @param int $flag
     * @return bool
     */
    public function manualSet($key, $value, $exp = 0, $flag = 0)
    {
        if (!$this->allowCacheData) {
            return false;
        }
        $this->memcache->set($key, $value, $flag, $exp);
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
            $this->memcache->delete($key);
            return false;
        }

        if ($this->memcache->get($key) !== false) {
            return true;
        }
        return false;
    }
}
