<?php
/**
 * Created by PhpStorm.
 * User: ThanhDT
 * Date: 5/22/2018
 * Time: 8:29 AM
 */

namespace App\Controller\Author;

use App\Controller\BaseController;
use App\Entity\PostPublishes;
use App\Service\CryptUtils;
use App\Service\DataExchange;
use App\Utils\Constants;
use App\Utils\Lib;
use Symfony\Component\HttpFoundation\Request;

class AuthorController extends BaseController
{
    /**
     * Get data Post in by Author
     * author: AnhPT4
     * date:   2018-10-25 09:37 AM
     * @param $authorSlug
     * @param int $currentPage
     * @param DataExchange $dataExchangeService
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function list(
        $authorSlug,
        $currentPage = 1,
        DataExchange $dataExchangeService,
        Request $request,
        CryptUtils $cryptUtils
    )
    {
        $author = self::getAuthor($authorSlug, $dataExchangeService);
        if ($author == null) {
            throw $this->createNotFoundException('The Author does not exist');
        }

        $postAuthor = $author['id'];
        // Get article in category
        $service_cache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $articleKeyCache = sprintf(Constants::CACHE_AUTHOR_LASTEST_PAGE, $postAuthor, $currentPage);
        $authorPostInfo = $service_cache->get($articleKeyCache);
        if ($authorPostInfo === false) {
            $em = $this->getDoctrine()->getManager();
            $sourcePosts = $em->getRepository(PostPublishes::class)->getArticleByAuthorTimestamp($postAuthor, Constants::PAGE_SIZE + 1, time());
            if ($sourcePosts) {
                if (count($sourcePosts) > Constants::PAGE_SIZE) {
                    $sourcePosts = array_slice($sourcePosts, 0, Constants::PAGE_SIZE);
                    $lastHomeStream = $sourcePosts[Constants::PAGE_SIZE - 1];
                    $lastInfo = [
                        'authorId' => $postAuthor,
                        'lastPostId' => $lastHomeStream['postId'],
                        'lastPublishedTimestamp' => $lastHomeStream['publishedTimestamp'],
                        'nextPage' => $currentPage + 1
                    ];
                } else {
                    $lastInfo = null;
                }
                $sourcePosts = $dataExchangeService->ExchangeArrayArticle($sourcePosts, Constants::POST_AVATAR_LIST_SIZE);
                $authorPostInfo = [
                    'authorPosts' => $sourcePosts,
                    'loadMoreToken' => $this->encrypt($cryptUtils, $lastInfo)
                ];
            } else {
                $authorPostInfo = [
                    'authorPosts' => [],
                    'loadMoreToken' => null
                ];
            }

            $service_cache->set($articleKeyCache, $authorPostInfo, $this->getParameter('cache_time')['hour']);
        }

        // Build Seo
        $authorUrl = $this->generateUrl('news_author', ['authorSlug' => $authorSlug]);
        $rssUrl = $this->generateUrl('rss_author', ['username' => $authorSlug]);

        $seo = $this->buildPagingMeta($authorUrl, $author['displayName'], 1, 1, $this->getParameter('site_name'));
        $description = sprintf(Constants::BUILD_FORM_SEO_META_DES_AUTHOR,$author['displayName']);
        $seo = array_merge($seo, array(
            'og_type' => 'object',
            'is_home' => false,
            'description' => $description
        ));
        // RSS
        $rss = array(
            'title' => 'Posts by ' . $author['displayName'] . ' Feed',
            'url' => $rssUrl
        );

        $response = $this->render('category/index.html.twig', array(
            'loadMoreToken' => $authorPostInfo['loadMoreToken'],
            'news_list' => $authorPostInfo['authorPosts'],
            'cate_name' => 'Written by ' . $author['displayName'],
            'cate_id' => $postAuthor,
            'url_ajax' => $this->generateUrl('news_ajax_author'),
            'type' => 2,
            'seo' => $seo,
            'rss' => $rss
        ));
        // ThanhDT: Set cache page
        $this->addCachePage($request, $response);

        return $response;
    }
}
