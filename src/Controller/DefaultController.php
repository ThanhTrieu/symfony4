<?php

namespace App\Controller;

use App\Entity\Pages;
use App\Entity\PostPublishes;
use App\Entity\Videos;
use App\Service\Category;
use App\Service\CryptUtils;
use App\Service\DataExchange;
use App\Utils\Constants;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use App\Utils\Lib;

class DefaultController extends BaseController
{
    /**
     * Home page action
     * author: ThanhDT
     * date:   2018-10-17 09:23 PM
     * @param DataExchange $exchangeService
     * @param Category $category
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function index(DataExchange $exchangeService, Category $category, Request $request, CryptUtils $cryptUtils)
    {
        $postId = $request->query->get('p', null);
        $response = $this->shortPostLink($postId);
        if ($response) {
            return $response;
        }
        // Get top focus posts
        $cacheService = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $focusCacheKey = Constants::CACHE_HOMEPAGE_FOCUS;
        if (($focusPosts = $cacheService->get($focusCacheKey)) === false) {
            $em = $this->getDoctrine()->getManager();
            $sourcePosts = $em->getRepository(PostPublishes::class)
                ->getTopFocusPosts(Constants::FOCUS_HOME, Constants::HOMEPAGE_FOCUS_POST_LIMIT);
            if ($sourcePosts) {
                $dataTopFocus = array_column($sourcePosts, 'postId');
                $focusTopCenter = $focusTopLeft = $focusTopRight = [];
                $focusCount = count($sourcePosts);
                for ($i = 0; $i < $focusCount; $i++) {
                    // First 5 items in center
                    if ($i < Constants::HOME_FOCUS_POST_CENTER_COUNT) {
                        $focusTopCenter[] = $sourcePosts[$i];
                    } elseif ($i < Constants::HOME_FOCUS_POST_CENTER_COUNT + 2) { // Next 2 items in left column
                        $focusTopLeft[] = $sourcePosts[$i];
                    } else { // Last 2 items in right column
                        $focusTopRight[] = $sourcePosts[$i];
                    }
                }
                //$exchangeService = $this->get(DataExchange::class);
                $focusTopCenter = $exchangeService->exchangeArrayArticle(
                    $focusTopCenter,
                    Constants::POST_AVATAR_FOCUS_TOP1_SIZE
                );
                $focusTopLeft = $exchangeService->exchangeArrayArticle(
                    $focusTopLeft,
                    Constants::POST_AVATAR_FOCUS_TOP2_SIZE
                );
                $focusTopRight = $exchangeService->exchangeArrayArticle(
                    $focusTopRight,
                    Constants::POST_AVATAR_FOCUS_TOP2_SIZE
                );
                $focusPosts = [
                    'focusTopCenter' => $focusTopCenter,
                    'focusTopLeft' => $focusTopLeft,
                    'focusTopRight' => $focusTopRight,
                    'focusPostIds' => $dataTopFocus,
                ];
                $cacheService->set($focusCacheKey, $focusPosts, $this->getParameter('cache_time')['hour']);
            } else {
                $focusPosts = [
                    'focusTopCenter' => [],
                    'focusTopLeft' => [],
                    'focusTopRight' => [],
                    'focusPostIds' => [],
                ];
            }
        }

        // Get page 1 lastest posts
        $currentPage = 1;
        $ignoreTopFocusPostIds = $focusPosts ? $focusPosts['focusPostIds'] : [];
        $lastestCacheKey = sprintf(Constants::CACHE_HOMEPAGE_LASTEST_PAGE, Constants::HOMEPAGE_LASTEST_PAGE_INDEX);
        if (($lastestPostInfo = $cacheService->get($lastestCacheKey)) === false) {
            $em = $this->getDoctrine()->getManager();
            $sourcePosts = $em->getRepository(PostPublishes::class)
                ->getLastestPosts(Constants::HOMEPAGE_LASTEST_LIMIT + 1, time(), $ignoreTopFocusPostIds);
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
                $sourcePosts = $exchangeService->ExchangeArrayArticle($sourcePosts, Constants::IMAGE_DETAIL_NEWS);
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
            $cacheService->set($lastestCacheKey, $lastestPostInfo, $this->getParameter('cache_time')['hour']);
        }

        // Get MostView
        $mostViewCacheKey = Constants::CACHE_HOME_MOST_VIEW;
        if (($mostViewPosts = $cacheService->get($mostViewCacheKey)) === false) {
            $em = $this->getDoctrine()->getManager();
            $sourcePosts = $em->getRepository(PostPublishes::class)
                ->getMostViewPosts(Constants::HOME_MOST_VIEW_POST_LIMIT, Constants::HOME_MOST_VIEW_LAST_DAY);
            if ($sourcePosts) {
                $countDataPosts = count($sourcePosts);
                if ($countDataPosts < Constants::HOME_MOST_VIEW_POST_LIMIT) {
                    $rowRequest = (int)Constants::HOME_MOST_VIEW_POST_LIMIT - $countDataPosts;
                    $overSourcePosts = $em->getRepository(PostPublishes::class)
                        ->getMostViewPosts($rowRequest, Constants::HOME_MOST_VIEW_LAST_DAY_2);
                    $viewPosts = array_merge($sourcePosts, $overSourcePosts);
                    $mostViewPosts = $exchangeService->exchangeArrayArticle(
                        $viewPosts,
                        Constants::IMAGE_MOST_VIEW_SIZE
                    );
                } else {
                    $mostViewPosts = $exchangeService->exchangeArrayArticle(
                        $sourcePosts,
                        Constants::IMAGE_MOST_VIEW_SIZE
                    );
                }
                $cacheService->set($mostViewCacheKey, $mostViewPosts, $this->getParameter('cache_time')['hour']);
            } else {
                $sourcePosts = $em->getRepository(PostPublishes::class)
                    ->getMostViewPosts(Constants::HOME_MOST_VIEW_POST_LIMIT, Constants::HOME_MOST_VIEW_LAST_DAY_2);
                $mostViewPosts = $exchangeService->exchangeArrayArticle($sourcePosts, Constants::IMAGE_MOST_VIEW_SIZE);
                $cacheService->set($mostViewCacheKey, $mostViewPosts, $this->getParameter('cache_time')['hour']);
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
            Constants::POST_AVATAR_LIST_SIZE
        );
        // Box Cars review
        $carsReviewCate = $category->getCateById(Constants::CARS_REVIEW_CATE_ID);
        $carsReviewCate['url'] = $this->generateUrl('news_cate', ['cateSlug' => $carsReviewCate['slug']]);
        $carsReviewPosts = self::getPostInCate(
            $cacheService,
            $exchangeService,
            Constants::CARS_REVIEW_CATE_ID,
            Constants::HOME_OTHER_CATE_POSTS_LIMIT,
            Constants::POST_AVATAR_HOME_CATE_SIZE,
            Constants::POST_AVATAR_HOME_CATE_FOCUS_SIZE
        );
        $firstCarsReviewPosts = array_shift($carsReviewPosts);

        // Box Bikes review
        $bikesReviewCate = $category->getCateById(Constants::BIKES_REVIEW_CATE_ID);
        $bikesReviewCate['url'] = $this->generateUrl('news_cate', ['cateSlug' => $bikesReviewCate['slug']]);
        $bikesReviewPosts = self::getPostInCate(
            $cacheService,
            $exchangeService,
            Constants::BIKES_REVIEW_CATE_ID,
            Constants::HOME_OTHER_CATE_POSTS_LIMIT,
            Constants::POST_AVATAR_HOME_CATE_SIZE,
            Constants::POST_AVATAR_HOME_CATE_FOCUS_SIZE
        );
        $firstBikeReviewPosts = array_shift($bikesReviewPosts);

        // TrieuNT added - Box Videos
        $videosViewCacheKey = Constants::CACHE_HOME_VIDEOS_VIEW;
        if (($videosViewPosts = $cacheService->get($videosViewCacheKey)) === false) {
            $em = $this->getDoctrine()->getManager();
            $sourceVideos = $em->getRepository(Videos::class)
                ->getMostViewVideos(
                    Constants::START_PAGE,
                    Constants::HOME_OTHER_CATE_POSTS_LIMIT
                );
            if ($sourceVideos) {
                $mostViewVideos = $exchangeService->exchangeArrayVideosGallery(
                    $sourceVideos,
                    Constants::POST_AVATAR_HOME_CATE_SIZE
                );
                $firstVideo = ($mostViewVideos) ? array_shift($mostViewVideos) : [];
                $videosViewPosts['mostViewVideos'] = $mostViewVideos;
                $videosViewPosts['firstVideo'] = $firstVideo;
            } else {
                $videosViewPosts['mostViewVideos'] = [];
                $videosViewPosts['firstVideo'] = [];
            }
            $cacheService->set($videosViewCacheKey, $videosViewPosts, $this->getParameter('cache_time')['hour']);
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

        $response = $this->render('default/index.html.twig', [
            'focusPosts' => $focusPosts,
            'lastestPosts' => $lastestPostInfo['lastestPosts'],
            'loadMoreToken' => $lastestPostInfo['loadMoreToken'],
            'mostViewPosts' => $mostViewPosts,
            'featuredStoriesPosts' => $featuredStoriesPosts,
            'carsReviewPosts' => $carsReviewPosts,
            'firstCarsReviewPosts' => $firstCarsReviewPosts,
            'bikesReviewPosts' => $bikesReviewPosts,
            'firstBikeReviewPosts' => $firstBikeReviewPosts,
            'featuredStoriesCate' => $featuredStoriesCate,
            'carsReviewCate' => $carsReviewCate,
            'bikesReviewCate' => $bikesReviewCate,
            'parentSlug' => 'home',
            'mostViewVideos' => $videosViewPosts['mostViewVideos'],
            'firstVideo' => $videosViewPosts['firstVideo'],
            'seo' => $seo,
        ]);
        // ThanhDT: Set cache page
        $this->addCachePage($request, $response);

        return $response;
    }

    /**
     * Custom short link for post
     * @param $postId
     * @return bool|RedirectResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */

    public function shortPostLink($postId)
    {
        if ($postId != null) {
            $em = $this->getDoctrine()->getManager();
            // Redirect to published post
            $service_cache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
            $keyDetail = sprintf(Constants::TABLE_ARTICLE_DETAIL_URL, $postId);
            if (($detailUrl = $service_cache->get($keyDetail)) === false) {
                $data = $em->getRepository(PostPublishes::class)->getDetail($postId);
                if ($data) {
                    $detailUrl = $this->generateUrl(
                        'news_detail',
                        ['slug' => $data['slug'], 'postId' => $data['postId']]
                    );
                    $service_cache->set($keyDetail, $detailUrl, $this->getParameter('cache_time')['hour']);
                }
            }
            if ($detailUrl) {
                return new RedirectResponse($detailUrl, 301);
            }
        }
        return false;
    }

    /**
     * Get top posts in category
     * author: ThanhDT
     * date:   2018-10-19 05:22 PM
     * @param $cacheService
     * @param DataExchange $exchangeService
     * @param $cateId
     * @param $limit
     * @param $imageSize
     * @param null $specialSize
     * @param $ignoreLastestIds
     * @return mixed
     * @throws \Exception
     */
    private function getPostInCate(
        $cacheService,
        DataExchange $exchangeService,
        $cateId,
        $limit,
        $imageSize = null,
        $specialSize = null
    ) {
        $cateCacheKey = sprintf(Constants::CACHE_HOMEPAGE_CATE_LIST, $cateId);
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
     * about page
     * author: TrieuNT
     * create date: 2018-11-14 03:17 PM
     * @return \Symfony\Component\HttpFoundation\Response
     *
     */

    public function about()
    {
        $url = $this->generateUrl('about');
        $seo = $this->buildPagingMeta($url, Constants::SEO_TITLE_ABOUT, 1, 1, $this->getParameter('site_name'));
        $seo = array_merge($seo, array(
            'og_type' => 'object',
            'is_home' => false
        ));
        return $this->render('default/about.html.twig', ['seo' => $seo]);
    }

    /**
     * privacy-policy page
     * author: TrieuNT
     * create date: 2018-11-14 03:26 PM
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */

    public function privacyPolicy()
    {
        $slug = Constants::SLUG_PRIVACY_POLICY;
        $serviceCache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $cateCacheKey = sprintf(Constants::CACHE_HOMEPAGE_PRIVACY_POLICY);
        if (($dataPages = $serviceCache->get($cateCacheKey)) === false) {
            $em = $this->getDoctrine()->getManager();
            $sourcePages = $em->getRepository(Pages::class)->getDetailPage($slug);
            if ($sourcePages) {
                $dataPages = $sourcePages;
                $serviceCache->set($cateCacheKey, $dataPages, $this->getParameter('cache_time')['hour']);
            } else {
                $dataPages = [];
            }
        }

        $url = $this->generateUrl('privacy_policy');
        $seo = $this->buildPagingMeta(
            $url,
            Constants::SEO_TITLE_PRIVACY_POLICY,
            1,
            1,
            $this->getParameter('site_name')
        );
        $seo = array_merge($seo, array(
            'og_type' => 'object',
            'is_home' => false
        ));
        return $this->render('default/privacy-policy.html.twig', [
            'seo' => $seo,
            'sourcePages' => $dataPages
        ]);
    }

    /**
     * contact page
     * author: TrieuNT
     * create date: 2018-11-14 03:29 PM
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contact(Request $request)
    {
        $url = $this->generateUrl('contact');
        $seo = $this->buildPagingMeta($url, Constants::SEO_TITLE_CONTACT, 1, 1, $this->getParameter('site_name'));
        $seo = array_merge($seo, array(
            'og_type' => 'object',
            'is_home' => false
        ));

        $message = $request->get('state');
        return $this->render('default/contact.html.twig', ['message' => $message, 'seo' => $seo]);
    }

    /**
     * contactSendmail
     * author: TrieuNT
     * create date: 2018-11-14 04:30 PM
     * @param Request $request
     * @param PHPMailer $mail
     * @return mixed
     */
    public function contactSendmail(Request $request)
    {
        if ($request->getMethod() === 'POST') {
            $fullName = $request->get('txtName');
            $fullName = filter_var($fullName, FILTER_SANITIZE_STRING);

            $phoneNumber = $request->get('txtPhone');
            $phoneNumber = filter_var($phoneNumber, FILTER_SANITIZE_STRING);

            $email = $request->get('txtEmail');
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);

            $questions = $request->get('tctQuestion');
            $questions = filter_var($questions, FILTER_SANITIZE_STRING);

            $message = $this->renderView(
                'default/contact-sendmail.html.twig',
                [
                    'name' => $fullName,
                    'phone' => $phoneNumber,
                    'email' => $email,
                    'questions' => $questions
                ]
            );

            $mail = new PHPMailer(true);
            try {
                //Server settings
                //$mail->SMTPDebug = 2;
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'noreply@indianautosblog.com';
                $mail->Password = 'ADFKw3k23o4i2432@#$%';
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;

                //Recipients
                $mail->setFrom('noreply@indianautosblog.com', 'IndianAutosBlog');
                $mail->addAddress(Constants::EMAIL_SUPPORT_CUSTOMER);

                //Content
                $mail->isHTML(true);
                $mail->MsgHTML($message);
                $mail->Subject = 'The questions of customer from indianautosblog.com';
                $mail->CharSet = 'utf-8';

                $mail->send();
                return $this->redirectToRoute('contact', ['state' => 'success']);
            } catch (\Exception $e) {
                var_dump(' error setting ' . $mail->ErrorInfo);
            }
        }
    }

    public function _404page()
    {
        $seo = array(
            'title' => 'Page not found - ' . $this->getParameter('site_name'),
            'og_type' => 'object',
            'is_home' => false
        );
        $response = $this->render('404.html.twig', array('seo' => $seo));
        $response->setStatusCode(404);
        return $response;
    }

    /**
     * Show exception 404 page
     * author: ThanhDT
     * date:   2018-12-19 04:56 PM
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showException()
    {
        $seo = array(
            'title' => 'Page not found - ' . $this->getParameter('site_name'),
            'og_type' => 'object',
            'is_home' => false
        );
        $response = $this->render('404.html.twig', array('seo' => $seo));
        $response->setStatusCode(404);
        return $response;
    }
}
