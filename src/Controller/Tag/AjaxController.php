<?php
/**
 * Created by PhpStorm.
 * User: ThanhDT
 * Date: 10/18/2018
 * Time: 9:50 AM
 */

namespace App\Controller\Tag;

use App\Controller\BaseController;
use App\Entity\PostPublishes;
use App\Service\CryptUtils;
use App\Service\DataExchange;
use App\Utils\Constants;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AjaxController extends BaseController
{
    /**
     * ajax List post focus tag
     * author: AnhPT4
     * date:   2018-10-23 10:50 AM
     * @param Request $request
     * @param DataExchange $dataExchangeService
     * @param CryptUtils $cryptUtils
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function ajax(Request $request, DataExchange $dataExchangeService, CryptUtils $cryptUtils)
    {
        $token = $request->query->get('loadMoreToken');
        if (!$token) {
            return new JsonResponse([
                'success' => 0
            ]);
        }
        $curLastInfo = $this->decrypt($cryptUtils, $token);
        if (!$curLastInfo) {
            return new JsonResponse([
                'success' => 0
            ]);
        }

        $tagId = $curLastInfo['tagId'];
        $postId = $curLastInfo['lastPostId'];
        $timestamp = $curLastInfo['lastPublishedTimestamp'];
        $nextPage = $curLastInfo['nextPage'];
        // Get article in tag
        $articleKeyCache = sprintf(Constants::CACHE_TAG_LASTEST_TIMESTAMP, $tagId, $timestamp);
        $service_cache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $tagPostInfo = $service_cache->get($articleKeyCache);
        if ($tagPostInfo === false) {
            $em = $this->getDoctrine()->getManager();
            $sourcePosts = $em->getRepository(PostPublishes::class)->getArticleInTagTimestamp($tagId, Constants::PAGE_SIZE + 1, $timestamp, [$postId]);
            if ($sourcePosts) {
                if (count($sourcePosts) > Constants::PAGE_SIZE) {
                    $sourcePosts = array_slice($sourcePosts, 0, Constants::PAGE_SIZE);
                    $lastestStream = $sourcePosts[Constants::PAGE_SIZE - 1];
                    $lastInfo = [
                        'tagId' => $tagId,
                        'lastPostId' => $lastestStream['postId'],
                        'lastPublishedTimestamp' => $lastestStream['publishedTimestamp'],
                        'nextPage' => $nextPage + 1
                    ];
                    $lastInfo = $this->encrypt($cryptUtils, $lastInfo);
                } else {
                    $lastInfo = null;
                }
                $sourcePosts = $dataExchangeService->exchangeArrayArticle($sourcePosts, Constants::POST_AVATAR_LIST_SIZE);

                $tagPostInfo = [
                    'posts' => $sourcePosts,
                    'loadMoreToken' => $lastInfo
                ];
            } else {
                $tagPostInfo = [
                    'posts' => [],
                    'loadMoreToken' => null
                ];
            }
            $service_cache->set($articleKeyCache, $tagPostInfo, $this->getParameter('cache_time')['hour']);
        }

        $data = [
            'success' => 1,
            'loadMoreToken' => $tagPostInfo['loadMoreToken'],
            'data' => $this->renderView('category/contents.html.twig', ['news_list' => $tagPostInfo['posts']])
        ];

        return new JsonResponse($data);
    }
}
