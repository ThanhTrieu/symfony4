<?php

namespace App\Service;

use App\Entity\Categories;
use App\Entity\KtCates;
use App\Utils\CacheProvider;
use App\Utils\Constants;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;

class Category
{
    const CATEGORY_CACHE_TIME = 86400;
    const CATEGORY_CACHE_NAME = 'CategoryCache';
    protected $request;
    protected $doctrine;
    protected $cacheParams;
    protected $container;
    private $router;

    public function __construct(RequestStack $requestStack, ManagerRegistry $doctrine, $cacheParams)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->doctrine = $doctrine;
        $this->cacheParams = $cacheParams;
    }

    /**
     * Get category by slug
     * author: ThanhDT
     * date:   2018-05-17 10:18 PM
     * @param $cateSlug
     * @return null
     */
    public function getCateBySlug($cateSlug)
    {
        $allCateSlug = $this->getCategoriesKeySlug();
        if (isset($allCateSlug[$cateSlug])) {
            return $allCateSlug[$cateSlug];
        }

        return null;
    }

    /**
     * Get all categories from cache
     * author: ThanhDT
     * date:   2018-05-17 10:06 PM
     * @return array|bool|string
     */
    public function getCategoriesKeySlug()
    {
        global $allCateSlugs;
        if (isset($allCateSlugs)) {
            return $allCateSlugs;
        }

        $keyCategoryAll = Constants::CACHE_CATEGORY_ALL_SLUG;
        $cacheService = CacheProvider::createInstance($this->request, Constants::SERVER_CACHE_ARTICLE, $this->cacheParams);
        $allCateSlugs = $cacheService->get($keyCategoryAll);
        if ($allCateSlugs === false) {
            $em = $this->doctrine->getManager();
            $cates = $em->getRepository(Categories::class)->getAllCategories();
            $allCateSlugs = [];
            foreach ($cates as $cate) {
                $allCateSlugs[$cate['slug']] = $cate;
            }
            $cacheService->set($keyCategoryAll, $allCateSlugs, self::CATEGORY_CACHE_TIME);
        }

        return $allCateSlugs;
    }

    /**
     * Get category by Taxonomy ID
     * author: ThanhDT
     * date:   2018-05-18 10:49 AM
     * @param $cateId
     * @return null
     * @internal param $id
     */
    public function getCateById($cateId)
    {
        $allCates = $this->getCategoriesKeyId();
        if (isset($allCates[$cateId])) {
            return $allCates[$cateId];
        }

        return null;
    }

    /**
     * Get categories with key Taxonomy ID
     * author: ThanhDT
     * date:   2018-05-18 10:49 AM
     * @return array|bool|string
     */
    public function getCategoriesKeyId()
    {
        global $allCateIds;
        if (isset($allCateIds)) {
            return $allCateIds;
        }

        $keyCategoryAll = Constants::CACHE_CATEGORY_ALL_ID;
        $cacheService = CacheProvider::createInstance($this->request, Constants::SERVER_CACHE_ARTICLE, $this->cacheParams);
        $allCateIds = $cacheService->get($keyCategoryAll);
        if ($allCateIds === false) {
            $em = $this->doctrine->getManager();
            $cates = $em->getRepository(Categories::class)->getAllCategories();
            $allCateIds = [];
            foreach ($cates as $cate) {
                $allCateIds[$cate['cateId']] = $cate;
            }
            $cacheService->set($keyCategoryAll, $allCateIds, self::CATEGORY_CACHE_TIME);
        }

        return $allCateIds;
    }

    /**
     * Get list child cate by TermId
     * author: ThanhDT
     * date:   2018-05-18 10:49 AM
     * @param $termId
     * @return array
     */
    public function getCategoryParentId($termId)
    {
        $allCateParentIds = $this->getCategoriesParentId();
        if (isset($allCateParentIds[$termId])) {
            return $allCateParentIds[$termId];
        }

        return [];
    }

    /**
     * Get categories with key parentId is TermId
     * author: ThanhDT
     * date:   2018-05-18 10:50 AM
     * @return array|bool|string
     */
    public function getCategoriesParentId()
    {
        global $allCateIdParent;
        if (isset($allCateIdParent)) {
            return $allCateIdParent;
        }

        $keyCategoryAll = Constants::CACHE_CATEGORY_ALL_SLUG_CHILD;
        $cacheService = CacheProvider::createInstance($this->request, Constants::SERVER_CACHE_ARTICLE, $this->cacheParams);
        $allCateIdParent = $cacheService->get($keyCategoryAll);
        if ($allCateIdParent === false) {
            $em = $this->doctrine->getManager();
            $cates = $em->getRepository(Categories::class)->getAllCategories();
            foreach ($cates as $cate) {
                if ($cate['parentId'] == 0) {
                    $allCateIdParent[$cate['cateId']][] = $cate['cateId'];
                } else {
                    $allCateIdParent[$cate['parentId']][] = $cate['cateId'];
                }
            }
            $cacheService->set($keyCategoryAll, $allCateIdParent, self::CATEGORY_CACHE_TIME);
        }

        return $allCateIdParent;
    }

    /**
     * Get list child cate by TermId
     * author: AnhPT4
     * date:   2018-10-23 04:52 PM
     * @param $termId
     * @return array
     */
    public function getCategoryChild($termId)
    {
        if (!$termId) {
            return [];
        }

        $allCateChild = $this->getCategoriesChild();
        $allCateChild = array_map('array_values', $allCateChild);
        if (isset($allCateChild[$termId])) {
            return $allCateChild[$termId];
        }
        return [];
    }

    /**
     * get all Categories Child : id,name,slug
     * author: AnhPT4
     * date:   2018-10-23 05:05 PM
     * @return array|bool|mixed|string
     * @throws \Exception
     */
    public function getCategoriesChild()
    {
        global $allCateChilds;
        if (isset($allCateChilds)) {
            return $allCateChilds;
        }

        $keyCategoryAll = Constants::CACHE_CATEGORY_ALL_CHILD;
        $cacheService = CacheProvider::createInstance($this->request, Constants::SERVER_CACHE_ARTICLE, $this->cacheParams);
        $allCateChilds = $cacheService->get($keyCategoryAll);
        if ($allCateChilds === false) {
            $em = $this->doctrine->getManager();
            $cates = $em->getRepository(Categories::class)->getAllCategories();
            $i = 0;
            foreach ($cates as $cate) {
                if ($cate['parentId'] != 0) {
                    $allCateChilds[$cate['parentId']][$i]['name'] = $cate['name'];
                    $allCateChilds[$cate['parentId']][$i]['slug'] = $cate['slug'];
                    $allCateChilds[$cate['parentId']][$i]['cateId'] = $cate['cateId'];
                }
                $i++;
            }
            $cacheService->set($keyCategoryAll, $allCateChilds, self::CATEGORY_CACHE_TIME);
        }

        return $allCateChilds;
    }
}
