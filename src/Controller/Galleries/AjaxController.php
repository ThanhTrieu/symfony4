<?php
/**
 * Created by PhpStorm.
 * User: AnhPT4
 * Date: 10/18/2018
 * Time: 9:50 AM
 */

namespace App\Controller\Galleries;

use App\Entity\PhotoGalleries;
use App\Controller\BaseController;
use App\Entity\PhotoGalleriesImages;
use App\Service\DataExchange;
use App\Utils\Constants;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Service\CryptUtils;

class AjaxController extends BaseController
{
    /**
     * ajax List post focus Author
     * author: AnhPT4
     * date:   2018-10-23 10:50 AM
     * @param $tagSlug
     * @param int $currentPage
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajax(Request $request, DataExchange $dataExchangeService)
    {
        $galleryId = $request->get('id', 0);
        $response = [
            'success' => 0,
            'html' => '',
        ];
        
        if (!$galleryId) {
            return new JsonResponse($response);
        }
        
        // Get article in category
        $galleryKeyCache = sprintf(Constants::CACHE_PHOTOS_GALLERY_IMAGES_DATA, $galleryId);
        $serviceCache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $galleryExchange = $serviceCache->get($galleryKeyCache);
//        var_dump($galleryExchange);die;
        if ($galleryExchange === false) {
            $em = $this->getDoctrine()->getManager();
            $data = $em->getRepository(PhotoGalleriesImages::class)->getListAllImageByGalleryById($galleryId);
            if ($data) {
                $galleryExchange = $dataExchangeService->ExchangeAllDetailGalleryPhotoData($data, Constants::IMAGE_THUMB_POPUP, Constants::IMAGE_LARGE_POPUP);
            } else {
                $galleryExchange = [];
            }
            $serviceCache->set($galleryKeyCache, $galleryExchange, $this->getParameter('cache_time')['hour']);
        }
        
        if (count($galleryExchange)) {
            $response = [
                'success' => 1,
                'html' => $this->renderView('galleries/popup.html.twig', ['list' => $galleryExchange])
            ];
        }
        return new JsonResponse($response);
    }
    
    /**
     * Ajax Get data Photos
     * author: AnhPT4
     * date: 2018-36-13 11:36 AM
     * @param Request $request
     * @param DataExchange $dataExchangeService
     * @return JsonResponse
     * @throws \Exception
     */
    public function list(Request $request, DataExchange $dataExchangeService, CryptUtils $cryptUtils)
    {
        $token = $request->query->get('loadMoreToken');
        if (!$token) {
            return new JsonResponse([
                'success' => 0,
                'showMore' => 0,
                'lastItem' => null,
                'data' => '',
            ]);
        }
        $curLastInfo = $this->decrypt($cryptUtils, $token);
        if (!$curLastInfo) {
            return new JsonResponse([
                'success' => 0,
                'showMore' => 0,
                'lastItem' => null,
                'data' => '',
            ]);
        }
        
        $pages = $curLastInfo['nextPage'];
        if ($pages <= 0) {
            $response = [
                'success' => 0,
                'showMore' => 0,
                'lastItem' => null,
                'data' => '',
            ];
            return new JsonResponse($response);
        }
        $photosKeyCache = sprintf(Constants::CACHE_PHOTOS_PAGE, $pages);
        $cacheService = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $photoGalleries = $cacheService->get($photosKeyCache);
        if ($photoGalleries === false) {
            $em = $this->getDoctrine()->getManager();
            $data = $em->getRepository(PhotoGalleries::class)->getPhotoGalleriesPaging($pages, Constants::PAGE_SIZE + 1);
            if ($data) {
                $photoGalleries = $dataExchangeService->exchangeArrayGallery($data, Constants::MOBILE_IMAGE_HOME_LATEST_NEWS);
            } else {
                $photoGalleries = [];
            }
            $cacheService->set($photosKeyCache, $photoGalleries, $this->getParameter('cache_time')['hour']);
        }
        
        $photoGalleriesCount = count($photoGalleries);
        if ($photoGalleriesCount > Constants::PAGE_SIZE) {
            $loadMoreToken = $this->encrypt($cryptUtils, ['nextPage' => $pages + 1, 'lastPostId' => 0]);
            $response = [
                'success' => 1,
                'loadMoreToken' => $loadMoreToken,
                'data' => $this->renderView('galleries/contentsphotos.html.twig', ['list' => array_slice($photoGalleries, 0, $photoGalleriesCount - 1)])
            ];
        } else {
            $response = [
                'success' => 1,
                'loadMoreToken' => null,
                'data' => $this->renderView('galleries/contentsphotos.html.twig', array('list' => $photoGalleries))
            ];
        }
        return new JsonResponse($response);
    }
}