<?php
/**
 * Created by PhpStorm.
 * User: ThanhDT
 * Date: 5/22/2018
 * Time: 8:29 AM
 */

namespace App\Controller\Category;

use App\Controller\BaseController;
use App\Entity\PostPublishes;
use App\Service\Category;
use App\Service\CryptUtils;
use App\Service\DataExchange;
use App\Utils\Constants;
use App\Utils\Lib;
use Symfony\Component\HttpFoundation\Request;

//use App\Services\Author;
class IndexController extends BaseController
{
    /**
     * author: AnhPT4
     * date:   2018-10-18 11:56 AM
     * @param int $currentPage
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function list($currentPage = 1, DataExchange $dataExchangeService, Category $cateService, Request $request, CryptUtils $cryptUtils)
    {
        // Process category info
        $rootSlug = $request->get('rootSlug');
        $parentSlug = strtolower($request->get('parentSlug'));
        $cateSlug = strtolower($request->get('cateSlug'));
        $cate = $cateService->getCateBySlug($cateSlug);

        if ($cate == null) {
            return $this->forward('App\Controller\DefaultController::_404page');
        }

        // Cate parent slug is invalid
        if ($cate['parentId'] != 0) {
            $parentCate = $cateService->getCateById($cate['parentId']);
            if ($parentCate != null) {
                if ($parentCate['parentId'] == 0) {
                    if ($parentCate['slug'] != $parentSlug) {
                        return $this->redirectToRoute('news_sub_cate', ['parentSlug' => $parentCate['slug'], 'cateSlug' => $cateSlug], 301);
                        /*if ($currentPage == 1) {
                            return $this->redirectToRoute('news_sub_cate', ['parentSlug' => $parentCate['slug'], 'cateSlug' => $cateSlug], 301);
                        } else {
                            return $this->redirectToRoute('news_sub_cate_paging', ['parentSlug' => $parentCate['slug'], 'cateSlug' => $cateSlug, 'currentPage' => $currentPage], 301);
                        }*/
                    }
                } else {
                    $rootCate = $cateService->getCateById($parentCate['parentId']);
                    if ($rootCate != null && ($rootCate['slug'] != $rootSlug || $parentCate['slug'] != $parentSlug)) {
                        return $this->redirectToRoute('news_sub_cate_level2', ['rootSlug' => $rootCate['slug'], 'parentSlug' => $parentCate['slug'], 'cateSlug' => $cateSlug], 301);
                    }
                }
            }
        }

        // list category child : name, id , slug , url
        $getCategoryChild = $cateService->getCategoryChild($cate['cateId']);
        $array_child = [];
        $limit_child = 0;
        if (count($getCategoryChild)) {
            foreach ($getCategoryChild as $key) {
                if ($limit_child == 7) {
                    break;
                }

                $array_child[$limit_child]['cateId'] = $key['cateId'];
                $array_child[$limit_child]['name'] = $key['name'];
                $array_child[$limit_child]['slug'] = $key['slug'];

                $url = $this->generateUrl(
                    'news_sub_cate',
                    array('parentSlug' => $cateSlug, 'cateSlug' => $key['slug'])
                );
                $array_child[$limit_child]['url'] = $url;
                $limit_child++;
            }
        }

        $cateId = $cate['cateId'];
        if ($cate['parentId'] == 0) {
            $cateIdList = $cateService->getCategoryParentId($cate['cateId']);
        } else {
            $cateIdList = $cateId;
        }

        // Get data Post in category
        $articleKeyCache = sprintf(Constants::CACHE_CATEGORY_LASTEST_PAGE, $cateId, $currentPage);
        $service_cache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $catePostInfo = $service_cache->get($articleKeyCache);
        if ($catePostInfo === false) {
            $em = $this->getDoctrine()->getManager();
            $sourcePosts = $em->getRepository(PostPublishes::class)->getArticleInCateTimestamp($cateIdList, Constants::PAGE_SIZE + 1, time());
            if ($sourcePosts) {
                if (count($sourcePosts) > Constants::PAGE_SIZE) {
                    $sourcePosts = array_slice($sourcePosts, 0, Constants::PAGE_SIZE);
                    $lastHomeStream = $sourcePosts[Constants::PAGE_SIZE - 1];
                    $lastInfo = [
                        'cateId' => $cateId,
                        'lastPostId' => $lastHomeStream['postId'],
                        'lastPublishedTimestamp' => $lastHomeStream['publishedTimestamp'],
                        'nextPage' => $currentPage + 1
                    ];
                } else {
                    $lastInfo = null;
                }
                $sourcePosts = $dataExchangeService->ExchangeArrayArticle($sourcePosts, Constants::POST_AVATAR_LIST_SIZE);
                $catePostInfo = [
                    'posts' => $sourcePosts,
                    'loadMoreToken' => $this->encrypt($cryptUtils, $lastInfo)
                ];
            } else {
                $catePostInfo = [
                    'posts' => [],
                    'loadMoreToken' => null
                ];
            }

            $service_cache->set($articleKeyCache, $catePostInfo, $this->getParameter('cache_time')['hour']);
        }

        // Build Seo
        if (!empty($parentSlug)) {
            if (!empty($rootSlug)) {
                $cateUrl = $this->generateUrl('news_sub_cate_level2', ['rootSlug' => $rootSlug, 'parentSlug' => $parentSlug, 'cateSlug' => $cateSlug]);
                $rssUrl = $this->generateUrl('rss_sub_cate_level2', ['rootSlug' => $rootSlug, 'parentSlug' => $parentSlug, 'cateSlug' => $cateSlug]);
            } else {
                $cateUrl = $this->generateUrl('news_sub_cate', ['parentSlug' => $parentSlug, 'cateSlug' => $cateSlug]);
                $rssUrl = $this->generateUrl('rss_sub_cate', ['parentSlug' => $parentSlug, 'cateSlug' => $cateSlug]);
            }
        } else {
            $cateUrl = $this->generateUrl('news_cate', ['cateSlug' => $cateSlug]);
            $rssUrl = $this->generateUrl('rss_category', ['cateSlug' => $cateSlug]);
        }
        $seo = $this->buildPagingMeta($cateUrl, $cate['name'], 1, 1, $this->getParameter('site_name'));
        // Latest news on Nanoflowcell - Indianautosblog - car and bike news, reviews, new and upcoming launches.
        $description = $cate['description'] ? Lib::subString($cate['description'], 300) : sprintf(Constants::BUILD_FORM_SEO_META_DES_CATE, $cate['name']);
        $seo = array_merge($seo, array(
            'og_type' => 'object',
            'is_home' => false,
            'description' => $description,
            'amp' => $this->getParameter('mobile') . $cateUrl
        ));
        // RSS
        $rss = array(
            'title' => $cate['name'] . ' Category Feed',
            'url' => $rssUrl
        );

        // build view
        $response = $this->render('category/index.html.twig', array(
            'loadMoreToken' => $catePostInfo['loadMoreToken'],
            'news_list' => $catePostInfo['posts'],
            'cate_name' => $cate['name'],
            'cate_id' => $cate['cateId'],
            'url_ajax' => $this->generateUrl('news_ajax_cate'),
            'array_child' => $array_child,
            'type' => 0,
            'parentSlug' => $cateSlug,
            'seo' => $seo,
            'rss' => $rss
        ));
        // ThanhDT: Set cache page
        $this->addCachePage($request, $response);
        return $response;
    }


    /**
     * @param DataExchange $dataExchangeService
     * @param Category $cateService
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function ampCate(DataExchange $dataExchangeService, Category $cateService, Request $request)
    {
        // Process category info
        $rootSlug = $request->get('rootSlug');
        $parentSlug = strtolower($request->get('parentSlug'));
        $cateSlug = strtolower($request->get('cateSlug'));
        $cate = $cateService->getCateBySlug($cateSlug);
        $currentPage = $request->get('page');
        $currentPage = (is_numeric($currentPage) && $currentPage > 0) ? $currentPage : 1;

        if ($cate == null) {
            return $this->forward('App\Controller\DefaultController:_404page');
        }

        // Cate parent slug is invalid
        if ($cate['parentId'] != 0) {
            $parentCate = $cateService->getCateById($cate['parentId']);
            if ($parentCate != null) {
                if ($parentCate['parentId'] == 0) {
                    if ($parentCate['slug'] != $parentSlug) {
                        return $this->redirectToRoute('news_sub_cate_amp', ['parentSlug' => $parentCate['slug'], 'cateSlug' => $cateSlug], 301);
                        /*if ($currentPage == 1) {
                            return $this->redirectToRoute('news_sub_cate', ['parentSlug' => $parentCate['slug'], 'cateSlug' => $cateSlug], 301);
                        } else {
                            return $this->redirectToRoute('news_sub_cate_paging', ['parentSlug' => $parentCate['slug'], 'cateSlug' => $cateSlug, 'currentPage' => $currentPage], 301);
                        }*/
                    }
                } else {
                    $rootCate = $cateService->getCateById($parentCate['parentId']);
                    if ($rootCate != null && ($rootCate['slug'] != $rootSlug || $parentCate['slug'] != $parentSlug)) {
                        return $this->redirectToRoute('news_sub_cate_level2_amp', ['rootSlug' => $rootCate['slug'], 'parentSlug' => $parentCate['slug'], 'cateSlug' => $cateSlug], 301);
                    }
                }
            }
        }

        // list category child : name, id , slug , url
        $getCategoryChild = $cateService->getCategoryChild($cate['cateId']);
        $array_child = [];
        $limit_child = 0;
        if (count($getCategoryChild)) {
            foreach ($getCategoryChild as $key) {
                if ($limit_child == 7) {
                    break;
                }

                $array_child[$limit_child]['cateId'] = $key['cateId'];
                $array_child[$limit_child]['name'] = $key['name'];
                $array_child[$limit_child]['slug'] = $key['slug'];

                $url = $this->generateUrl(
                    'news_sub_cate',
                    array('parentSlug' => $cateSlug, 'cateSlug' => $key['slug'])
                );
                $array_child[$limit_child]['url'] = $url;
                $limit_child++;
            }
        }

        $cateId = $cate['cateId'];
        if ($cate['parentId'] == 0) {
            $cateIdList = $cateService->getCategoryParentId($cate['cateId']);
        } else {
            $cateIdList = $cateId;
        }

        // Get data Post in category
        $articleKeyCache = $this->formatCacheKeyAmp(Constants::CACHE_AMP_CATEGORY_PAGE, $cateId, $currentPage);
        $service_cache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $article_exchange = $service_cache->get($articleKeyCache);
        if ($article_exchange === false) {
            $em = $this->getDoctrine()->getManager();
            $data = $em->getRepository(PostPublishes::class)
                ->getArticleInCatePaging($cateIdList, $currentPage, Constants::PAGE_SIZE + 1);
            if ($data) {
                $article_exchange = $dataExchangeService->ExchangeArrayArticle(
                    $data,
                    Constants::MOBILE_IMAGE_HOME_LATEST_NEWS
                );
            } else {
                $article_exchange = [];
            }
            $service_cache->set($articleKeyCache, $article_exchange, $this->getParameter('cache_time')['hour']);
        }

        $lastestPostsCount = count($article_exchange);
        if ($lastestPostsCount > Constants::PAGE_SIZE) {
            $lastestPosts = array_slice($article_exchange, 0, $lastestPostsCount - 1);
            $flagCheckNextPage = true;
        } else {
            $lastestPosts = $article_exchange;
            $flagCheckNextPage = false;
        }
        $flagCheckPreviewPage = ($currentPage > 1) ? true : false;

        // Build Seo
        if (!empty($parentSlug)) {
            if (!empty($rootSlug)) {
                $cateUrl = $this->generateUrl(
                    'news_sub_cate_level2',
                    ['rootSlug' => $rootSlug, 'parentSlug' => $parentSlug, 'cateSlug' => $cateSlug]
                );
            } else {
                $cateUrl = $this->generateUrl('news_sub_cate', ['parentSlug' => $parentSlug, 'cateSlug' => $cateSlug]);
            }
        } else {
            $cateUrl = $this->generateUrl('news_cate', ['cateSlug' => $cateSlug]);
        }
        $seo = $this->buildPagingMeta($cateUrl, $cate['name'], 1, 1, $this->getParameter('site_name'));
        $seo = array_merge($seo, array(
            'author' => 'unknown',
            'title' => $cate['name'],
            'og_type' => 'object',
            'is_home' => false,
            'description' => $this->getParameter('site_desc'),
            'image' => '',
            'publish_time' => '',
            'amp' => $this->getParameter('mobile') . $cateUrl
        ));

        $linkNextPage = $cateUrl . "/amp?page=" . ($currentPage + 1);
        $linkPrevPage = $cateUrl . "/amp?page=" . ($currentPage - 1);

        $response = $this->render('category/amp/index.html.twig', array(
            'flagCheckNextPage' => $flagCheckNextPage,
            'flagCheckPreviewPage' => $flagCheckPreviewPage,
            'news_list' => $lastestPosts,
            'cate_name' => $cate['name'],
            'cate_id' => $cate['cateId'],
            'type' => 0,
            'parentSlug' => $cateSlug,
            'seo' => $seo,
            'linkNextPage' => $linkNextPage,
            'linkPrevPage' => $linkPrevPage
        ));
        // ThanhDT: Set cache page
        $this->addCachePage($request, $response);
        return $response;
    }
}
