<?php
/**
 * Created by PhpStorm.
 * User: ThanhDT
 * Date: 5/22/2018
 * Time: 9:04 PM
 */

namespace App\Controller;

use App\Entity\PostPublishes;
use App\Utils\Constants;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class RedirectController extends BaseController
{
    public function home()
    {
        return $this->redirectToRoute('index', [], 301);
    }

    public function cate($cateSlug)
    {
        $url = $this->generateUrl('news_cate', ['cateSlug' => $cateSlug]);
        return $this->redirect($url, 301);
    }

    public function cateParent($parentSlug, $cateSlug)
    {
        $url = $this->generateUrl('news_sub_cate', ['parentSlug' => $parentSlug, 'cateSlug' => $cateSlug]);
        return $this->redirect($url, 301);
    }

    public function cateParentLevel2($rootSlug, $parentSlug, $cateSlug)
    {
        $url = $this->generateUrl('news_sub_cate_level2', ['rootSlug' => $rootSlug, 'parentSlug' => $parentSlug, 'cateSlug' => $cateSlug]);
        return $this->redirect($url, 301);
    }

    public function tag($tagSlug)
    {
        $url = $this->generateUrl('news_tag', ['tagSlug' => $tagSlug]);
        return $this->redirect($url, 301);
    }

    public function authorPaging($username)
    {
        $url = $this->generateUrl('news_author', ['authorSlug' => $username]);
        return $this->redirect($url, 301);
    }

    public function detailAmp($slug, $postId)
    {
        $url = $this->generateUrl('news_detail_amp', ['slug' => $slug, 'postId' => $postId]);
        return $this->redirect($url, 301);
    }

    public function detail($slug, $postId)
    {
        $url = $this->generateUrl('news_detail', ['slug' => $slug, 'postId' => $postId]);
        return $this->redirect($url, 301);
    }

    /**
     * remove slash in end of url
     * author: ThanhDT
     * date:   2018-05-22 09:09 PM
     * @param $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeTrailingSlash($url)
    {
//        $pathInfo = $request->getPathInfo();
//        $requestUri = $request->getRequestUri();
//        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);
        //var_dump($url);die;
        // 308 (Permanent Redirect) is similar to 301 (Moved Permanently) except
        // that it does not allow changing the request method (e.g. from POST to GET)
        return $this->redirect($url, 301);
    }

    /**
     * Redirect 301 from post comment paging to post detail
     * author: ThanhDT
     * date:   2018-12-13 03:46 PM
     * @param $year
     * @param $month
     * @param $slug
     * @param int $postId
     * @param string $imageSlug
     * @return RedirectResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function postCommentPaging($year, $month, $slug, $postId = 0, $imageSlug = '')
    {
        if ($postId == 0) {
            $detailBySlug = $this->getShortPostBySlug($slug);
            if ($detailBySlug) {
                $postId = $detailBySlug['postId'];
            }
        }
        if ($postId == 0) {
            return $this->redirectToRoute('page404', [], 301);
        }
        $url = $this->generateUrl('news_detail', ['slug' => $slug, 'postId' => $postId]);

        return $this->redirect($url, 301);
    }

    /**
     * Remove old staging router
     * author: ThanhDT
     * date:   2018-06-04 02:09 PM
     * @param $slug
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function staging($slug)
    {
        $url = '/' . $slug;
        // 308 (Permanent Redirect) is similar to 301 (Moved Permanently) except
        // that it does not allow changing the request method (e.g. from POST to GET)
        return $this->redirect($url, 301);
    }

    /**
     * Lowercase Url
     * author: ThanhDT
     * date:   2018-06-04 03:38 PM
     * @param $slug
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function lowercaseSlug($slug)
    {
        $url = '/' . strtolower($slug);
        // 308 (Permanent Redirect) is similar to 301 (Moved Permanently) except
        // that it does not allow changing the request method (e.g. from POST to GET)
        return $this->redirect($url, 301);
    }

    /**
     * Redirect 301 to post detail
     * @param $slug
     * @return RedirectResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function postSlug($slug)
    {
        $slug = strtolower($slug);
        $detailUrl = $this->getPostUrlBySlug($slug);
        if ($detailUrl) {
            return $this->redirect($detailUrl, 301);
        }

        return $this->redirectToRoute('page404', [], 301);
    }

    /**
     * Redirect 301 to post detail
     * author: ThanhDT
     * date:   2018-12-13 04:09 PM
     * @param $slug
     * @return RedirectResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function postSlugAmp($slug)
    {
        $service_cache = $this->getCacheProvider(Constants::SERVER_CACHE_ARTICLE);
        $keyDetail = sprintf(Constants::TABLE_ARTICLE_DETAIL_URL_BY_SLUG_AMP, $slug);
        if (($detailUrl = $service_cache->get($keyDetail)) === false) {
            $em = $this->getDoctrine()->getManager();
            $data = $em->getRepository(PostPublishes::class)->getShortDetailBySlug($slug);
            if ($data) {
                $detailUrl = $this->generateUrl('news_detail_amp', ['slug' => $data['slug'], 'postId' => $data['postId']]);
                $service_cache->set($keyDetail, $detailUrl, $this->getParameter('cache_time')['hour']);
            }
        }
        if ($detailUrl) {
            return $this->redirect($detailUrl, 301);
        }

        return $this->redirectToRoute('page404', [], 301);
    }

    public function imagePostSlug($slug)
    {
        $detailUrl = $this->getPostUrlBySlug($slug);
        if ($detailUrl) {
            return $this->redirect($detailUrl, 301);
        }

        return $this->redirectToRoute('page404', [], 301);
    }

    public function cateRssRedirect($cateSlug, $parentSlug = '', $rootSlug = '')
    {
        if (!empty($parentSlug)) {
            if (!empty($rootSlug)) {
                $cateUrl = $this->generateUrl('rss_sub_cate_level2', ['rootSlug' => $rootSlug, 'parentSlug' => $parentSlug, 'cateSlug' => $cateSlug]);
            } else {
                $cateUrl = $this->generateUrl('rss_sub_cate', ['parentSlug' => $parentSlug, 'cateSlug' => $cateSlug]);
            }
        } else {
            $cateUrl = $this->generateUrl('rss_category', ['cateSlug' => $cateSlug]);
        }

        return $this->redirect($cateUrl, 301);
    }

    public function tagsRssRedirect($tagSlug)
    {
        return $this->redirectToRoute('rss_tag', ['tagSlug' => $tagSlug], 301);
    }

    public function detailFeedRedirect($slug, $postId)
    {
        return $this->redirectToRoute('rss_detail_news', ['slug' => $slug, 'postId' => $postId], 301);
    }
}
