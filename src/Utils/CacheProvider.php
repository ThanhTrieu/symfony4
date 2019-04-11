<?php
/**
 * Created by PhpStorm.
 * User: ThanhDT
 * Date: 11/9/2017
 * Time: 8:56 AM
 */

namespace App\Utils;

use App\Service\Memcache;
use App\Service\RedisUtils;
use Symfony\Component\HttpFoundation\Request;

class CacheProvider
{
    const MEMCACHED = 0;
    const REDIS = 1;

    /**
     * Provider cache server
     * author: ThanhDT
     * date:   2018-10-18 09:19 PM
     * @param Request $request
     * @param $serverCache
     * @param $cacheParams
     * @param int $cacheType
     * @return Memcache|RedisUtils
     * @throws \Exception
     */
    public static function createInstance(Request $request, $serverCache, $cacheParams, $cacheType = 1)
    {
        switch ($cacheType) {
            // Init Redis
            case self::REDIS:
                $cacheService = new RedisUtils($request, $serverCache, $cacheParams);
                break;
            // Init memcached
            default:
                $cacheService = new Memcache($request, $serverCache, $cacheParams);
                break;
        }

        return $cacheService;
    }
}
