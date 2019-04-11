<?php
/**
 * Created by PhpStorm.
 * User: TrieuNT
 * Date: 10/31/2018
 * Time: 9:17 AM
 */

namespace App\Controller\Galleries;

use App\Controller\BaseController;
use App\Service\DataExchange;
use App\Utils\Constants;
use App\Entity\Videos;
use App\Utils\Lib;
use Symfony\Component\HttpFoundation\Request;

class VideosController extends BaseController
{
    /**
     * author: TrieuNT
     * create  date: 2018-10-31 09:24 AM
     * @param  Request $request
     * @param  DataExchange $dataExchangeService
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */

    public function list(Request $request, DataExchange $dataExchangeService)
    {
        $currentPage = $request->get('page', 1);
        // Get Photos in Post
        $videosKeyCache = sprintf(Constants::CACHE_VIDEOS_PAGE, $currentPage);
        $cacheService = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $photoGalleries = $cacheService->get($videosKeyCache);

        if ($photoGalleries === false) {
            $photoGalleries = [];
            $em = $this->getDoctrine()->getManager();
            $galleryTotal = $em->getRepository(Videos::class)->getVideosGalleriesCount();
            $data = $em->getRepository(Videos::class)->getVideoGalleriesPaging($currentPage, Constants::PAGE_SIZE_VIDEOS);
            if ($data) {
                $list = $dataExchangeService->exchangeArrayVideosGallery($data);
                $photoGalleries['total_count'] = $galleryTotal;
                $totalPage = ceil($galleryTotal / Constants::PAGE_SIZE);
                $photoGalleries['total_page'] = $totalPage;
                $photoGalleries['current_page'] = $currentPage;
                $photoGalleries['list'] = $list;
            } else {
                $totalPage = 1;
                $photoGalleries['total_count'] = 0;
                $photoGalleries['total_page'] = 1;
                $photoGalleries['current_page'] = $currentPage;
                $photoGalleries['list'] = [];
            }
            $cacheService->set($videosKeyCache, $photoGalleries, $this->getParameter('cache_time')['hour']);
        }

        // Paging
        $pagination = [
            'pages_count' => $photoGalleries['total_page'],
            'current_page' => $currentPage,
            'url' => $this->generateUrl('galleries_videos'),
        ];

        // Build Seo
        $videoUrl = $this->generateUrl('galleries_videos');
        $seo = $this->buildPagingMeta($videoUrl, 'Videos', $currentPage, $photoGalleries['total_page'], $this->getParameter('site_name'));
        $seo = array_merge($seo, array(
            'og_type' => 'object',
            'is_home' => false,
            'description' => $this->getParameter('site_desc')
        ));

        $firstVideo = ($photoGalleries['list'] && isset($photoGalleries['list'][0])) ? $photoGalleries['list'][0] : [];
        $response = $this->render('galleries/videos.html.twig', array(
            'pagination' => $pagination,
            'list' => $photoGalleries['list'],
            'firstVideo' => $firstVideo,
            'seo' => $seo,
            'parentSlug' => 'galleries'
        ));
        return $response;
    }

    /**
     * Detail Videos
     * author: TrieuNT
     * create date: 2018-11-02 10:21 AM
     * @param Request $request
     * @param DataExchange $dataExchangeService
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */

    public function detail(Request $request, DataExchange $dataExchangeService)
    {
        $videoId = $request->get('galleryId', 1);
        $videosKeyCache = sprintf(Constants::CACHE_VIDEOS_DETAIL_BY_ID, $videoId);
        $cacheService = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $photoGalleries = $cacheService->get($videosKeyCache);
        if ($photoGalleries === false) {
            $em = $this->getDoctrine()->getManager();
            $detail = $em->getRepository(Videos::class)->getDataVideoById($videoId);
            if ($detail) {
                $infoVideo = $dataExchangeService->exchangeArrayVideoDetail($detail);
                $photoGalleries['info'] = $infoVideo;
                $olderVideos = $em->getRepository(Videos::class)->getOlderVideo(Constants::START_PAGE, Constants::PAGE_SIZE, $infoVideo['video_id']);
                $listOlderVideos = $dataExchangeService->exchangeArrayVideosGallery($olderVideos);
                $photoGalleries['listOlderVideos'] =  $listOlderVideos;
            } else {
                $photoGalleries['info'] = [];
                $photoGalleries['listOlderVideos'] = [];
            }
            $cacheService->set($videosKeyCache, $photoGalleries, $this->getParameter('cache_time')['hour']);
        }

        // Build Seo
        if( $photoGalleries['info'] ) {
            $videoUrl = $this->generateUrl('galleries_detail_videos', ['slug' => Lib::convertToSlug($photoGalleries['info']['title']), 'galleryId' => $videoId]);
            $seo = $this->buildPagingMeta($videoUrl, $photoGalleries['info']['title'], 1, 1, $this->getParameter('site_name'));
        } else {
            $videoUrl = null;
            $seo['title'] = 'detail videos';
        }
        $seo = array_merge($seo, array(
            'og_type' => 'object',
            'is_home' => false
        ));
        $listVideos = isset($photoGalleries['listOlderVideos']) ? $photoGalleries['listOlderVideos'] : [];
        $infoVideos = isset($photoGalleries['info']) ? $photoGalleries['info'] : [];

        $response = $this->render('galleries/video-detail.html.twig', array(
            'list' => $listVideos,
            'info' => $infoVideos,
            'seo' => $seo,
            'parentSlug' => 'galleries'
        ));
        return $response;
    }
}
