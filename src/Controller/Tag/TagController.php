<?php
/**
 * Created by PhpStorm.
 * User: ThanhDT
 * Date: 5/21/2018
 * Time: 9:46 PM
 */

namespace App\Controller\Tag;

use App\Controller\BaseController;
use App\Entity\PostPublishes;
use App\Entity\Tags;
use App\Service\CryptUtils;
use App\Service\DataExchange;
use App\Utils\Constants;
use App\Utils\Lib;
use Symfony\Component\HttpFoundation\Request;

class TagController extends BaseController
{
    /**
     * List post focus tag
     * author: AnhPT4
     * date:   2018-10-23 10:50 AM
     * @param $tagSlug
     * @param int $currentPage
     * @param DataExchange $dataExchangeService
     * @param Request $request
     * @param CryptUtils $cryptUtils
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function list($tagSlug, $currentPage = 1, DataExchange $dataExchangeService, Request $request, CryptUtils $cryptUtils)
    {
        // Process tag info
        $tag = $this->findByTagSlug($tagSlug);
        if ($tag == null) {
            throw $this->createNotFoundException('The Tag does not exist');
        }
        $tagId = $tag['tagId'];

        // Get article in category
        $articleKeyCache = sprintf(Constants::CACHE_TAG_LASTEST_PAGE, $tagId, $currentPage);
        $service_cache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $tagPostInfo = $service_cache->get($articleKeyCache);
        if ($tagPostInfo === false) {
            $em = $this->getDoctrine()->getManager();
            $sourcePosts = $em->getRepository(PostPublishes::class)->getArticleInTagTimestamp($tagId, Constants::PAGE_SIZE + 1, time());
            if ($sourcePosts) {
                if (count($sourcePosts) > Constants::PAGE_SIZE) {
                    $sourcePosts = array_slice($sourcePosts, 0, Constants::PAGE_SIZE);
                    $lastHomeStream = $sourcePosts[Constants::PAGE_SIZE - 1];
                    $lastInfo = [
                        'tagId' => $tagId,
                        'lastPostId' => $lastHomeStream['postId'],
                        'lastPublishedTimestamp' => $lastHomeStream['publishedTimestamp'],
                        'nextPage' => $currentPage + 1
                    ];
                } else {
                    $lastInfo = null;
                }
                $sourcePosts = $dataExchangeService->ExchangeArrayArticle($sourcePosts, Constants::POST_AVATAR_LIST_SIZE);
                $tagPostInfo = [
                    'posts' => $sourcePosts,
                    'loadMoreToken' => $this->encrypt($cryptUtils, $lastInfo)
                ];
            } else {
                $tagPostInfo = [
                    'posts' => [],
                    'loadMoreToken' => null
                ];
            }

            $service_cache->set($articleKeyCache, $tagPostInfo, $this->getParameter('cache_time')['hour']);
        }

        // Build Seo
        $tagUrl = $this->generateUrl('news_tag',['tagSlug'=>$tagSlug]);
        $rssUrl =  $this->generateUrl('rss_tag',['tagSlug'=>$tagSlug]);

        $seo = $this->buildPagingMeta($tagUrl, $tag['name'], 1, 1, $this->getParameter('site_name'));
        $description = $tag['description']? Lib::subString($tag['description'], 300):sprintf(Constants::BUILD_FORM_SEO_META_DES_TAG,$tag['name']);
        $seo = array_merge($seo, array(
            'og_type' => 'object',
            'is_home' => false,
            'description' => $description
        ));
        // RSS
        $rss = array(
            'title' =>  $tag['name'] . ' Tag Feed',
            'url' => $rssUrl
        );

        $response = $this->render('category/index.html.twig', array(
            'loadMoreToken' => $tagPostInfo['loadMoreToken'],
            'news_list' => $tagPostInfo['posts'],
            'cate_name' => $tag['name'],
            'cate_id' => $tag['tagId'],
            'url_ajax' => $this->generateUrl('news_ajax_tag'),
            'type' => 1,
            'seo' => $seo,
            'rss' => $rss
        ));

        return $response;
    }

    /**
     * find By Tag Slug
     * author: AnhPT4
     * date:   2018-10-25 10:02 AM
     * @param $slug
     * @return null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function findByTagSlug($slug)
    {
        $key_tag_all = sprintf(Constants::TABLE_TAG_BY_SLUG, $slug);
        $service_cache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $tag = $service_cache->get($key_tag_all);
        if ($tag === false) {
            $tag = $this->getDoctrine()->getManager()->getRepository(Tags::class)->getTagBySlug($slug);
            $service_cache->set($key_tag_all, $tag, $this->getParameter('cache_time')['hour']);
        }

        return $tag;
    }
}
