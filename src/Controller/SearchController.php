<?php
/**
 * Created by PhpStorm.
 * User: TrieuNT
 * Date: 10/30/2018
 * Time: 8:47 AM
 */

namespace App\Controller;

use App\Service\Elasticsearch;
use App\Utils\Constants;
use App\Utils\Lib;
use Symfony\Component\HttpFoundation\Request;
use App\Service\DataExchange;

class SearchController extends BaseController
{
    const LIMIT_ITEMS = 12;
    const LIMIT_KEYWORDS = 100;

    /**
     * author: TrieuNT
     * create date: 2018-10-30 08:49 AM
     * @param Elasticsearch $elasticSearch
     * @param Request $request
     * @param DataExchange $dataExchangeService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, Elasticsearch $elasticSearch, DataExchange $dataExchangeService)
    {
        $total = 0;
        $error = '';
        $keyword = $request->get('q');
        $keyword = filter_var($keyword, FILTER_SANITIZE_STRING);
        $keyword = (mb_strlen($keyword) > self::LIMIT_KEYWORDS) ? substr($keyword, 0, self::LIMIT_KEYWORDS) : $keyword;

        $page = $request->get('page');
        $page = (is_numeric($page) && $page > 0) ? $page : 1;
        $start = ($page - 1) * self::LIMIT_ITEMS;
        // search for title or sapo
        $queryData = '{"multi_match" : {"query" : "'.$keyword.'", "fields": ["title", "sapo"] }}';
        $data = $elasticSearch->search(Elasticsearch::INDIA_POSTS_INDEX, $queryData, $start, self::LIMIT_ITEMS, $total, $error);
        $totalRecord = $total;

        $mainData = $dataExchangeService->exchangeArraySearchPost($data, Constants::POST_AVATAR_LIST_SIZE);
        // pagination
        $totalPage = $total > 0  ? ceil($totalRecord/self::LIMIT_ITEMS) : 0;

        // show prev page
        $prePage = ($page > 1 && $page <= $totalPage) ? true : false;
        // show next page
        $nextPage = ($page >= 1 && $page < $totalPage) ? true : false;

        // Build Seo
        $searchUrl = $this->generateUrl('search').'?q='.$keyword;
        $seo = $this->buildPagingMeta($searchUrl, $keyword, $page, $totalPage, $this->getParameter('site_name'));
        $seo = array_merge($seo, array(
            'og_type' => 'object',
            'is_home' => true,
        ));

        $response = $this->render('default/search.html.twig', [
            'data' => $mainData,
            'keyword' => $keyword,
            'page' => $page,
            'totalPage' => $totalPage,
            'prePage' => $prePage,
            'nextPage' => $nextPage,
            'seo' => $seo,
            'module' => 'search'
        ]);

        return $response;
    }

    public function changToSearch(Request $request)
    {
        $keyword = $request->get('keyword');
        $keyword = strtolower($keyword);
        $findTag = strpos($keyword, '/');

        if ($findTag !== false) {
            $key = substr($keyword, 0, $findTag);
            if (is_numeric($key)) {
                $key = preg_replace('/[^a-z-\+_]/i', '', $keyword);
            }
            $keyword = $key;
        }

        $searchUrl = $this->generateUrl('search').'?q='.$keyword;
        return $this->redirect($searchUrl, 301);
    }
}
