<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 12/26/2018
 * Time: 3:11 PM
 */

namespace App\Controller;

use App\Entity\Pages;
use App\Entity\PostPublishes;
use App\Entity\Videos;
use App\Service\Category;
use App\Service\DataExchange;
use App\Utils\Constants;
use App\Utils\Lib;
use App\Service\CryptUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class AmpController extends BaseController
{
    const MOBILE_TYPE_VIDEOS_HOME = 1;

    /******************************* AMP - version mobile ************************************************************/
    /**
     * @param DataExchange $exchangeService
     * @param Category $category
     * @param Request $request
     * @param CryptUtils $cryptUtils
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function homeAmp(
        DataExchange $exchangeService,
        Category $category,
        Request $request,
        CryptUtils $cryptUtils,
        Session $session
    ) {
        // Get top focus posts
        $cacheService = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $focusCacheKey = $this->formatCacheKeyAmp(Constants::CACHE_HOMEPAGE_FOCUS);
        $dataTopFocus = [];
        if (($focusPosts = $cacheService->get($focusCacheKey)) === false) {
            $em = $this->getDoctrine()->getManager();
            $sourcePosts = $em->getRepository(PostPublishes::class)
                ->getTopFocusPosts(Constants::FOCUS_HOME, Constants::HOMEPAGE_FOCUS_POST_LIMIT);
            $dataTopFocus = $sourcePosts;
            if ($sourcePosts) {
                $focusPosts = $exchangeService->exchangeSitemapArrayArticle(
                    $sourcePosts,
                    Constants::MOBILE_IMAGE_HOME_TOPIC
                );
                $cacheService->set($focusCacheKey, $focusPosts, $this->getParameter('cache_time')['hour']);
            } else {
                $focusPosts = [];
            }
        }

        // get token
        $tokenPage = $request->query->get('token');
        $pagePanigate = $request->query->get('page');
        $typePage = $request->query->get('type');
        $pagePanigate = is_numeric($pagePanigate) ? $pagePanigate : 1;
        $urlNextPage = null;
        $urlPrevPage = null;
        $checkPrevPage = null;
        $checkNextPage = null;

        if ($tokenPage === null && $typePage === null) {
            // Get page 1 lastest posts
            $currentPage = $pagePanigate;
            $checkPrevPage = false;
            $lastestCacheKey = $this->formatCacheKeyAmp(
                Constants::CACHE_HOMEPAGE_LASTEST_PAGE,
                Constants::HOMEPAGE_LASTEST_PAGE_INDEX
            );
            if (($lastestPostInfo = $cacheService->get($lastestCacheKey)) === false) {
                $ignoreTopFocusPostIds = [];
                if ($dataTopFocus) {
                    foreach ($dataTopFocus as $key => $item) {
                        $ignoreTopFocusPostIds[] = $item['postId'];
                    }
                }
                $em = $this->getDoctrine()->getManager();
                $sourcePosts = $em->getRepository(PostPublishes::class)
                    ->getLastestPostsAmp(
                        Constants::HOMEPAGE_LASTEST_LIMIT + 1,
                        time(),
                        $pagePanigate,
                        $ignoreTopFocusPostIds
                    );
                if ($sourcePosts) {
                    if (count($sourcePosts) > Constants::HOMEPAGE_LASTEST_LIMIT) {
                        $sourcePosts = array_slice($sourcePosts, 0, Constants::HOMEPAGE_LASTEST_LIMIT);
                        $lastHomeStream = $sourcePosts[Constants::HOMEPAGE_LASTEST_LIMIT - 1];
                        $lastInfo = [
                            'lastPostId' => $lastHomeStream['postId'],
                            'lastPublishedTimestamp' => $lastHomeStream['publishedTimestamp'],
                            'nextPage' => $currentPage + 1
                        ];
                    } else {
                        $lastInfo = null;
                    }
                    $sourcePosts = $exchangeService->ExchangeArrayArticle(
                        $sourcePosts,
                        Constants::MOBILE_IMAGE_HOME_LATEST_NEWS
                    );
                    $lastestPostInfo = [
                        'lastestPosts' => $sourcePosts,
                        'loadMoreToken' => $this->encrypt($cryptUtils, $lastInfo)
                    ];
                } else {
                    $lastestPostInfo = [
                        'lastestPosts' => [],
                        'loadMoreToken' => null
                    ];
                }
                $checkNextPage = true;
                $urlNextPage = $this->generateUrl(
                    'home_amp',
                    ['page' => $pagePanigate+1,'token' => $lastestPostInfo['loadMoreToken'],'type'=>'next']
                );

                $cacheService->set($lastestCacheKey, $lastestPostInfo, $this->getParameter('cache_time')['hour']);
            }
        } else {
            $lastestPostInfo = $this->getLastestPostsPage($exchangeService, $cryptUtils, $tokenPage);
            $checkPrevPage = ($pagePanigate > 1 && $lastestPostInfo['loadMoreToken']['first']) ? true : false;
            $checkNextPage = ($lastestPostInfo['loadMoreToken']['lastest']) ? true : false;

            $urlNextPage  = $this->generateUrl(
                'home_amp',
                ['page' => $pagePanigate+1,'token' => $lastestPostInfo['loadMoreToken']['lastest'],'type'=>'next']
            );
            $urlPrevPage = $this->generateUrl(
                'home_amp',
                ['page' => $pagePanigate-1,'token' => $lastestPostInfo['loadMoreToken']['first'],'type'=>'previous']
            );
        }

        // Get MostView
        $mostViewCacheKey = $this->formatCacheKeyAmp(Constants::CACHE_HOME_MOST_VIEW);
        if (($mostViewPosts = $cacheService->get($mostViewCacheKey)) === false) {
            $em = $this->getDoctrine()->getManager();
            $sourcePosts = $em->getRepository(PostPublishes::class)
                ->getMostViewPosts(Constants::HOME_MOST_VIEW_POST_LIMIT, Constants::HOME_MOST_VIEW_LAST_DAY);
            if ($sourcePosts) {
                $mostViewPosts = $exchangeService->exchangeArrayArticle($sourcePosts);
                $cacheService->set($mostViewCacheKey, $mostViewPosts, $this->getParameter('cache_time')['hour']);
            } else {
                $mostViewPosts = [];
            }
        }

        // Box Featured stories
        $featuredStoriesCate = $category->getCateById(Constants::FEATURED_STORIES_CATE_ID);
        $featuredStoriesCate['url'] = $this->generateUrl('news_cate', ['cateSlug' => $featuredStoriesCate['slug']]);
        $featuredStoriesPosts = self::getPostInCate(
            $cacheService,
            $exchangeService,
            Constants::FEATURED_STORIES_CATE_ID,
            Constants::HOME_FEATURED_POSTS_LIMIT,
            Constants::MOBILE_IMAGE_DETAIL_NEWS_FEATURED
        );
        // Box Cars review
        $carsReviewCate = $category->getCateById(Constants::CARS_REVIEW_CATE_ID);
        $carsReviewCate['url'] = $this->generateUrl('news_cate', ['cateSlug' => $carsReviewCate['slug']]);
        $carsReviewPosts = self::getPostInCate(
            $cacheService,
            $exchangeService,
            Constants::CARS_REVIEW_CATE_ID,
            Constants::MOBILE_HOME_OTHER_CATE_POSTS_LIMIT,
            Constants::MOBILE_IMAGE_HOME_FEATURED_STORIES,
            Constants::MOBILE_IMAGE_HOME_LATEST_NEWS
        );

        // Box Bikes review
        $bikesReviewCate = $category->getCateById(Constants::BIKES_REVIEW_CATE_ID);
        $bikesReviewCate['url'] = $this->generateUrl('news_cate', ['cateSlug' => $bikesReviewCate['slug']]);
        $bikesReviewPosts = self::getPostInCate(
            $cacheService,
            $exchangeService,
            Constants::BIKES_REVIEW_CATE_ID,
            Constants::MOBILE_HOME_OTHER_CATE_POSTS_LIMIT,
            Constants::MOBILE_IMAGE_HOME_FEATURED_STORIES,
            Constants::MOBILE_IMAGE_HOME_LATEST_NEWS
        );

        // TrieuNT added - Box Videos
        $videosViewCacheKey = $this->formatCacheKeyAmp(Constants::CACHE_HOME_VIDEOS_VIEW);
        $dataVideos = [];
        if (($videosViewPosts = $cacheService->get($videosViewCacheKey)) === false) {
            $em = $this->getDoctrine()->getManager();
            $sourceVideos = $em->getRepository(Videos::class)
                ->getMostViewVideos(Constants::START_PAGE, Constants::HOME_OTHER_CATE_POSTS_LIMIT);
            if ($sourceVideos) {
                $mostViewVideos = $exchangeService->exchangeArrayVideosGallery(
                    $sourceVideos,
                    Constants::POST_AVATAR_HOME_CATE_SIZE,
                    self::MOBILE_TYPE_VIDEOS_HOME
                );
                $dataVideos['mostViewVideos'] = $mostViewVideos;
            } else {
                $dataVideos['mostViewVideos'] = [];
            }
            $cacheService->set($videosViewCacheKey, $dataVideos, $this->getParameter('cache_time')['hour']);
        } else {
            $dataVideos['mostViewVideos'] = [];
        }

        // Build Seo
        $indexUrl = $this->generateUrl('index');
        $seo = $this->buildPagingMeta(
            $indexUrl,
            $this->getParameter('home_title'),
            1,
            1,
            $this->getParameter('site_name')
        );
        $seo = array_merge($seo, array(
            'og_type' => 'object',
            'is_home' => true,
            'description' => $this->getParameter('site_desc'),
            'amp' => $this->getParameter('mobile')
        ));

        $response = $this->render('default/index-amp.html.twig', [
            'focusPosts' => $focusPosts,
            'lastestPosts' =>  $lastestPostInfo['lastestPosts'],
            'loadMoreToken' => $lastestPostInfo['loadMoreToken'],
            'mostViewPosts' => $mostViewPosts,
            'featuredStoriesPosts' => $featuredStoriesPosts,
            'carsReviewPosts' => $carsReviewPosts,
            'bikesReviewPosts' => $bikesReviewPosts,
            'featuredStoriesCate' => $featuredStoriesCate,
            'carsReviewCate' => $carsReviewCate,
            'bikesReviewCate' => $bikesReviewCate,
            'parentSlug' => 'home',
            'mostViewVideos' => $dataVideos['mostViewVideos'],
            'seo'=>$seo,
            'urlNextPage' => $urlNextPage,
            'urlPrevPage' => $urlPrevPage,
            'checkPrevPage' => $checkPrevPage,
            'checkNextPage' => $checkNextPage

        ]);
        // ThanhDT: Set cache page
        $this->addCachePage($request, $response);

        return $response;
    }


    /**
     * Get top posts in category
     * @param $cacheService
     * @param DataExchange $exchangeService
     * @param $cateId
     * @param $limit
     * @param null $imageSize
     * @param null $specialSize
     * @return array
     * @throws \Exception
     */
    private function getPostInCate(
        $cacheService,
        DataExchange $exchangeService,
        $cateId,
        $limit,
        $imageSize = null,
        $specialSize = null
    ){
        $cateCacheKey = $this->formatCacheKeyAmp(Constants::CACHE_HOMEPAGE_CATE_LIST, $cateId);
        if (($catePosts = $cacheService->get($cateCacheKey)) === false) {
            $em = $this->getDoctrine()->getManager();
            $sourcePosts = $em->getRepository(PostPublishes::class)->getTopPostsInCate($cateId, $limit);
            if ($sourcePosts) {
                $catePosts = $exchangeService->exchangeArrayArticle($sourcePosts, $imageSize, $specialSize);
                $cacheService->set($cateCacheKey, $catePosts, $this->getParameter('cache_time')['hour']);
            }
        }
        return $catePosts;
    }

    /**
     * @param null $token
     * @param DataExchange $exchangeService
     * @param CryptUtils $cryptUtils
     * @return array
     * @throws \Exception
     */
    private function getLastestPostsPage(
        $exchangeService,
        $cryptUtils,
        $token = null
    ) {
        if (!$token) {
            return [
                'loadMoreToken' => null,
                'data' =>  []
            ];
        }
        $curLastInfo = $this->decrypt($cryptUtils, $token);
        if (!$curLastInfo) {
            return [
                'loadMoreToken' => null,
                'data' =>  []
            ];
        }

        $postId = $curLastInfo['lastPostId'];
        $timestamp = $curLastInfo['lastPublishedTimestamp'];
        $nextPage = $curLastInfo['nextPage'];
        $cacheService = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        // Get page 1 lastest posts
        $lastestCacheKey = $this->formatCacheKeyAmp(Constants::CACHE_HOMEPAGE_LASTEST_TIMESTAMP, $postId, $timestamp);
        if (($lastestPostInfo = $cacheService->get($lastestCacheKey)) === false) {
            $em = $this->getDoctrine()->getManager();
            $sourcePosts = $em->getRepository(PostPublishes::class)
                ->getLastestPostsAmp(Constants::HOMEPAGE_LASTEST_LIMIT + 1, $timestamp, $nextPage, [$postId]);
            if ($sourcePosts) {
                if (count($sourcePosts) > Constants::HOMEPAGE_LASTEST_LIMIT) {
                    $firstTestStream = $sourcePosts[0];
                    $sourcePosts = array_slice($sourcePosts, 0, Constants::HOMEPAGE_LASTEST_LIMIT);
                    $lastestStream = $sourcePosts[Constants::HOMEPAGE_LASTEST_LIMIT - 1];
                    $lastInfo = [
                        'lastPostId' => $lastestStream['postId'],
                        'lastPublishedTimestamp' => $lastestStream['publishedTimestamp'],
                        'nextPage' => $nextPage + 1
                    ];
                    $firstInfo = [
                        'lastPostId' => $firstTestStream['postId'],
                        'lastPublishedTimestamp' => $firstTestStream['publishedTimestamp'],
                        'nextPage' => ($nextPage - 1)
                    ];
                    $lastInfo = $this->encrypt($cryptUtils, $lastInfo);
                    $firstInfo = $this->encrypt($cryptUtils, $firstInfo);
                } else {
                    $lastInfo = null;
                }
                $sourcePosts = $exchangeService->exchangeArrayArticle(
                    $sourcePosts,
                    Constants::MOBILE_IMAGE_HOME_LATEST_NEWS
                );

                $lastestPostInfo = [
                    'lastestPosts' => $sourcePosts,
                    'loadMoreToken' => [
                        'first' => $firstInfo,
                        'lastest' => $lastInfo
                    ]
                ];
            } else {
                $lastestPostInfo = [
                    'lastestPosts' => [],
                    'loadMoreToken' => null
                ];
            }
            $cacheService->set($lastestCacheKey, $lastestPostInfo, $this->getParameter('cache_time')['hour']);
        }

        $data = [
            'loadMoreToken' => $lastestPostInfo['loadMoreToken'],
            'lastestPosts' =>  $lastestPostInfo['lastestPosts']
        ];

        return $data;
    }
}