<?php

namespace App\Controller;

use App\Service\Category;
use App\Service\DataExchange;
use App\Service\Images;
use App\Utils\Constants;
use App\Utils\Lib;
use App\Entity\PostPublishes;
use App\Entity\Tags;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RssController extends BaseController
{

    /**
     * Content: RSS get limit = 18 latest news
     * author: TrieuNT
     * create date: 2018-10-22 05:26 PM
     * @param $dataExchange
     * @return Response
     * @throws \Exception
     */

    public function rssHome(DataExchange $dataExchange)
    {
        $serviceCache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $keyRssFeed = Constants::TABLE_RSS_FEED;
        if (($content = $serviceCache->get($keyRssFeed)) === false) {
            $data['title'] = $this->getParameter('site_name');
            $data['link'] = $this->getParameter('domain');
            $data['atomLink'] = $this->getParameter('domain');
            $data['desc'] = $this->getParameter('site_desc');
            $data['image'] = $this->getParameter('site_logo');
            $data['BuildDate'] = date('D, d M Y H:i:s O');

            $em = $this->getDoctrine()->getManager();
            $latestNews = $em->getRepository(PostPublishes::class)->getArticleRssLatest(Constants::FEED_RSS_LIMIT);
            if ($latestNews) {
                $data['items'] = $dataExchange->exchangeArticleRss($latestNews);
            }
            $response = $this->render('default/rss.xml.twig', array(
                'data' => $data
            ));
            $content = Lib::gzip($response->getContent());
            $serviceCache->set($keyRssFeed, $content, $this->getParameter('cache_time')['hour']);
        }

        $response = new Response($content);
        $response->headers->set('Content-Type', 'xml');
        $response->headers->set('Content-Encoding', 'gzip');

        return $response;
    }

    /**
     * Content: Cate RSS
     * author: TrieuNT
     * create date: 2018-10-22 06:03 PM
     * @param $request
     * @param $dataExchange
     * @param $cateService
     * @return Response
     * @throws \Exception
     */

    public function cateRss(Request $request, DataExchange $dataExchange, Category $cateService)
    {
        $serviceCache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $rootSlug = $request->get('rootSlug');
        $parentSlug = $request->get('parentSlug');
        $cateSlug = $request->get('cateSlug');

        $keyRssCateFeed = sprintf(Constants::TABLE_RSS_CATE_FEED, $cateSlug);
        if (($content = $serviceCache->get($keyRssCateFeed)) === false) {
            if (!empty($parentSlug)) {
                if (!empty($rootSlug)) {
                    $cateUrl = $this->generateUrl('rss_sub_cate_level2', ['rootSlug' => $rootSlug, 'parentSlug' => $parentSlug, 'cateSlug' => $cateSlug]);
                } else {
                    $cateUrl = $this->generateUrl('rss_sub_cate', ['parentSlug' => $parentSlug, 'cateSlug' => $cateSlug]);
                }
            } else {
                $cateUrl = $this->generateUrl('rss_category', ['cateSlug' => $cateSlug]);
            }

            $cate = $cateService->getCateBySlug($cateSlug);

            if ($cate == null) {
                return $this->forward('App\Controller\DefaultController::_404page');
            }

            $cateId = isset($cate['cateId']) ? $cate['cateId'] : '';
            if (isset($cate['parentId']) && $cate['parentId'] == 0) {
                $cateIdList =  $cateService->getCategoryParentId($cate['cateId']);
            } else {
                $cateIdList = $cateId;
            }

            $data['title'] = $cate['name'] . ' &#8211; ' . $this->getParameter('site_name');
            $data['link'] = $this->getParameter('domain');
            $data['atomLink'] = $this->getParameter('domain') . $cateUrl;
            $data['desc'] = $this->getParameter('site_desc');
            $data['image'] = $this->getParameter('site_logo');
            $data['BuildDate'] = date('D, d M y H:i:s O');

            $em = $this->getDoctrine()->getManager();
            $articleList = $em->getRepository(PostPublishes::class)->getArticleInCatePaging($cateIdList, 1, Constants::FEED_RSS_LIMIT);
            if ($articleList) {
                $data['items'] = $dataExchange->exchangeArticleRss($articleList);
            }

            $response = $this->render('default/rss.xml.twig', array(
                'data' => $data
            ));
            $content = Lib::gzip($response->getContent());
            $serviceCache->set($keyRssCateFeed, $content, $this->getParameter('cache_time')['hour']);
        }

        $response = new Response($content);
        $response->headers->set('Content-Type', 'xml');
        $response->headers->set('Content-Encoding', 'gzip');
        return $response;
    }


    /**
     * Content: RSS tags
     * author: TrieuNT
     * create date: 2018-10-22 05:37 PM
     * @param $tagSlug
     * @param DataExchange $dataExchange
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */

    public function tagsRss($tagSlug, DataExchange $dataExchange)
    {
        $service_cache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $key_rss_tags_feed = sprintf(Constants::TABLE_RSS_TAGS_FEED, $tagSlug);
        if (($content = $service_cache->get($key_rss_tags_feed)) === false) {
            $em = $this->getDoctrine()->getManager();
            //$tagService = $this->get(Tag::TAG_CACHE_NAME);
            $tags = $em->getRepository(Tags::class)->getTagBySlug($tagSlug);
            if ($tags == null) {
                return $this->forward('App\Controller\DefaultController::_404page');
            }

            $data['title'] = $tags['name'] . ' &#8211; ' . $this->getParameter('site_name');
            $data['link'] = $this->getParameter('domain');
            $data['atomLink'] = $this->getParameter('domain') . $this->generateUrl('rss_tag', ['tagSlug' => $tagSlug]);
            $data['desc'] = $this->getParameter('site_desc');
            $data['image'] = $this->getParameter('site_logo');
            $data['BuildDate'] = date('D, d M y H:i:s O');

            $tagsData = $em->getRepository(PostPublishes::class)->getArticleInTagRss($tags['tagId'], Constants::FEED_RSS_LIMIT);
            if ($tagsData) {
                $data['items'] = $dataExchange->exchangeArticleRss($tagsData);
            }
            $response = $this->render('default/rss.xml.twig', array(
                'data' => $data
            ));
            $content = Lib::gzip($response->getContent());
            $service_cache->set($key_rss_tags_feed, $content, $this->getParameter('cache_time')['hour']);
        }

        $response = new Response($content);
        $response->headers->set('Content-Type', 'xml');
        $response->headers->set('Content-Encoding', 'gzip');
        return $response;
    }

    /**
     * Content: RSS author
     * author: TrieuNT
     * create date: 2018-10-23 09:48 AM
     * @param $username
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function authorRss($username, DataExchange $dataExchange)
    {
        $service_cache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $key_rss_author_feed = sprintf(Constants::TABLE_RSS_AUTHOR_FEED, $username);
        if (($content = $service_cache->get($key_rss_author_feed)) === false) {
            $author = self::getAuthor($username, $dataExchange);
            if ($author == null) {
                return $this->forward('App\Controller\DefaultController::_404page');
            }

            $data['title'] = $author['displayName'] . ' &#8211; ' . $this->getParameter('site_name');
            $data['link'] = $this->getParameter('domain');
            $data['atomLink'] = $this->getParameter('domain') . $this->generateUrl('rss_author', ['username' => $username], true);
            ;
            $data['desc'] = $this->getParameter('site_desc');
            $data['image'] = $this->getParameter('site_logo');
            $data['BuildDate'] = date('D, d M y H:i:s O');

            $em = $this->getDoctrine()->getManager();
            $authordata = $em->getRepository(PostPublishes::class)->getArticleByAuthorRss($author['id'], Constants::FEED_RSS_LIMIT);
            if ($authordata) {
                $data['items'] = $dataExchange->exchangeArticleRss($authordata);
            }
            $response = $this->render('default/rss.xml.twig', array(
                'data' => $data
            ));
            $content = Lib::gzip($response->getContent());
            $service_cache->set($key_rss_author_feed, $content, $this->getParameter('cache_time')['hour']);
        }

        $response = new Response($content);
        $response->headers->set('Content-Type', 'xml');
        $response->headers->set('Content-Encoding', 'gzip');
        return $response;
    }
    /**
     * Content: Feed news detail
     * author: TrieuNT
     * create date: 2018-10-23 09:25 AM
     * @param $postId
     * @param $year
     * @param $month
     * @param $slug
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function detailFeed($slug, $postId, DataExchange $dataExchange)
    {
        $serviceCache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $keyDetail = sprintf(Constants::TABLE_ARTICLE_DETAIL_BY_SLUG, $slug);
        $content = null;
        if (($data = $serviceCache->get($keyDetail)) === false) {
            $em = $this->getDoctrine()->getManager();
            $detail = $em->getRepository(PostPublishes::class)->getDetailBySlug($slug);

            if ($detail == null) {
                return $this->forward('App\Controller\DefaultController::_404page');
            }
            $linkDetail = $this->getParameter('domain') . $this->generateUrl('rss_detail_news', [ 'slug' => $slug, 'postId' => $postId], true);
            $data['title'] = 'Comments on: ' . $detail['title'];
            $data['link'] = $linkDetail;
            $data['atomLink'] = $linkDetail;
            $data['BuildDate'] = date('D, d M y H:i:s O');
            $data['desc'] = $this->getParameter('site_desc');
            $data['items'][] = $dataExchange->ExchangeArticleDetail($detail);
            $response = $this->render('default/rss.xml.twig', array(
                'data' => $data,
                'domain' => $linkDetail
            ));

            $content = Lib::gzip($response->getContent());
            $serviceCache->set($keyDetail, $data, $this->getParameter('cache_time')['hour']);
        }
        $response = new Response($content);
        $response->headers->set('Content-Type', 'xml');
        $response->headers->set('Content-Encoding', 'gzip');
        return $response;
    }
    /**
     * Content: Feed gallery detail
     * author: TrieuNT
     * create date: 2018-10-23 10:05 AM
     * @param $slug
     * @param $imageSlug
     * @param $postId
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */

    public function detailGalleryFeed($year, $month, $slug, $imageSlug, $postId = 0)
    {
        $serviceCache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $keyImageDetail = sprintf(Constants::TABLE_ARTICLE_IMAGE_GALLERY_DETAIL, $slug, $imageSlug);
        if (($data = $serviceCache->get($keyImageDetail)) === false) {
            $em = $this->getDoctrine()->getManager();
            $detail = $em->getRepository('NewsBundle:GalleryImages')->getImageDetailBySlug($imageSlug);
            if ($detail) {
                if ($postId == 0) {
                    $shortPostSlug = $this->getShortPostBySlug($slug);
                    if ($shortPostSlug) {
                        $postId = $shortPostSlug['postId'];
                        $publishedDate = isset($shortPostSlug['publishedDate']) ? $shortPostSlug['publishedDate'] : new \DateTime(Constants::MIN_DATE);
                        $year = $publishedDate->format('Y');
                        $month = $publishedDate->format('m');
                    }
                }
                $linkDetail = $this->getParameter('domain') . $this->generateUrl('news_detail_gallery', ['year' => $year, 'month' => $month, 'slug' => $slug, 'postId' => $postId, 'imageSlug' => $imageSlug], true);
                $data['title'] = 'Comments on: ' . $detail['title'];
                $data['link'] = $linkDetail;
                $data['atomLink'] = $linkDetail;
                $data['BuildDate'] = date('D, d M y H:i:s O');
                $data['desc'] = $this->getParameter('site_desc');
                $imageService = $this->get(Images::IMAGE_SERVICE);
                //$attach = $em->getRepository('NewsBundle:WpPostmeta')->getAttachPost($detail['id']);
                $data['attach'] = $imageService->getFullImage($detail['url']);
                /*$comments = $em->getRepository('NewsBundle:WpComments')->getCommentDetailById($detail['id']);
                if (!empty($comments)) {
                    $dataExchange = $this->get(DataExchange::DATA_EXCHANGE);
                    $data['items'] = $dataExchange->ExchangeCommentRss($comments, $data);
                }*/

                $response = $this->render('default/rss.xml.twig', array(
                    'data' => $data,
                    'domain' => $linkDetail
                ));
                $content = Lib::gzip($response->getContent());
                $serviceCache->set($keyImageDetail, $data, $this->getParameter('cache_time')['hour']);
            }
        }
        $response = new Response($content);
        $response->headers->set('Content-Type', 'xml');
        $response->headers->set('Content-Encoding', 'gzip');
        return $response;
    }
}
