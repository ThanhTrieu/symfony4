<?php

namespace App\Controller;

use App\Service\Category;
use App\Service\DataExchange;
use App\Utils\Constants;
use App\Utils\Lib;
use App\Entity\PostPublishes;
use App\Entity\AdminUsers;
use App\Entity\Tags;
use Symfony\Component\HttpFoundation\Response;

class SitemapController extends BaseController
{

    const _BEGIN_DATE = 1214850600; //'2008-07-01';

    /**
     * author: TrieuNT
     * create date: 2018-10-24 11:04 AM
     * @return Response
     * @throws \Exception
     */
    public function sitemap()
    {
        //$redis = $this->get(RedisUtils::ArticleCache);
        $serviceCache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $keySiteMap = Constants::TABLE_ARTICLE_SITE_MAP;
        $content = $serviceCache->get($keySiteMap);

        if ($content === false) {
            //set_time_limit(3600);
            // Lấy bài ngày hiện tại, nếu chưa có dữ liệu thì chưa gen sitemap ngày hiện tại
            $hours = date('H');
            $em = $this->getDoctrine()->getManager();
            $firstCurrentArticle = $em->getRepository(PostPublishes::class)->getArticleByCurrentTime();
            // nếu chưa có bài cache trong 5 phút
            if (count($firstCurrentArticle) == 0) {
                $cacheTimeExpire = 300;
                $endDate = strtotime("-1 month", time()); //strtotime("-1 day", strtotime(date("d-m-Y")));
                // Nếu đã có bài thì cache đến đầu ngày hôm sau
            } else {
                // Thời gian còn lại để cache
                $cacheTimeExpire = 86400 - $hours * 3600;
                // nếu < 5 phút thì cache 5 phút
                if ($cacheTimeExpire < 300) {
                    $cacheTimeExpire = 300;
                }
                $endDate = time(); //strtotime(date("d-m-Y"));
            }
            // Generate sitemap
            $articleExchange = [];
            $category = self::generateLink(self::_BEGIN_DATE, $endDate);
            if ($category) {
                $articleExchange['site_map_pubDate'] = date("c");
                $articleExchange['site_map_generator'] = $this->getParameter('site_name');;
                $articleExchange['site_map_domain_local'] = $this->getParameter('domain');
                $articleExchange['news_list'] = $category;
            }

            //$response = new Response();
            $response = $this->render('default/sitemap.xml.twig', array(
                'data' => $articleExchange
            ));
            $content = Lib::gzip($response->getContent());
            $serviceCache->set($keySiteMap, $content, $cacheTimeExpire);
        }

        $response = new Response($content);
        $response->headers->set('Content-Type', 'xml');
        $response->headers->set('Content-Encoding', 'gzip');
        return $response;
    }

    public function checkSitemap()
    {
        set_time_limit(7200);
        //$category = self::generateLink(self::_BEGIN_DATE, date("d-m-Y"));
        $content = file_get_contents('https://indianautosblog.com/sitemap_index.xml');
        $content = @Lib::gunzip($content);
        $pattern = "/<loc>((.)*)<\\/loc>/i";
        preg_match_all($pattern, $content, $category);
        $html = '';
        $i = 0;
        //$category = [];
        foreach ($category[1] as $item => $value) {
            $i++;
            //$url = 'http://tinnhac.com'.$item["url"];
            $url = $value;
            //echo $url;die;
            //$data =
            self::clearAllCache($url);
            /*$data = str_replace('<?xml version="1.0"?>','',$data);
            $data = preg_replace('/<\/?urlset[^>]*>/i','', $data);
            $data = trim($data);*/
            //if($data!='') $html .= $url.'<br>';
            $html .= $url . '<br>';
            //if($i==10)break;
        }
        $response = new Response($html);
        return $response;
    }

    public function clearAllCache($url = '')
    {
        if (empty($url)) {
            return false;
        }

        try {
            $header = [
                'X-Refresh-Cache:123456',
            ];
            $process = curl_init($url);
            curl_setopt($process, CURLOPT_HTTPHEADER, $header);
            curl_setopt($process, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.95 Safari/537.36');
            curl_setopt($process, CURLOPT_TIMEOUT, 30);
            curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
            $return = curl_exec($process);
            curl_close($process);
            return $return;
        } catch (\Exception $e) {
            //  Yii::log("\n".$e->getMessage());
            return false;
        }
    }


    /**
     * Content: tags sitemap
     * author: TrieuNT
     * create date: 2018-10-24 11:09 AM
     * @param $tags
     * @return Response
     * @throws \Exception
     */
    public function tags()
    {
        $serviceCache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $keyArticleSiteMapTags = Constants::TABLE_ARTICLE_SITE_MAP_TAGS;
        $content = $serviceCache->get($keyArticleSiteMapTags);
        if ($content === false) {
            $allCate = $this->getTagsKeySlug();
            $article = null;
            $category = null;
            $articleExchange = null;
            if ($allCate) {
                $category = self::buidLinkTagsSiteMap($allCate);
                if ($category) {
                    $articleExchange['site_map_pubDate'] = date("c");
                    $articleExchange['site_map_generator'] = $this->getParameter('site_name');
                    $articleExchange['site_map_domain_local'] = $this->getParameter('domain');
                    $articleExchange['news_list'] = $category;
                }
            }

            $response = $this->render('default/category-sitemap.xml.twig', array(
                'data' => $articleExchange
            ));
            $content = Lib::gzip($response->getContent());
            $serviceCache->set($keyArticleSiteMapTags, $content, $this->getParameter('cache_time')['hour']);
        }

        $response = new Response($content);
        $response->headers->set('Content-Encoding', 'gzip');
        $response->headers->set('Content-Type', 'xml');
        return $response;
    }


    /**
     * Get all categories from cache
     * author: ThanhDT
     * date:   2018-05-17 10:06 PM
     * @return array|bool|string
     * @throws \Exception
     */
    private function getTagsKeySlug()
    {
        global $allTagSlug;
        if (isset($allTagSlug)) {
            return $allTagSlug;
        }

        $key_tag_all = Constants::TABLE_TAG_ALL_SLUG;
        $serviceCache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $allTagSlug = $serviceCache->get($key_tag_all);
        if ($allTagSlug === false) {
            $em = $this->getDoctrine()->getManager();
            $cates = $em->getRepository(Tags::class)->getAllTags();
            $allTagSlug = [];
            foreach ($cates as $cate) {
                $allTagSlug[$cate['slug']] = $cate;
            }
            $serviceCache->set($key_tag_all, $allTagSlug, $this->getParameter('cache_time')['hour']);
        }
        return $allTagSlug;
    }

    /**
     * Content: author sitemap
     * author: TrieuNT
     * create date: 2018-10-24 11:35 AM
     */
    public function author()
    {
        $serviceCache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $keyCacheSiteMapAuthor = Constants::TABLE_ARTICLE_SITE_MAP_AUTHOR;
        if (($content = $serviceCache->get($keyCacheSiteMapAuthor)) === false) {
            $em = $this->getDoctrine()->getManager();
            $author = $em->getRepository(AdminUsers::class)->getAuthorSitemap();
            if ($author) {
                // link author
                $category = self::buidLinkAuthor($author);
                $articleExchange['site_map_pubDate'] = date("c");
                $articleExchange['site_map_generator'] = $this->getParameter('site_name');
                $articleExchange['site_map_domain_local'] = $this->getParameter('domain');
                $articleExchange['news_list'] = $category;
            }

            $response = $this->render('default/category-sitemap.xml.twig', array(
                'data' => isset($articleExchange) ? $articleExchange : null
            ));
            $content = Lib::gzip($response->getContent());
            $serviceCache->set($keyCacheSiteMapAuthor, $content, $this->getParameter('cache_time')['hour']);
        }

        $response = new Response($content);
        $response->headers->set('Content-Encoding', 'gzip');
        $response->headers->set('Content-Type', 'xml');
        return $response;
    }

    /**
     * author: TrieuNT
     * create date: 2018-10-24 11:45 AM
     * @param $category
     * @return Response
     * @throws \Exception
     */
    public function category(Category $category)
    {
        $serviceCache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $keyCacheSiteMapAuthor = Constants::TABLE_ARTICLE_SITE_MAP_CATEGORY;
        $content = $serviceCache->get($keyCacheSiteMapAuthor);
        if ($content === false) {
            $allCate = $category->getCategoriesKeyId();
            $article = null;
            $category = null;
            $article_exchange = null;

            if ($allCate) {
                $category = self::buidLinkSiteMap($allCate);
                if ($category) {
                    $article_exchange['site_map_pubDate'] = date("c");
                    $article_exchange['site_map_generator'] = $this->getParameter('site_name');
                    $article_exchange['site_map_domain_local'] = $this->getParameter('domain');
                    $article_exchange['news_list'] = $category;
                }
            }
            $response = $this->render('default/category-sitemap.xml.twig', array(
                'data' => $article_exchange
            ));
            $content = Lib::gzip($response->getContent());
            $serviceCache->set($keyCacheSiteMapAuthor, $content, $this->getParameter('cache_time')['hour']);
        }

        $response = new Response($content);
        $response->headers->set('Content-Encoding', 'gzip');
        $response->headers->set('Content-Type', 'xml');
        return $response;
    }

    /**
     * author: TrieuNT
     * create date: 2018-10-24 01:20 PM
     * @param $date
     * @param $dataExchange
     * @return Response
     * @throws \Exception
     */
    public function article($date, DataExchange $dataExchange)
    {
        //$redis = $this->get(RedisUtils::ArticleCache);
        $serviceCache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $keyArticleSiteMapArticle = sprintf(Constants::TABLE_ARTICLE_SITE_MAP_ARTICLE, $date);
        $content = $serviceCache->get($keyArticleSiteMapArticle);
        if ($content === false) {
            //$date_format = date_create($date);
            //$date_format = date_format($date_format, 'Y-m-d');
            $startDate = strtotime($date.'01');
            $endDate = strtotime(date('Y-m-t 23:59:59', $startDate));
            if ($endDate > time()) $endDate = time();
            /*$y = substr($date, 0, 4);
            $m = substr($date, 4, 2);

            $startDate = new \DateTime("$y-$m");
            $startDate->modify('first day of this month');
            $startDate->setTime(0, 0, 0);

            $endDate = clone $startDate;
            $endDate->modify('last day of this month');
            $endDate->setTime(23, 59, 59);*/
/*var_dump(date('Y-m-d H:i:s', $startDate));
var_dump(date('Y-m-d H:i:s', $endDate));die;*/
            $em = $this->getDoctrine()->getManager();
            $data = $em->getRepository(PostPublishes::class)
                ->getPostsBetweenDates(
                    $startDate,
                    $endDate
                );
            //$data = $em->getRepository(PostPublishes::class)->getArticleByDate($date_format);
            $articleExchange = [];
            if ($data) {
                $article = $dataExchange->ExchangeSitemapArrayArticle($data);
                $articleExchange['site_map_pubDate'] = date("c");
                $articleExchange['site_map_generator'] = $this->getParameter('site_name');
                $articleExchange['site_map_domain_local'] = $this->getParameter('domain');
                $articleExchange['news_list'] = $article;
            }

            $response = $this->render('default/article-sitemap.xml.twig', array(
                'data' => $articleExchange,
            ));
            $content = Lib::gzip($response->getContent());
            $now = date("Ym");
            if ($date >= $now) {
                $cacheExpire = 300; // 5 minutes
            } else {
                $cacheExpire = 86400; // cache 1 day
            }
            $serviceCache->set($keyArticleSiteMapArticle, $content, $cacheExpire);
        }

        $response = new Response($content);
        $response->headers->set('Content-Encoding', 'gzip');
        $response->headers->set('Content-Type', 'xml');
        return $response;
    }

    /**
     * Sitemap news
     * author: ThanhDT
     * date:   2018-09-17 02:04 PM
     * @return Response
     * @throws \Exception
     */
    public function siteMapNews()
    {
        $serviceCache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $keySiteMap = Constants::TABLE_ARTICLE_SITEMAP_NEWS;
        $content = $serviceCache->get($keySiteMap);
        if ($content === false) {
            //set_time_limit(3600);
            // Lấy bài ngày hiện tại, nếu chưa có dữ liệu thì chưa gen sitemap ngày hiện tại
            $hours = date('H');
            $em = $this->getDoctrine()->getManager();
            $firstCurrentArticle = $em->getRepository(PostPublishes::class)->getArticleByCurrentTime();
            // nếu chưa có bài cache trong 5 phút
            if (count($firstCurrentArticle) == 0) {
                $cacheTimeExpire = 300;
                $endDate = strtotime("-1 month", time()); //strtotime("-1 day", strtotime(date("d-m-Y")));
                // Nếu đã có bài thì cache đến đầu ngày hôm sau
            } else {
                // Thời gian còn lại để cache
                $cacheTimeExpire = 86400 - $hours * 3600;
                // nếu < 5 phút thì cache 5 phút
                if ($cacheTimeExpire < 300) {
                    $cacheTimeExpire = 300;
                }
                $endDate = time(); //strtotime(date("d-m-Y"));
            }
            // Generate sitemap
            $articleExchange = [];
            $category = self::generateLink(self::_BEGIN_DATE, $endDate, true);
            if ($category) {
                $articleExchange['site_map_pubDate'] = date("c");
                $articleExchange['site_map_generator'] = $this->getParameter('site_name');
                $articleExchange['site_map_domain_local'] = $this->getParameter('domain');
                $articleExchange['news_list'] = $category;
            }

            //$response = new Response();
            $response = $this->render('default/sitemap-news.xml.twig', array(
                'data' => $articleExchange
            ));
            $content = Lib::gzip($response->getContent());
            $serviceCache->set($keySiteMap, $content, $cacheTimeExpire);
        }

        $response = new Response($content);
        $response->headers->set('Content-Type', 'xml');
        $response->headers->set('Content-Encoding', 'gzip');
        return $response;
    }

    /**
     * Sitemap news by date
     * author: ThanhDT
     * date:   2018-09-17 02:04 PM
     * @param $date
     * @param $dataExchange
     * @return Response
     * @throws \Exception
     */
    public function newsDate($date, DataExchange $dataExchange)
    {
        $serviceCache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $keyArticleSiteMapArticle = sprintf(Constants::TABLE_ARTICLE_SITEMAP_NEWS_DATE, $date);
        $content = $serviceCache->get($keyArticleSiteMapArticle);
        if ($content === false) {
            $startDate = strtotime($date.'01');
            $endDate = strtotime(date('Y-m-t 23:59:59', $startDate));
            if ($endDate > time()) $endDate = time();
            //die($date_format);
            $em = $this->getDoctrine()->getManager();
            $data = $em->getRepository(PostPublishes::class)->getArticleByDate($startDate, $endDate);
            $articleExchange = [];
            $articleExchange['news_list'] = [];
            if ($data) {
                $article = $dataExchange->ExchangeSitemapArrayArticle($data);
                $articleExchange['site_map_pubDate'] = date("c");
                $articleExchange['site_map_generator'] = $this->getParameter('site_name');
                $articleExchange['site_map_domain_local'] = $this->getParameter('domain');
                $articleExchange['news_list'] = $article;
            }

            $response = $this->render('default/news-sitemap.xml.twig', array(
                'data' => $articleExchange,
            ));
            $content = Lib::gzip($response->getContent());
            $now = date("Ymd");
            if ($date >= $now) {
                $cacheExpire = 300; // 5 minutes
            } else {
                $cacheExpire = 86400; // cache 1 day
            }
            $serviceCache->set($keyArticleSiteMapArticle, $content, $cacheExpire);
        }

        $response = new Response($content);
        $response->headers->set('Content-Encoding', 'gzip');
        $response->headers->set('Content-Type', 'xml');
        return $response;
    }

    /**
     * author: TrieuNT
     * create date: 2018-10-24 01:45 PM
     * @param $beginDate
     * @param $endDate
     * @param $isNewSiteMap
     * @return null
     */
    private function generateLink($beginDate, $endDate, $isNewSiteMap = false)
    {
        // Những ngày không có tin, sitemap bị trống - Fix lỗi Webmaster tools
        $date = $endDate;
        //$listDate = [];
        $url = [];
        $isFirstCurrent = date('Ym', $date) == date('Ym');
        while ($beginDate < $date) {
            $dateName = date('Ym', $date);
            $urlItem = [];
            if ($isNewSiteMap) {
                $urlItem['url'] = $this->generateUrl('site_map_news_by_date', ['date' => $dateName]);
            } else {
                $urlItem['url'] = $this->generateUrl('site_map_category_article', ['date' => $dateName]);
            }
            if ($isFirstCurrent) {
                $urlItem['time'] = date('c', time());
                $isFirstCurrent = false;
            } else {
                $urlItem['time'] = date('c', strtotime(date("Y-m-t 23:59:59", $date)));
            }
            $url[] = $urlItem;

            $date = strtotime("-1 month", $date);
        }
        /*$url = [];
        foreach ($listDate as $key => $dateName) {
            if (isset($excludeDate[$dateName[0]])) {
                continue;
            }
            if ($isNewSiteMap) {
                $url[$key]['url'] = $this->generateUrl('site_map_news_by_date', ['date' => $dateName[0]]);
            } else {
                $url[$key]['url'] = $this->generateUrl('site_map_category_article', ['date' => $dateName[0]]);
            }
            $url[$key]['time'] = date('c', $dateName[1]);
        }*/
        return $url;
    }

    /**
     * Content: build link author
     * author: TrieuNT
     * create date: 2018-10-24 01:45 PM
     */
    private function buidLinkAuthor($data)
    {
        $author = [];
        if ($data) {
            foreach ($data as $i => $item) {
                $author[$i]['url'] = $this->generateUrl('news_author', ['authorSlug' => $item['slug']]);
                $author[$i]['datePublished'] = date("c");
            }
        }
        return $author;
    }

    private function buidLinkSiteMap($data)
    {
        $category_all_id = $data;
        if ($category_all_id) {
            //$category = $this->get(Category::CATEGORY_CACHE_NAME)->getCategoriesParentId();
            foreach ($category_all_id as $i => $item) {
                if ($item['cateId'] == 1) {
                    unset($category_all_id[$i]);
                    continue;
                }
                if ($item['parentId'] > 0) {
                    if (!isset($category_all_id[$item['parentId']])) {
                        unset($category_all_id[$i]);
                        continue;
                    }
                    $category_all_id[$i]['url'] = $this->generateUrl('news_sub_cate', ['parentSlug' => $category_all_id[$item['parentId']]['slug'], 'cateSlug' => $item['slug']]);
                    $category_all_id[$i]['datePublished'] = date("c");
                } else {
                    $category_all_id[$i]['url'] = $this->generateUrl('news_cate', ['cateSlug' => $item['slug']]);
                    $category_all_id[$i]['datePublished'] = date("c");
                }
            }
        }
        return $category_all_id;
    }

    private function buidLinkTagsSiteMap($data)
    {
        $category_all_id = $data;
        try {
            if ($category_all_id) {
                foreach ($category_all_id as $i => $item) {
                    $category_all_id[$i]['url'] = $this->generateUrl('news_tag', ['tagSlug' => $item['slug']]);
                    $category_all_id[$i]['datePublished'] = date("c");
                }
            }
            return $category_all_id;
        } catch (\Exception $e) {
            return [];
        }
    }
}
