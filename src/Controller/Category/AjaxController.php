<?php
/**
 * Created by PhpStorm.
 * User: ThanhDT
 * Date: 10/18/2018
 * Time: 9:50 AM
 */

namespace App\Controller\Category;

use App\Controller\BaseController;
use App\Entity\PostPublishes;
use App\Service\Category;
use App\Service\CryptUtils;
use App\Service\DataExchange;
use App\Utils\Constants;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AjaxController extends BaseController
{
    /**
     * author: AnhPT4
     * date:   2018-10-22 09:26 AM
     * @param Request $request
     * @param DataExchange $dataExchangeService
     * @param Category $cateService
     * @param CryptUtils $cryptUtils
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function ajax(Request $request, DataExchange $dataExchangeService, Category $cateService, CryptUtils $cryptUtils)
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

        $cateId = $curLastInfo['cateId'];
        $postId = $curLastInfo['lastPostId'];
        $timestamp = $curLastInfo['lastPublishedTimestamp'];
        $nextPage = $curLastInfo['nextPage'];
        $cateIdList = $cateService->getCategoryParentId($cateId);
        if (empty($cateIdList)) {
            $cateIdList = $cateId;
        }
        // Get article in category
        $articleKeyCache = sprintf(Constants::CACHE_CATEGORY_LASTEST_TIMESTAMP, $cateId, $timestamp);
        $service_cache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $catePostInfo = $service_cache->get($articleKeyCache);
        if ($catePostInfo === false) {
            $em = $this->getDoctrine()->getManager();
            $sourcePosts = $em->getRepository(PostPublishes::class)->getArticleInCateTimestamp($cateIdList, Constants::PAGE_SIZE + 1, $timestamp, [$postId]);
            if ($sourcePosts) {
                if (count($sourcePosts) > Constants::PAGE_SIZE) {
                    $sourcePosts = array_slice($sourcePosts, 0, Constants::PAGE_SIZE);
                    $lastestStream = $sourcePosts[Constants::PAGE_SIZE - 1];
                    $lastInfo = [
                        'cateId' => $cateId,
                        'lastPostId' => $lastestStream['postId'],
                        'lastPublishedTimestamp' => $lastestStream['publishedTimestamp'],
                        'nextPage' => $nextPage + 1
                    ];
                    $lastInfo = $this->encrypt($cryptUtils, $lastInfo);
                } else {
                    $lastInfo = null;
                }
                $sourcePosts = $dataExchangeService->exchangeArrayArticle($sourcePosts, Constants::POST_AVATAR_LIST_SIZE);

                $catePostInfo = [
                    'posts' => $sourcePosts,
                    'loadMoreToken' => $lastInfo
                ];
            } else {
                $catePostInfo = [
                    'posts' => [],
                    'loadMoreToken' => null
                ];
            }

            $service_cache->set($articleKeyCache, $catePostInfo, $this->getParameter('cache_time')['hour']);
        }
        $data = [
            'success' => 1,
            //'showMore' => $lastestPostInfo['loadMoreToken'] ? 1 : 0,
            'loadMoreToken' => $catePostInfo['loadMoreToken'],
            'data' => $this->renderView('category/contents.html.twig', ['news_list' => $catePostInfo['posts']])
        ];

        return new JsonResponse($data);
    }
}
