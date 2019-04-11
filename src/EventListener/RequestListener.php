<?php

/**
 * Created by PhpStorm.
 * User: Do Tien Thanh
 * Date: 1/12/2016
 * Time: 10:03 AM
 */

namespace App\EventListener;

use App\Utils\CacheProvider;
use App\Utils\Constants;
use App\Utils\Lib;
use \Symfony\Component\HttpKernel\Event\GetResponseEvent;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\DependencyInjection\ContainerInterface;
use \Symfony\Component\HttpKernel\HttpKernel;

class RequestListener
{
    protected $container;

    public function __construct(ContainerInterface $container) // this is @service_container
    {
        $this->container = $container;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            // don't do anything if it's not the master request
            return;
        }
        $request = $event->getRequest();
        $url = $request->getRequestUri();
        if (!Lib::checkValidPageUrl($url)) {
            return;
        }
        $ip = isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER['REMOTE_ADDR'];
        // Check IP blacklist
        $cacheConfig = $this->container->getParameter('cache_config');
        $redisCrawler = CacheProvider::createInstance($request, Constants::SERVER_CACHE_UPDATE_VIEW, $cacheConfig, CacheProvider::REDIS);
        if ($redisCrawler->hexists(Constants::IP_BLACKLIST_HASH, $ip)) {
            $htmlContent = "You are blocked. Please contact to info@indianautosblog.com. Thanks!";
            $response = new Response(Lib::gzip($htmlContent));
            $response->headers->set('Content-Encoding', 'gzip');
            $event->setResponse($response);
            return;
        }

        // Detect crawler
        if ($redisCrawler->exists(Constants::ALLOW_TRACKING_CRAWLER) && !preg_match('#\\.(jpg|jpeg|png|gif|css|js)$#', $url)) {
            $redisCrawler->zincrby(Constants::CRAWLER_IP_LIST, 1, $ip);
        }

        // If don't allow cache page
        if ($cacheConfig['allow_cache_page'] !== 1) {
            return;
        }
        // Get cache page
        $cacheService = CacheProvider::createInstance($request, Constants::SERVER_CACHE_FULL_PAGE, $cacheConfig, CacheProvider::REDIS);
        $htmlContent = $cacheService->getString($url);
        if ($htmlContent) {
            $response = new Response($htmlContent);
            $response->headers->set('Content-Encoding', 'gzip');
            $response->headers->set('X-Cache-Page', '1');
            $event->setResponse($response);
        }
    }
}