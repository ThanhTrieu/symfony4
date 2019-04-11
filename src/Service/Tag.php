<?php
/**
 * User: HaiHS
 * Date: 6/19/2017
 * Time: 10:35 AM
 */
namespace NewsBundle\Services;

use NewsBundle\Utils\CacheProvider;
use NewsBundle\Utils\Constants;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;

class Tag
{

    protected $em;
    protected $container;
    const TAG_CACHE_NAME = 'TagCache';

    public function __construct(ContainerInterface $container/*, Router $router*/)
    {
        $this->container = $container;
        //$this->router = $router;
        $this->em = $container->get('doctrine')->getManager();
    }

    /**
     * Get all categories from cache
     * author: ThanhDT
     * date:   2018-05-17 10:06 PM
     * @return array|bool|string
     */
    public function getTagsKeySlug()
    {
        global $all_tag_slug;
        if (isset($all_tag_slug)) {
            return $all_tag_slug;
        }

        $key_tag_all = Constants::TABLE_TAG_ALL_SLUG;
        $service_cache = CacheProvider::createInstance($this->container, $this->container->get('request_stack')->getCurrentRequest(), Constants::ServerCacheArticle);
        //$this->container->get(Constants::ServerCacheArticle);
        $all_tag_slug = $service_cache->get($key_tag_all);
        if ($all_tag_slug === false) {
            $cates = $this->em->getRepository('NewsBundle:WpTerms')->getAllTags();
            $all_tag_slug = [];
            foreach ($cates as $cate) {
                $all_tag_slug[$cate['slug']] = $cate;
            }
            $service_cache->set($key_tag_all, $all_tag_slug, $this->container->getParameter('cache_time')['hour']);
        }

        return $all_tag_slug;
    }

    /**
     * Get category by slug
     * author: ThanhDT
     * date:   2018-05-17 10:18 PM
     * @param $cateSlug
     * @return null
     */
    public function findByTagSlug($cateSlug)
    {
        $all_cate_slug = $this->getTagsKeySlug();
        if (isset($all_cate_slug[$cateSlug])) {
            return $all_cate_slug[$cateSlug];
        }

        return null;
    }

    /**
     * Content: get tags with key is id
     * author: HaiHS
     * createdDate: 2018-05-29 18:07
     */

    public function getTagsKeyId()
    {
        global $all_tags_id;
        if (isset($all_tags_id)) {
            return $all_tags_id;
        }

        $key_tags_all = Constants::TABLE_TAG_ALL_ID;
        $service_cache = CacheProvider::createInstance($this->container, $this->container->get('request_stack')->getCurrentRequest(), Constants::ServerCacheArticle);
        $all_tags_id = $service_cache->get($key_tags_all);
        if ($all_tags_id === false) {
            $cates = $this->em->getRepository('NewsBundle:WpTerms')->getAllTags();
            $all_tags_id = [];
            foreach ($cates as $cate) {
                $all_tags_id[$cate['termTaxonomyId']] = $cate;
            }
            $service_cache->set($key_tags_all, $all_tags_id, $this->container->getParameter('cache_time')['hour']);
        }

        return $all_tags_id;
    }
}
