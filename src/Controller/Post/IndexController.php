<?php
/**
 * Created by PhpStorm.
 * User: ThanhDT
 * Date: 5/17/2018
 * Time: 4:02 PM
 */

namespace App\Controller\Post;

use App\Controller\BaseController;
use App\Service\Category;
use App\Entity\PostPublishes;
use App\Entity\PhotoGalleries;
use App\Entity\PhotoGalleriesImages;
use App\Service\DataExchange;
use App\Entity\Pages;
use App\Entity\Posts;
use App\Service\CryptUtils;
use App\Utils\Constants;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\Elasticsearch;

class IndexController extends BaseController
{
    /**
     * News detail action
     * author: TrieuNT
     * create date: 2018-10-18 10:18 AM
     * update time:  2019-03-16 10:20 AM
     * @param $slug
     * @param $postId
     * @param Request $request
     * @param DataExchange $dataExchangeService
     * @param Elasticsearch $elasticSearch
     * @param Category $cateService
     * @param $router
     * @return string
     * @throws \Exception
     */

    public function detail(
        $slug,
        $postId,
        Request $request,
        DataExchange $dataExchangeService,
        RouterInterface $router,
        Elasticsearch $elasticSearch,
        Category $cateService
    ) {
        $cacheService = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $keyDetail = sprintf(Constants::TABLE_ARTICLE_DETAIL_BY_ID, $postId);
        $listDataPhotos = [];
        $urlShareLink = null;
        $titleGallery = null;
        $detailUrl = null;
        if (($data = $cacheService->get($keyDetail)) === false) {
            $em = $this->getDoctrine()->getManager();
            $detail = $em->getRepository(PostPublishes::class)->getDetail($postId);
            if ($detail) {
                $data = $dataExchangeService->ExchangeArticleDetail($detail, Constants::IMAGE_SHARE_SIZE);
            } else {
                $data = [];
            }
            $cacheService->set($keyDetail, $data, $this->getParameter('cache_time')['hour']);
        }

        // Post not exist
        if ($data == null || $data == false) {
            $keyParams = $slug . '-' . $postId;
            return $this->changeToNotFoundPageBySlug($keyParams, $request, $elasticSearch, $dataExchangeService);
        }
        // Redirect 301 if slug is difference
        if ($slug != $data['slug']) {
            // Old Router redirect 301 to new router
            $url = $this->generateUrl('news_detail', ['slug' => $data['slug'], 'postId' => $postId]);
            $queryString = $request->getQueryString();
            if (!empty($queryString)) {
                $url = $url . '?' . $queryString;
            }
            return $this->redirect($url, 301);
        }

        // gallery
        $galleryPhoto = self::getDataGalleryByPostId($postId, $dataExchangeService);
        if ($galleryPhoto) {
            $titleGallery = $galleryPhoto['title'];
            $galleryId = isset($galleryPhoto['galleryId']) ? $galleryPhoto['galleryId'] : 0;
            $listDataPhotos = self::getDetailGalleryById($galleryId, $dataExchangeService);
        }

        $detailUrl = $this->generateUrl('news_detail', ['slug' => $slug, 'postId' => $postId]);
        $rssUrl = $this->generateUrl('rss_detail_news', ['slug' => $slug, 'postId' => $postId]);
        $urlShareLink = $this->getParameter('domain') . $detailUrl;

        // xu ly active tags by cate
        $cateIdByPost = (isset($data['cates'])) ? end($data['cates']) : null;
        $parentSlug = null;
        // get info cate
        if ($cateIdByPost) {
            $infoCate = $cateService->getCateById($cateIdByPost);
            if ($infoCate) {
                if ($infoCate['parentId'] != 0) {
                    $infoParentCate = $cateService->getCateById($infoCate['parentId']);
                    $parentSlug = (isset($infoParentCate['slug'])) ? $infoParentCate['slug'] : '';
                } else {
                    $parentSlug = (isset($infoCate['slug'])) ? $infoCate['slug'] : '';
                }
            }
        }

        // Get MostView
        $mostViewCacheKey = Constants::CACHE_HOME_MOST_VIEW;
        if (($mostViewPosts = $cacheService->get($mostViewCacheKey)) === false) {
            $em = $this->getDoctrine()->getManager();
            $sourcePosts = $em->getRepository(PostPublishes::class)
                ->getMostViewPosts(Constants::DETAIL_MOST_VIEW_POST_LIMIT, Constants::HOME_MOST_VIEW_LAST_DAY);
            if ($sourcePosts) {
                $countDataPosts = count($sourcePosts);
                if ($countDataPosts < Constants::DETAIL_MOST_VIEW_POST_LIMIT) {
                    $rowRequest = (int)Constants::DETAIL_MOST_VIEW_POST_LIMIT - $countDataPosts;
                    $overSourcePosts = $em->getRepository(PostPublishes::class)
                        ->getMostViewPosts($rowRequest, Constants::HOME_MOST_VIEW_LAST_DAY_2);
                    $viewPosts = array_merge($sourcePosts, $overSourcePosts);
                    $mostViewPosts = $dataExchangeService->exchangeArrayArticle(
                        $viewPosts,
                        Constants::IMAGE_MOST_VIEW_SIZE
                    );
                } else {
                    $mostViewPosts = $dataExchangeService->exchangeArrayArticle(
                        $sourcePosts,
                        Constants::IMAGE_MOST_VIEW_SIZE
                    );
                }
                $cacheService->set($mostViewCacheKey, $mostViewPosts, $this->getParameter('cache_time')['hour']);
            } else {
                $sourcePosts = $em->getRepository(PostPublishes::class)
                    ->getMostViewPosts(Constants::DETAIL_MOST_VIEW_POST_LIMIT, Constants::HOME_MOST_VIEW_LAST_DAY_2);
                $mostViewPosts = $dataExchangeService->exchangeArrayArticle(
                    $sourcePosts,
                    Constants::IMAGE_MOST_VIEW_SIZE
                );
                $cacheService->set($mostViewCacheKey, $mostViewPosts, $this->getParameter('cache_time')['hour']);
            }
        }

        // Get page 1 lastest posts
        $lastestCacheKey = Constants::CACHE_DETAIL_TOP_LASTEST;
        if (($lastestPosts = $cacheService->get($lastestCacheKey)) === false) {
            // add code
            //$ignoreCateIds  = [Constants::FEATURED_STORIES_CATE_ID,Constants::CARS_REVIEW_CATE_ID, Constants::BIKES_REVIEW_CATE_ID];
            $em = $this->getDoctrine()->getManager();
            $sourcePosts = $em->getRepository(PostPublishes::class)->getTopLastestPosts(Constants::DETAIL_LASTEST_NEWS);
            if ($sourcePosts) {
                $lastestPosts = $dataExchangeService->exchangeArrayArticle(
                    $sourcePosts,
                    Constants::IMAGE_MOST_VIEW_SIZE
                );
            } else {
                $lastestPosts = [];
            }
            $cacheService->set($lastestCacheKey, $lastestPosts, $this->getParameter('cache_time')['hour']);
        }
        /*// Post is incorrect url format
        if ($data['url'] != $request->getRequestUri()) {
            return $this->redirect($data['url'], 301);
        }*/

        $author = self::getAuthorById($data['authorId'], $dataExchangeService);
        if ($author) {
            $urlAuthor = $router->generate('news_author', array('authorSlug' => $author['slug']));
        } else {
            $urlAuthor = '';
        }
        // box update Tag
        $dataTagsPost = $this->relatedInTag(
            $data['tags'],
            $postId,
            $dataExchangeService
        );

        // box more from Cate
        $dataCatePost = $this->relatedInCate(
            $data['cates'],
            $postId,
            $dataTagsPost['strIgonreId'],
            $dataExchangeService
        );

        // box featuredStories
        $dataFeaturedSrories = $this->featuredStories(
            $postId,
            $dataCatePost['strIgonreId'],
            $dataExchangeService
        );
        $detailUrlContribute = $router->generate(
            'page_detail',
            ['pageSlug' => 'submit-spy-shots-and-get-bragging-rights',
                'page_id' => 29]
        );
        // RSS
        $rss = array(
            'title' =>  $slug . ' Feed',
            'url' => $rssUrl
        );

        $firstNameCate = null;
        $firstNameTag  = null;
        if ($data['tags']) {
            reset($data['tags']);
            $firstNameTag = key($data['tags']);
        }
        if ($data['cates']) {
            reset($data['cates']);
            $firstNameCate = key($data['cates']);
        }

        $response = $this->render('post/detail.html.twig', [
            'data' => $data,
            'author' => $author,
            'urlAuthor' => $urlAuthor,
            'seo' => $data['seo'],
            'postId' => $postId,
            'tagIdList' => $data['tags'],
            'cateIdList' => $data['cates'],
            'parentSlug' => $parentSlug,
            'titleGallery' => $titleGallery,
            'listDataPhotos' => $listDataPhotos,
            'detailUrl' => $detailUrl,
            'mostViewPosts' => $mostViewPosts,
            'lastestPosts' => $lastestPosts,
            'urlShareLink' => $urlShareLink,
            'detailUrlContribute' => $detailUrlContribute,
            'dataRelatedTag'  => $dataTagsPost['data'],
            'nameTag' => $firstNameTag,
            'dataRelatedCate'  => $dataCatePost['data'],
            'nameCate' => $firstNameCate,
            'dataRelatedFeatured' => $dataFeaturedSrories,
            'rss' => $rss
        ]);
        // ThanhDT: Set cache page
        $this->addCachePage($request, $response);

        return $response;
    }


    /**
     * @param Request $request
     * @param $postId
     * @param $crypt
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function preview(
        $postId,
        Request $request,
        CryptUtils $crypt,
        DataExchange $dataExchangeService,
        RouterInterface $router,
        Elasticsearch $elasticSearch,
        Category $cateService
    ) {
        $tokenKey = $request->get('token');
        $validateToken = $crypt->validateToken($tokenKey);

        if (!is_numeric($postId) || !$validateToken) {
            return $this->forward('App\Controller\DefaultController:_404page');
        }

        $cacheService = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $keyDetail = sprintf(Constants::TABLE_ARTICLE_PREVIEW_BY_ID, $postId);
        $listDataPhotos = [];
        $urlShareLink = null;
        $titleGallery = null;
        $detailUrl = null;
        if (($data = $cacheService->get($keyDetail)) === false) {
            $em = $this->getDoctrine()->getManager();
            $detail = $em->getRepository(Posts::class)->getDetail($postId);
            if ($detail) {
                $data = $dataExchangeService->ExchangeArticleDetail($detail, Constants::IMAGE_SHARE_SIZE);
                $cacheService->set($keyDetail, $data, $this->getParameter('cache_time')['hour']);
            }
        }

        // gallery
        $galleryPhoto = self::getDataGalleryByPostId($postId, $dataExchangeService);
        if ($galleryPhoto) {
            $titleGallery = $galleryPhoto['title'];
            $galleryId = isset($galleryPhoto['galleryId']) ? $galleryPhoto['galleryId'] : 0;
            $listDataPhotos = self::getDetailGalleryById($galleryId, $dataExchangeService);
        }
        $detailUrl = $this->generateUrl('news_detail', ['slug' => $data['slug'], 'postId' => $postId]);
        $urlShareLink = 'https://' . $request->getHttpHost() . $request->getBasePath() . $detailUrl;

        // xu ly active tags by cate
        $cateIdByPost = (isset($data['cates'])) ? end($data['cates']) : null;
        $parentSlug = null;
        // get info cate
        if ($cateIdByPost) {
            $infoCate = $cateService->getCateById($cateIdByPost);
            if ($infoCate) {
                if ($infoCate['parentId'] != 0) {
                    $infoParentCate = $cateService->getCateById($infoCate['parentId']);
                    $parentSlug = (isset($infoParentCate['slug'])) ? $infoParentCate['slug'] : '';
                } else {
                    $parentSlug = (isset($infoCate['slug'])) ? $infoCate['slug'] : '';
                }
            }
        }

        // Get MostView
        $mostViewCacheKey = Constants::CACHE_HOME_MOST_VIEW;
        if (($mostViewPosts = $cacheService->get($mostViewCacheKey)) === false) {
            $em = $this->getDoctrine()->getManager();
            $sourcePosts = $em->getRepository(PostPublishes::class)
                ->getMostViewPosts(Constants::DETAIL_MOST_VIEW_POST_LIMIT, Constants::HOME_MOST_VIEW_LAST_DAY);
            if ($sourcePosts) {
                $countDataPosts = count($sourcePosts);
                if ($countDataPosts < Constants::DETAIL_MOST_VIEW_POST_LIMIT) {
                    $rowRequest = (int)Constants::DETAIL_MOST_VIEW_POST_LIMIT - $countDataPosts;
                    $overSourcePosts = $em->getRepository(PostPublishes::class)
                        ->getMostViewPosts($rowRequest, Constants::HOME_MOST_VIEW_LAST_DAY_2);
                    $viewPosts = array_merge($sourcePosts, $overSourcePosts);
                    $mostViewPosts = $dataExchangeService->exchangeArrayArticle($viewPosts);
                } else {
                    $mostViewPosts = $dataExchangeService->exchangeArrayArticle($sourcePosts);
                }
                $cacheService->set($mostViewCacheKey, $mostViewPosts, $this->getParameter('cache_time')['hour']);
            } else {
                $sourcePosts = $em->getRepository(PostPublishes::class)
                    ->getMostViewPosts(Constants::DETAIL_MOST_VIEW_POST_LIMIT, Constants::HOME_MOST_VIEW_LAST_DAY_2);
                $mostViewPosts = $dataExchangeService->exchangeArrayArticle($sourcePosts);
                $cacheService->set($mostViewCacheKey, $mostViewPosts, $this->getParameter('cache_time')['hour']);
            }
        }

        // Get page 1 lastest posts
        $lastestCacheKey = Constants::CACHE_DETAIL_TOP_LASTEST;
        if (($lastestPosts = $cacheService->get($lastestCacheKey)) === false) {
            // add code
            //$ignoreCateIds  = [Constants::FEATURED_STORIES_CATE_ID,Constants::CARS_REVIEW_CATE_ID, Constants::BIKES_REVIEW_CATE_ID];
            $em = $this->getDoctrine()->getManager();
            $sourcePosts = $em->getRepository(PostPublishes::class)->getTopLastestPosts(Constants::DETAIL_LASTEST_NEWS);
            if ($sourcePosts) {
                $lastestPosts = $dataExchangeService->exchangeArrayArticle(
                    $sourcePosts,
                    Constants::MOBILE_IMAGE_DETAIL_NEWS_FEATURED
                );
            } else {
                $lastestPosts = [];
            }
            $cacheService->set($lastestCacheKey, $lastestPosts, $this->getParameter('cache_time')['hour']);
        }

        $author = self::getAuthorById($data['authorId'], $dataExchangeService);
        if ($author) {
            $urlAuthor = $router->generate('news_author', array('authorSlug' => $author['slug']));
        } else {
            $urlAuthor = '';
        }
        $detailUrlContribute = $router->generate(
            'page_detail',
            ['pageSlug' => 'submit-spy-shots-and-get-bragging-rights',
                'page_id' => 29]
        );

        // box update Tag
        $dataTagsPost = $this->relatedInTag(
            $data['tags'],
            $postId,
            $dataExchangeService
        );

        // box more from Cate
        $dataCatePost = $this->relatedInCate(
            $data['cates'],
            $postId,
            $dataTagsPost['strIgonreId'],
            $dataExchangeService
        );
        // box featuredStories
        $dataFeaturedSrories = $this->featuredStories(
            $postId,
            $dataCatePost['strIgonreId'],
            $dataExchangeService
        );

        $firstNameCate = null;
        $firstNameTag  = null;
        if ($data['tags']) {
            reset($data['tags']);
            $firstNameTag = key($data['tags']);
        }
        if ($data['cates']) {
            reset($data['cates']);
            $firstNameCate = key($data['cates']);
        }

        $response = $this->render('post/detail.html.twig', [
            'data' => $data,
            'author' => $author,
            'urlAuthor' => $urlAuthor,
            'seo' => $data['seo'],
            'postId' => $postId,
            'tagIdList' => $data['tags'],
            'cateIdList' => $data['cates'],
            'parentSlug' => $parentSlug,
            'titleGallery' => $titleGallery,
            'listDataPhotos' => $listDataPhotos,
            'detailUrl' => $detailUrl,
            'mostViewPosts' => $mostViewPosts,
            'lastestPosts' => $lastestPosts,
            'urlShareLink' => $urlShareLink,
            'detailUrlContribute' => $detailUrlContribute,
            'dataRelatedTag'  => $dataTagsPost['data'],
            'nameTag' => $firstNameTag,
            'dataRelatedCate'  => $dataCatePost['data'],
            'nameCate' => $firstNameCate,
            'dataRelatedFeatured' => $dataFeaturedSrories
        ]);
        // ThanhDT: Set cache page
        $this->addCachePage($request, $response);

        return $response;
    }

    /**
     * author: TrieuNT
     * create date: 2018-11-15 04:02 PM
     * @param Request $request
     * @return mixed
     */
    public function getGooglePlus(Request $request)
    {
        $url = $request->get('url');
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, 'https://plusone.google.com/_/+1/fastbutton?url=' . urlencode($url));
        $curl_result = curl_exec($curl);
        curl_close($curl);

        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadHTML($curl_result);
        $counter = $doc->getElementById('aggregateCount');
        $request = ($counter) ? $counter->nodeValue : 0;
        return new JsonResponse($request);
    }


    /**
     * Get data gallery
     * author: TrieuNT
     * create date: 2018-10-19 09:17 AM
     * @param $postId
     * @param $dataExchangeService
     * @return array
     * @throws \Exception
     */

    private function getDataGalleryByPostId($postId, $dataExchangeService)
    {
        $serviceCache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $keyDetail = sprintf(Constants::TABLE_ARTICLE_GALLERY_PHOTO_POSTID, $postId);
        if (($galleryPhoto = $serviceCache->get($keyDetail)) === false) {
            $em = $this->getDoctrine()->getManager();
            $galleryPhotos = $em->getRepository(PhotoGalleries::class)->getDataGalleryPhotosByPostId($postId);
            if ($galleryPhotos) {
                $galleryPhoto = $dataExchangeService->ExchangeGalleryPhotoData($galleryPhotos);
                $serviceCache->set($keyDetail, $galleryPhoto, $this->getParameter('cache_time')['hour']);
            } else {
                $galleryPhoto = [];
            }
        }
        return $galleryPhoto;
    }


    /**
     * Get data gallery
     * author: TrieuNT
     * create date: 2018-10-19 09:26 AM
     * @param $galleryId
     * @param $dataExchangeService
     * @return array
     * @throws \Exception
     */

    private function getDetailGalleryById(
        $galleryId,
        $dataExchangeService,
        $sizeImg = Constants::IMAGE_GALLERY_LIST_SIZE_2
    ) {
        $serviceCache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $keyDetail = sprintf(Constants::TABLE_ARTICLE_DETAIL_GALLERY_ID, $galleryId);
        $galleryPhotoDetail = $serviceCache->get($keyDetail);
        if ($galleryPhotoDetail === false) {
            $em = $this->getDoctrine()->getManager();
            $galleryPhoto = $em->getRepository(PhotoGalleriesImages::class)->getListAllImageByGalleryById($galleryId);
            if ($galleryPhoto) {
                $galleryPhotoDetail = $dataExchangeService->ExchangeAllDetailGalleryPhotoData(
                    $galleryPhoto,
                    $sizeImg,
                    Constants::IMAGE_LARGE_POPUP
                );
                $serviceCache->set($keyDetail, $galleryPhotoDetail, $this->getParameter('cache_time')['hour']);
            }
        }
        return $galleryPhotoDetail;
    }

    /**
     * Detail AMP
     * author: TrieuNT
     * create date: 2018-11-09 10:24 AM
     * @param $postId
     * @param $slug
     * @param DataExchange $exchangeService
     * @param Request $request
     * @param Elasticsearch $elasticSearch
     * @param RouterInterface $router
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */

    public function detailAmp(
        $slug,
        $postId,
        Request $request,
        DataExchange $exchangeService,
        RouterInterface $router,
        Elasticsearch $elasticSearch
    ) {
        $serviceCache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $keyDetail = $this->formatCacheKeyAmp(Constants::TABLE_ARTICLE_DETAIL_BY_ID_AMP, $postId);
        $listDataPhotos = [];
        if (($data = $serviceCache->get($keyDetail)) === false) {
            $em = $this->getDoctrine()->getManager();
            $detail = $em->getRepository(PostPublishes::class)->getDetailAmp($postId);
            if ($detail) {
                $data = $exchangeService->ExchangeArticleDetail($detail, Constants::IMAGE_SHARE_SIZE);
            } else {
                $data = [];
            }
            $serviceCache->set($keyDetail, $data, $this->getParameter('cache_time')['hour']);
        }

        // Post not exist
        if ($data == null || $data == false) {
            $keyParams = $slug . '-' . $postId;
            return $this->changeToNotFoundPageBySlug($keyParams, $request, $elasticSearch, $exchangeService, true);
//            $detailUrlAmp = $this->generateUrl('news_detail_amp', ['slug' => $slug, 'postId' => $postId]);
//            return $this->notFoundPageAmp($detailUrlAmp);
        }

        // Redirect 301 if slug is difference
        if ($slug != $data['slug']) {
            // Old Router redirect 301 to new router
            $url = $this->generateUrl('news_detail_amp', ['slug' => $data['slug'], 'postId' => $postId]);
            $queryString = $request->getQueryString();
            if (!empty($queryString)) {
                $url = $url . '?' . $queryString;
            }
            return $this->redirect($url, 301);
        }

        $titleGallery = null;
        $galleryPhoto = self::getDataGalleryByPostId($postId, $exchangeService);
        if ($galleryPhoto) {
            $titleGallery = $galleryPhoto['title'];
            $galleryId = isset($galleryPhoto['galleryId']) ? $galleryPhoto['galleryId'] : 0;
            $listDataPhotos = self::getDetailGalleryById(
                $galleryId,
                $exchangeService,
                Constants::MOBILE_IMAGE_DETAIL_NEWS_GALLERY
            );
        }
        $detailUrl = $this->generateUrl('news_detail', ['slug' => $slug, 'postId' => $postId]);
        $urlShareLink = 'https://' . $request->getHttpHost() . $request->getBasePath() . $detailUrl;

        // Get MostView
        $mostViewCacheKey = $this->formatCacheKeyAmp(Constants::CACHE_AMP_MOST_VIEW);
        if (($mostViewPosts = $serviceCache->get($mostViewCacheKey)) === false) {
            $em = $this->getDoctrine()->getManager();
            $sourcePosts = $em->getRepository(PostPublishes::class)
                ->getMostViewPosts(Constants::DETAIL_MOST_VIEW_POST_LIMIT, Constants::HOME_MOST_VIEW_LAST_DAY);
            if ($sourcePosts) {
                $countDataPosts = count($sourcePosts);
                if ($countDataPosts < Constants::DETAIL_MOST_VIEW_POST_LIMIT) {
                    $rowRequest = (int)Constants::DETAIL_MOST_VIEW_POST_LIMIT - $countDataPosts;
                    $overSourcePosts = $em->getRepository(PostPublishes::class)
                        ->getMostViewPosts($rowRequest, Constants::HOME_MOST_VIEW_LAST_DAY_2);
                    $viewPosts = array_merge($sourcePosts, $overSourcePosts);
                    $mostViewPosts = $exchangeService->exchangeArrayArticle($viewPosts);
                } else {
                    $mostViewPosts = $exchangeService->exchangeArrayArticle($sourcePosts);
                }
                $serviceCache->set($mostViewCacheKey, $mostViewPosts, $this->getParameter('cache_time')['hour']);
            } else {
                $sourcePosts = $em->getRepository(PostPublishes::class)
                    ->getMostViewPosts(Constants::DETAIL_MOST_VIEW_POST_LIMIT, Constants::HOME_MOST_VIEW_LAST_DAY_2);
                $mostViewPosts = $exchangeService->exchangeArrayArticle($sourcePosts);
                $serviceCache->set($mostViewCacheKey, $mostViewPosts, $this->getParameter('cache_time')['hour']);
            }
        }

        // Get page 1 lastest posts
        $lastestCacheKey = $this->formatCacheKeyAmp(Constants::CACHE_AMP_TOP_LASTEST);
        if (($lastestPosts = $serviceCache->get($lastestCacheKey)) === false) {
            // add code
            $em = $this->getDoctrine()->getManager();
            $sourcePosts = $em->getRepository(PostPublishes::class)->getTopLastestPosts(Constants::DETAIL_LASTEST_NEWS);
            if ($sourcePosts) {
                $lastestPosts = $exchangeService->exchangeArrayArticle($sourcePosts);
            } else {
                $lastestPosts = [];
            }
            $serviceCache->set($lastestCacheKey, $lastestPosts, $this->getParameter('cache_time')['hour']);
        }

        $author = self::getAuthorById($data['authorId'], $exchangeService);
        if ($author) {
            $data['seo']['author'] = $author['displayName'];
        }
        $urlAuthor = $router->generate('news_author', array('authorSlug' => $author['slug']));
        $detailUrlContribute = $this->generateUrl(
            'page_detail',
            ['pageSlug' => 'submit-spy-shots-and-get-bragging-rights',
                'page_id' => 29]
        );
        // box update Tag
        $dataTagsPost = $this->relatedInTag(
            $data['tags'],
            $postId,
            $exchangeService
        );

        // box more from Cate
        $dataCatePost = $this->relatedInCate(
            $data['cates'],
            $postId,
            $dataTagsPost['strIgonreId'],
            $exchangeService
        );
        // box featuredStories
        $dataFeaturedSrories = $this->featuredStories(
            $postId,
            $dataCatePost['strIgonreId'],
            $exchangeService
        );

        $firstNameCate = null;
        $firstNameTag  = null;
        if ($data['tags']) {
            reset($data['tags']);
            $firstNameTag = key($data['tags']);
        }
        if ($data['cates']) {
            reset($data['cates']);
            $firstNameCate = key($data['cates']);
        }

        $response = $this->render('post/amp-detail-mobile.html.twig', [
            'data' => $data,
            'author' => $author,
            'urlAuthor' => $urlAuthor,
            'titleGallery' => $titleGallery,
            'listDataPhotos' => $listDataPhotos,
            'detailUrl' => $detailUrl,
            'seo' => $data['seo'],
            'tagIdList' => $data['tags'],
            'cateIdList' => $data['cates'],
            'postId' => $postId,
            'mostViewPosts' => $mostViewPosts,
            'lastestPosts' => $lastestPosts,
            'dataRelatedTag'  => $dataTagsPost['data'],
            'nameTag' => $firstNameTag,
            'dataRelatedCate'  => $dataCatePost['data'],
            'nameCate' => $firstNameCate,
            'dataRelatedFeatured' => $dataFeaturedSrories,
            'urlShareLink' => $urlShareLink,
            'detailUrlContribute' => $detailUrlContribute,
            'ampDetail' => $postId
        ]);
        // ThanhDT: Set cache page
        $this->addCachePage($request, $response);

        return $response;
    }

    /**
     * @param $detailUrlAmp
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function notFoundPageAmp($detailUrlAmp)
    {
        $seo = array(
            'title' => 'Page not found - ' . $this->getParameter('site_name'),
            'description' => 'Page not found - ' . $this->getParameter('site_name'),
            'og_type' => 'object',
            'is_home' => false,
            'url' => $detailUrlAmp
        );
        $response = $this->render('post/404-amp.html.twig', array('seo' => $seo));
        return $response;
    }

    /**
     * @param Request $request
     * @param DataExchange $dataExchangeService
     * @param RouterInterface $router
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function postAll(Request $request, DataExchange $dataExchangeService, RouterInterface $router)
    {
        $pageSlug = trim($request->get('pageSlug'));
        $keyPage = sprintf(Constants::TABLE_PAGE_BY_SLUG, $pageSlug);
        $cacheService = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $pageData = $cacheService->get($keyPage);
        if ($pageData === false) {
            $em = $this->getDoctrine()->getManager();
            $pageData = $em->getRepository(Pages::class)->getDetailPage($pageSlug);
            if ($pageData) {
                $pageData = $dataExchangeService->ExchangePageDetail($pageData, Constants::IMAGE_SHARE_SIZE);
                $seo = array(
                    'title' => $pageData['title'],
                    'description' => $pageData['sapo'],
                    'image' => $pageData['avatar'],
                    'url' => $this->getParameter('domain') . $pageData['url'],
                    'mobile_url' => $this->getParameter('mobile') . $pageData['url'],
                    'publish_time' => $pageData['meta_publish_time'],
                    'og_type' => 'article',
                    'is_home' => false
                );
                $pageData['seo'] = $seo;
            }
            $cacheService->set($keyPage, $pageData, $this->getParameter('cache_time')['hour']);
        }
        if ($pageData == null) {
            $postShortKey = sprintf(Constants::TABLE_POST_URL_BY_SLUG, $pageSlug);
            $postUrl = $cacheService->get($postShortKey);
            if ($postUrl === false) {
                $em = $this->getDoctrine()->getManager();
                $post = $em->getRepository(PostPublishes::class)->getShortDetailBySlug($pageSlug);
                if ($post) {
                    $postUrl = $dataExchangeService->getPostUrl($post['postId'], $post['slug']);
                } else {
                    $postUrl = '';
                }
                $cacheService->set($postShortKey, $postUrl, $this->getParameter('cache_time')['medium']);
            }
            if ($postUrl) {
                return $this->redirect($postUrl, 301);
            }
        }

        // Get page 1 lastest posts
        $lastestCacheKey = Constants::CACHE_DETAIL_PAGE_TOP_LASTEST;
        if (($lastestPosts = $cacheService->get($lastestCacheKey)) === false) {
            // add code
            //$ignoreCateIds  = [Constants::FEATURED_STORIES_CATE_ID,Constants::CARS_REVIEW_CATE_ID, Constants::BIKES_REVIEW_CATE_ID];
            $em = $this->getDoctrine()->getManager();
            $sourcePosts = $em->getRepository(PostPublishes::class)->getTopLastestPosts(Constants::DETAIL_LASTEST_NEWS);
            if ($sourcePosts) {
                $lastestPosts = $dataExchangeService->exchangeArrayArticle(
                    $sourcePosts,
                    Constants::MOBILE_IMAGE_DETAIL_NEWS_FEATURED
                );
            } else {
                $lastestPosts = [];
            }
            $cacheService->set($lastestCacheKey, $lastestPosts, $this->getParameter('cache_time')['hour']);
        }

        $urlShareLink = 'https://' . $request->getHttpHost() . $request->getBasePath() . $pageData['url'];
        $urlAuthor = $router->generate('news_author', array('authorSlug' => $pageData['userNicename']));
        $detailUrlContribute = $router->generate(
            'page_detail',
            ['pageSlug' => 'submit-spy-shots-and-get-bragging-rights',
                'page_id' => 29]
        );
        $response = $this->render('post/post-page.html.twig', [
            'data' => $pageData,
            'seo' => $pageData['seo'],
            'urlAuthor' => $urlAuthor,
            'lastestPosts' => $lastestPosts,
            'urlShareLink' => $urlShareLink,
            'detailUrlContribute' => $detailUrlContribute
        ]);
        return $response;
    }

    /**
     * @param $cateIdList
     * @param $articleId
     * @param $igonreIds
     * @param DataExchange $dataExchangeService
     * @return array
     * @throws \Exception
     */
    private function relatedInCate($cateIdList, $articleId, $igonreIds, $dataExchangeService)
    {
        $keyCacheCateId = implode(':', $cateIdList);
        $nameCate = implode(',  ', array_keys($cateIdList));
        $keyFeatured = array_search(Constants::FEATURED_STORIES_CAT_ID, $cateIdList);
        if ($keyFeatured) {
            unset($cateIdList[$keyFeatured]);
        }
        $strCateId = implode(',', $cateIdList);

        $articleKeyCache = sprintf(Constants::TABLE_ARTICLE_RELATED_BY_CATE, $keyCacheCateId, $articleId);
        $serviceCache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $articleExchange = $serviceCache->get($articleKeyCache);
        if ($articleExchange === false) {
            $em = $this->getDoctrine()->getManager();
            $dataRelated = $em->getRepository(PostPublishes::class)->getArticleInCateExclude(
                $strCateId,
                $igonreIds,
                Constants::PAGE_SIZE_ARTILCE_RELATED_TAG
            );
            if ($dataRelated) {
                $articles = $dataExchangeService->ExchangeArrayArticle($dataRelated, Constants::IMAGE_DETAIL_NEWS);
                $articleExchange = $articles;
            } else {
                $articleExchange = [];
            }
            $serviceCache->set($articleKeyCache, $articleExchange, $this->getParameter('cache_time')['hour']);
        }

        if ($articleExchange) {
            foreach ($articleExchange as $key => $item) {
                $igonreIds .= ($igonreIds == '') ? $item['id'] : ',' . $item['id'];
            }
        }

        return [
            'data' => $articleExchange,
            'nameCate' => $nameCate,
            'strIgonreId' => $igonreIds,
            'strCateId' => $strCateId
        ];
    }

    /**
     * get article by tag
     * author: TrieuNT
     * create date: 2018-10-19 10:07 AM
     * @param $tagIdList
     * @param $articleId
     * @param $dataExchangeService
     * @return array
     * @throws \Exception
     */
    private function relatedInTag($tagIdList, $articleId, $dataExchangeService)
    {
        $keyCacheTagId = implode(':', $tagIdList);
        $nameTag = implode(',', array_keys($tagIdList));
        $strTagId = implode(',', $tagIdList);
        $articleKeyCache = sprintf(Constants::TABLE_ARTICLE_RELATED_BY_TAG, $keyCacheTagId, $articleId);
        $serviceCache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $articleExchange = $serviceCache->get($articleKeyCache);
        if ($articleExchange === false) {
            $em = $this->getDoctrine()->getManager();
            $dataRelated = $em->getRepository(PostPublishes::class)->getArticleInTagExclude(
                $strTagId,
                $articleId,
                Constants::PAGE_SIZE_ARTILCE_RELATED_TAG
            );

            if ($dataRelated) {
                $articles = $dataExchangeService->ExchangeArrayArticle($dataRelated, Constants::IMAGE_DETAIL_NEWS);
                $articleExchange = $articles;
            } else {
                $articleExchange = [];
            }
            $serviceCache->set($articleKeyCache, $articleExchange, $this->getParameter('cache_time')['hour']);
        }
        $strIgonreId = $articleId;
        if ($articleExchange) {
            foreach ($articleExchange as $key => $item) {
                $strIgonreId .= ($strIgonreId == '') ? $item['id'] : ',' . $item['id'];
            }
        }
        return [
            'data' => $articleExchange,
            'nameTag' => $nameTag,
            'strIgonreId' => $strIgonreId
        ];
    }

    /**
     * @param $postId
     * @param $strIgnoreId
     * @param DataExchange $dataExchangeService
     * @return array|bool
     * @throws \Exception
     */
    private function featuredStories($postId, $strIgnoreId, $dataExchangeService)
    {
        $keySaveCache = preg_replace('/,/', ':', $strIgnoreId);
        $articleKeyCache = sprintf(Constants::TABLE_ARTICLE_FEATURED_STORIES_POST_ID, $keySaveCache, $postId);
        $serviceCache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $articleExchange = $serviceCache->get($articleKeyCache);
        if ($articleExchange === false) {
            $em = $this->getDoctrine()->getManager();
            $featuredStories = $em->getRepository(PostPublishes::class)
                ->getArticleFeaturedStories(
                    Constants::FEATURED_STORIES_CAT_ID,
                    $strIgnoreId,
                    Constants::PAGE_SIZE_ARTILCE_RELATED_TAG
                );
            if ($featuredStories) {
                $articles = $dataExchangeService->ExchangeArrayArticle($featuredStories, Constants::IMAGE_DETAIL_NEWS);
                $articleExchange = $articles;
            } else {
                $articleExchange = [];
            }
            $serviceCache->set($articleKeyCache, $articleExchange, $this->getParameter('cache_time')['hour']);
        }
        return $articleExchange;
    }

    /**
     * @param $postNameSlug
     * @param Request $request
     * @param DataExchange $dataExchangeService
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */

    public function changeToLinkPost($postNameSlug, Request $request, DataExchange $dataExchangeService)
    {
        $cacheService = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $keyCacheCheckPost =  sprintf(Constants::TABLE_ARTICLE_CHECK_POST_LINK, $postNameSlug);

        if (($data = $cacheService->get($keyCacheCheckPost)) === false) {
            $em = $this->getDoctrine()->getManager();
            $infoPost = $em->getRepository(Posts::class)->getDetailPostBySlug($postNameSlug);
            if ($infoPost) {
                $data = $dataExchangeService->exchangeArrayCheckPost($infoPost, Constants::IMAGE_SHARE_SIZE);
            } else {
                $data = [];
            }
            $cacheService->set($keyCacheCheckPost, $data, $this->getParameter('cache_time')['hour']);
        }
        if ($data) {
            // Old Router redirect 301 to new router
            $url =  $data['url'];
            $queryString = $request->getQueryString();
            if (!empty($queryString)) {
                $url = $url . '?' . $queryString;
            }
            return $this->redirect($url, 301);
        }
    }

    /**
     * @param Request $request
     * @param DataExchange $dataExchangeService
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function changeToLinkPage(Request $request, DataExchange $dataExchangeService)
    {
        $cacheService = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $nameSlug = $request->get('pageNameSlug');
        $pageNameSlugLevel = $request->get('nameSlugLevel');
        $pageNameSlugLevel2 = $request->get('nameSlugLevel2');
        if ($pageNameSlugLevel == null && $pageNameSlugLevel2 == null) {
            $keyCacheCheckPage =  sprintf(Constants::TABLE_ARTICLE_CHECK_PAGE_LINK, $nameSlug);
            if (($data = $cacheService->get($keyCacheCheckPage)) === false) {
                $em = $this->getDoctrine()->getManager();
                $infoPage = $em->getRepository(Pages::class)->getDetailPage($nameSlug);
                if ($infoPage) {
                    $data = $dataExchangeService->exchangeArrayCheckPage($infoPage, Constants::IMAGE_SHARE_SIZE);
                } else {
                    $data = [];
                }
                $cacheService->set($keyCacheCheckPage, $data, $this->getParameter('cache_time')['hour']);
            }
        } elseif ($pageNameSlugLevel2 == null) {
            $keyCacheCheckPageV2 =  sprintf(Constants::TABLE_ARTICLE_CHECK_PAGE_LINK_V2, $nameSlug, $pageNameSlugLevel);
            if (($data = $cacheService->get($keyCacheCheckPageV2)) === false) {
                $em = $this->getDoctrine()->getManager();
                $infoPage = $em->getRepository(Pages::class)->getDetailPage($pageNameSlugLevel);
                if ($infoPage) {
                    $data = $dataExchangeService->exchangeArrayCheckPage($infoPage, Constants::IMAGE_SHARE_SIZE);
                } else {
                    $data = [];
                }
                $cacheService->set($keyCacheCheckPageV2, $data, $this->getParameter('cache_time')['hour']);
            }
        } else {
            $keyCacheCheckPageV3 =  sprintf(
                Constants::TABLE_ARTICLE_CHECK_PAGE_LINK_V3,
                $nameSlug,
                $pageNameSlugLevel,
                $pageNameSlugLevel2
            );
            if (($data = $cacheService->get($keyCacheCheckPageV3)) === false) {
                $em = $this->getDoctrine()->getManager();
                $infoPage = $em->getRepository(Pages::class)->getDetailPage($pageNameSlugLevel2);
                if ($infoPage) {
                    $data = $dataExchangeService->exchangeArrayCheckPage($infoPage, Constants::IMAGE_SHARE_SIZE);
                } else {
                    $data = [];
                }
                $cacheService->set($keyCacheCheckPageV3, $data, $this->getParameter('cache_time')['hour']);
            }
        }

        if ($data) {
            // Old Router redirect 301 to new router
            $url = $data['url'];
            $queryString = $request->getQueryString();
            if (!empty($queryString)) {
                $url = $url . '?' . $queryString;
            }
            return $this->redirect($url, 301);
        }
    }
}
