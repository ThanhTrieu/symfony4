<?php
/**
 * Created by PhpStorm.
 * User: ThanhDT
 * Date: 11/8/2017
 * Time: 10:33 AM
 */

namespace App\Controller;

use App\Service\CryptUtils;
use App\Service\DataExchange;
use App\Utils\CacheProvider;
use App\Utils\Constants;
use App\Entity\AdminUsers;
use App\Entity\PostPublishes;
use App\Service\Elasticsearch;
use Symfony\Component\HttpFoundation\Request;
use App\Utils\Lib;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseController extends AbstractController
{
    const LIMIT_ITEMS = 12;

    /**
     * Get cache provider for all controller
     * author: ThanhDT
     * date:   2017-11-08 04:33 PM
     * @param $serverCache
     * @return mixed
     * @throws \Exception
     */
    protected function getCacheProvider($serverCache)
    {
        $cacheService = CacheProvider::createInstance(
            $this->get('request_stack')->getCurrentRequest(),
            $serverCache,
            $this->getParameter('cache_config')
        );
        return $cacheService;
    }

    /**
     * Get Redis cache provider
     * author: ThanhDT
     * date:   2017-11-09 09:10 AM
     * @param $serverCache
     * @return \App\Service\RedisUtils
     * @throws \Exception
     */
    protected function getRedisProvider($serverCache)
    {
        $cacheService = CacheProvider::createInstance(
            $this->get('request_stack')->getCurrentRequest(),
            $serverCache,
            $this->getParameter('cache_config'),
            CacheProvider::REDIS
        );
        return $cacheService;
    }

    /**
     * Add cache page
     * author: ThanhDT
     * date:   2018-12-15 04:44 PM
     * @param $request
     * @param $response
     * @param int $cacheTime
     */
    protected function addCachePage($request, $response, $cacheTime = 0)
    {
        $cacheConfig = $this->getParameter('cache_config');
        if ($cacheConfig['allow_cache_page'] !== 1) {
            return;
        }
        if ($cacheTime == 0) {
            $cacheTime = $this->getParameter('cache_time')['hour'];
        }

        Lib::AddCachePage($request, $response, $cacheConfig, $cacheTime);
    }

    /**
     * Get Author information
     * author: ThanhDT
     * date:   2018-07-03 10:15 AM
     * @param $userSlug
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getAuthor($userSlug, $dataExchangeService)
    {
        $key_author = sprintf(Constants::TABLE_AUTHOR_USERNICENAME, $userSlug);
        $service_cache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $author = $service_cache->get($key_author);
        if ($author === false) {
            $em = $this->getDoctrine()->getManager();
            $authorInfo = $em->getRepository(AdminUsers::class)->getDetailAuthor($userSlug);
            if ($authorInfo) {
                $author = $dataExchangeService->exchangeAuthorData($authorInfo);
                $service_cache->set($key_author, $author, $this->getParameter('cache_time')['hour']);
            }
        }

        return $author;
    }

    protected function getAuthorById($userId, $dataExchangeService)
    {
        $key_author = sprintf(Constants::TABLE_AUTHOR_USERID, $userId);
        $service_cache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $author = $service_cache->get($key_author);
        if ($author === false) {
            $em = $this->getDoctrine()->getManager();
            $authorInfo = $em->getRepository(AdminUsers::class)->getDetailAuthorById($userId);
            if ($authorInfo) {
                $author = $dataExchangeService->exchangeAuthorData($authorInfo);
                $service_cache->set($key_author, $author, $this->getParameter('cache_time')['hour']);
            }
        }

        return $author;
    }

    /**
     * Get post url by slug
     * author: ThanhDT
     * date:   2018-08-27 11:33 PM
     * @param $slug
     * @return null|string
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getPostUrlBySlug($slug)
    {
        $service_cache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $keyDetail = sprintf(Constants::TABLE_ARTICLE_DETAIL_URL_BY_SLUG, $slug);
        if (($detailUrl = $service_cache->get($keyDetail)) === false) {
            $em = $this->getDoctrine()->getManager();
            $data = $em->getRepository(PostPublishes::class)->getShortDetailBySlug($slug);
            if ($data) {
                $detailUrl = $this->generateUrl('news_detail', ['slug' => $data['slug'], 'postId' => $data['postId']]);
            } else {
                $detailUrl = null;
            }
            $service_cache->set($keyDetail, $detailUrl, $this->getParameter('cache_time')['hour']);
            return $detailUrl;
        }
        return $detailUrl;
    }

    /**
     * Get post by slug
     * author: ThanhDT
     * date:   2018-08-30 03:34 PM
     * @param $slug
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getShortPostBySlug($slug)
    {
        $service_cache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $keyDetail = sprintf(Constants::TABLE_ARTICLE_DETAIL_SHORT_BY_SLUG, $slug);
        if (($detailBySlug = $service_cache->get($keyDetail)) === false) {
            $em = $this->getDoctrine()->getManager();
            $detailBySlug = $em->getRepository(PostPublishes::class)->getShortDetailBySlug($slug);
            $service_cache->set($keyDetail, $detailBySlug, $this->getParameter('cache_time')['hour']);
            return $detailBySlug;
        }
        return $detailBySlug;
    }

    /**
     * @param $slug
     * @param Request $request
     * @param Elasticsearch $elasticSearch
     * @param DataExchange $dataExchangeService
     * @param $isAmp
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function changeToNotFoundPageBySlug($slug, $request, $elasticSearch, $dataExchangeService, $isAmp = false)
    {
        $total = 0;
        $error = '';
        $limited = ($isAmp) ? (self::LIMIT_ITEMS * 2) : self::LIMIT_ITEMS;

        $slug = str_replace('-', ' ', $slug);

        $page = $request->get('page');
        $page = (is_numeric($page) && $page > 0) ? $page : 1;
        $start = ($page - 1) * $limited;
        // search for title or sapo
        $queryData = '{"multi_match" : {"query" : "' . $slug . '", "fields": ["title", "sapo"] }}';
        $data = $elasticSearch->search(
            Elasticsearch::INDIA_POSTS_INDEX,
            $queryData,
            $start,
            $limited,
            $total,
            $error
        );
        $totalRecord = $total;

        $mainData = $dataExchangeService->exchangeArraySearchPost($data, Constants::POST_AVATAR_LIST_SIZE);
        // pagination
        $totalPage = $total > 0 ? ceil($totalRecord / self::LIMIT_ITEMS) : 0;

        // show prev page
        $prePage = ($page > 1 && $page <= $totalPage) ? true : false;
        // show next page
        $nextPage = ($page >= 1 && $page < $totalPage) ? true : false;

        // Build Seo
        $searchUrl = $this->generateUrl('search') . '?q=' . $slug;
        $seo = $this->buildPagingMeta($searchUrl, $slug, $page, $totalPage, $this->getParameter('site_name'));
        $seo = array_merge($seo, array(
            'og_type' => 'object',
            'is_home' => true,
        ));
        if ($isAmp) {
            $response = $this->render('default/search-amp.html.twig', [
                'data' => $mainData,
                'keyword' => $slug,
                'page' => $page,
                'totalPage' => $totalPage,
                'prePage' => $prePage,
                'nextPage' => $nextPage,
                'seo' => $seo,
                'module' => 'notPage',
                'totalRecord' => $totalRecord
            ]);
        } else {
            $response = $this->render('default/search.html.twig', [
                'data' => $mainData,
                'keyword' => $slug,
                'page' => $page,
                'totalPage' => $totalPage,
                'prePage' => $prePage,
                'nextPage' => $nextPage,
                'seo' => $seo,
                'module' => 'notPage',
                'totalRecord' => $totalRecord
            ]);
        }
        return $response;
    }

    /**
     * Build paging meta
     * author: ThanhDT
     * date:   2018-12-19 10:06 AM
     * @param $baseUrl
     * @param $title
     * @param $pageIndex
     * @param $pageCount
     * @param string $suffix
     * @return array
     */
    public function buildPagingMeta($baseUrl, $title, $pageIndex, $pageCount, $suffix = '')
    {
        $seo = [];
        if ($pageIndex == 1) {
            $seo['title'] = $title;
        } else {
            $seo['title'] = sprintf(Constants::TITLE_SEO_PAGING_FORMAT, $title, $pageIndex, $pageCount);
        }
        if (!empty($suffix)) {
            $seo['title'] .= ' - ' . $suffix;
        }
        $seo['url'] = $this->getParameter('domain') . $baseUrl;
        $seo['mobile_url'] = $this->getParameter('mobile') . $baseUrl;
        /*$pageUrl = $baseUrl[strlen($baseUrl) - 1] == '/' ? $baseUrl . 'page/' : $baseUrl . '/page/';
        if ($pageIndex != 1) {
            if ($pageIndex != 2) {
                $seo['prev_url'] = $pageUrl . ($pageIndex - 1);
            } else {
                $seo['prev_url'] = $baseUrl;
            }
            $seo['url'] = $pageUrl . $pageIndex;
        } else {
            $seo['url'] = $baseUrl;
        }
        if ($pageCount != 1) {
            if ($pageIndex != $pageCount) {
                $seo['next_url'] = $pageUrl . ($pageIndex + 1);
            }
        }*/

        return $seo;
    }

    /**
     * Encrypt data
     * author: ThanhDT
     * date:   2018-12-24 11:45 PM
     * @param CryptUtils $cryptUtils
     * @param $arrData : Array data
     * @return string
     */
    protected function encrypt(CryptUtils $cryptUtils, $arrData)
    {
        if ($arrData == null) {
            return null;
        }
        return $cryptUtils->encrypt(json_encode($arrData));
    }

    /**
     * Decrypt data
     * author: ThanhDT
     * date:   2018-12-24 11:45 PM
     * @param CryptUtils $cryptUtils
     * @param $encryptedString
     * @return string
     */
    protected function decrypt(CryptUtils $cryptUtils, $encryptedString)
    {
        return json_decode($cryptUtils->decrypt($encryptedString), true);
    }

    /**
     * Format cache key for mobile
     * author: ThanhDT
     * date:   2018-12-14 11:59 AM
     * @param $format
     * @param null $args
     * @param null $_
     * @return string
     */
    protected function formatCacheKeyAmp($format, ...$args)
    {
        if ($args) {
            return Constants::WEB_AMP . vsprintf($format, $args);
        }

        return Constants::WEB_AMP . $format;
    }
}
