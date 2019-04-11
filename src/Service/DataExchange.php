<?php
/**
 * Created by PhpStorm.
 * User: Do Tien Thanh
 * Date: 6/17/2017
 * Time: 10:09 AM
 */

namespace App\Service;

use App\Utils\Constants;
use App\Utils\Lib;
use App\Utils\WordPressLib;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Constraints\Date;

class DataExchange
{
    const DATA_EXCHANGE = 'DataExchange';
    private $imageService;
    private $domain;
    private $mobile;
    private $router;

    public function __construct(Router $router, string $domain, string $mobile, Images $imageService)
    {
        $this->router = $router;
        $this->imageService = $imageService;
        $this->domain = $domain;
        $this->mobile = $mobile;
    }

    public function exchangeArrayCheckPost(
        $data,
        $imageSize = Constants::POST_AVATAR_LIST_SIZE
    ) {
        $new_data = [];
        if ($data) {
            $imageService = $this->imageService;
            $articleId = $data['postId'];
            $publishedDate = isset($data['publishedDate'])
                ? $data['publishedDate']
                : new \DateTime(Constants::MIN_DATE);
            $new_data['id'] = $articleId;
            $new_data['slug'] = $data['slug'];
            $new_data['authorId'] = $data['authorId'];
            $new_data['reviewId'] = $data['reviewId'];
            $new_data['s_publish_time'] = Lib::fomartDate($publishedDate);
            $new_data['title'] = $data['title'];
            $new_data['avatar'] = $imageService->getImageSize($data['avatar'], $imageSize);
            $new_data['url'] = $this->getPostUrl($articleId, $data['slug']);
            $newItem['url_amp'] = $this->router->generate(
                'news_detail_amp',
                ['slug' => $data['slug'], 'postId' => $data['postId']]
            );
            // Add SEO info
            $seo = array(
                'title' => empty($data['seoTitle']) ? $data['title'] : $data['seoTitle'],
                'description' => empty($data['seoMetadesc']) ? $data['sapo'] : $data['seoMetadesc'],
                'image' => $imageService->getImageSize($data['avatar'], Constants::IMAGE_SHARE_SIZE),
                'url' => $this->domain . $new_data['url'],
                'mobile_url' => $this->mobile . $new_data['url'],
                'publish_time' => Lib::fomartMetaDate($publishedDate),
                'og_type' => 'article',
                'is_home' => false,
                'author' => 'Unknown',
                'amp' =>  $this->mobile . $new_data['url']
            );
            $new_data['seo'] = $seo;
        }
        return $new_data;
    }

    public function exchangeArrayCheckPage(
        $data,
        $imageSize = Constants::POST_AVATAR_LIST_SIZE
    ) {
        $new_data = [];
        if ($data) {
            $imageService = $this->imageService;
            $articleId = $data['pageId'];
            $publishedDate = isset($data['publishedDate'])
                ? $data['publishedDate']
                : new \DateTime(Constants::MIN_DATE);
            $new_data['id'] = $articleId;
            $new_data['slug'] = $data['slug'];
            $new_data['authorId'] = $data['authorId'];
            $new_data['reviewId'] = $data['reviewId'];
            $new_data['s_publish_time'] = Lib::fomartDate($publishedDate);
            $new_data['title'] = $data['title'];
            $new_data['avatar'] = $imageService->getImageSize($data['avatar'], $imageSize);
            $new_data['url'] = $this->router->generate(
                'page_detail',
                ['pageSlug' => $data['slug'], 'page_id' => $articleId]
            );
            // Add SEO info
            $seo = array(
                'title' => $data['title'] ,
                'description' =>  $data['sapo'],
                'image' => $imageService->getImageSize($data['avatar'], Constants::IMAGE_SHARE_SIZE),
                'url' => $this->domain . $new_data['url'],
                'mobile_url' => $this->mobile . $new_data['url'],
                'publish_time' => Lib::fomartMetaDate($publishedDate),
                'og_type' => 'article',
                'is_home' => false,
                'author' => 'Unknown',
                'amp' =>  $this->mobile . $new_data['url']
            );
            $new_data['seo'] = $seo;
        }
        return $new_data;
    }

    public function exchangeArrayArticle($data, $imageSize = Constants::POST_AVATAR_LIST_SIZE, $specialImageSize = null)
    {
        $new_data = [];
        if ($data) {
            $imageService = $this->imageService;
            foreach ($data as $index => $item) {
                $articleId = $item['postId'];
                $publishedDate = isset($item['publishedDate']) ? $item['publishedDate'] : new \DateTime(Constants::MIN_DATE);
                $newItem = [];
                $newItem['id'] = $articleId;
                $newItem['slug'] = $item['slug'];
                $newItem['s_publish_time'] = Lib::fomartDate($publishedDate);
                if (isset($item['publishedTimestamp'])) {
                    $newItem['publishedTimestamp'] = $item['publishedTimestamp'];
                }
                $newItem['title'] = $item['title'];
                $newItem['sapo'] = $item['sapo'];
                if ($index == 0 && $specialImageSize) {
                    $newItem['avatar'] = $imageService->getImageSize($item['avatar'], $specialImageSize);
                } else {
                    $newItem['avatar'] = $imageService->getImageSize($item['avatar'], $imageSize);
                }
                $newItem['avatar_amp'] = $imageService->getImageSize($item['avatar'], $specialImageSize);
                $newItem['url'] = $this->router->generate('news_detail', ['slug' => $item['slug'], 'postId' => $item['postId']]);
                $newItem['url_amp'] = $this->router->generate('news_detail_amp', ['slug' => $item['slug'], 'postId' => $item['postId']]);
                // Build author info
                if (isset($item['authorSlug'])) {
                    $newItem['author_name'] = $item['fullname'];
                    $newItem['author_url'] = $this->router->generate('news_author', ['authorSlug' => $item['authorSlug']]);
                } else {
                    $newItem['author_name'] = '';
                    $newItem['author_url'] = '';
                }
                //$item['short_url'] = $this->router->generate('news_detail_short', ['id' => $item['articleid']]);
                $new_data[] = $newItem;
            }
        }

        return $new_data;
    }

    /**
     * @param $data
     * @param string $imageSize
     * @return array
     * @throws \Exception
     */
    public function exchangeSitemapArrayArticle ($data, $imageSize =  Constants::POST_AVATAR_LIST_SIZE )
    {
        $new_data = [];
        if ($data) {
            $imageService = $this->imageService;
            foreach ($data as $item) {
                $articleId = $item['postId'];
                $publishedDate = isset($item['publishedDate']) ? $item['publishedDate'] : new \DateTime(Constants::MIN_DATE);
                $newItem = [];
                $newItem['id'] = $articleId;
                //$newItem['slug'] = $item['postName'];
                $newItem['s_publish_time'] = Lib::fomartMetaDate($publishedDate);
                $modifiedDate = empty($item['modifiedDate']) ? $publishedDate : $item['modifiedDate'];
                $newItem['s_modified_date'] = Lib::fomartMetaDate($modifiedDate);
                $newItem['title'] = $item['title'];
                //$newItem['sapo'] = Lib::subString($item['postContent'], 200, '');
                $newItem['url'] = $this->getPostUrl($item['postId'], $item['slug']);
                $newItem['url_amp'] = $this->router->generate('news_detail_amp', ['slug' => $item['slug'], 'postId' => $item['postId']]);
                //$item['short_url'] = $this->router->generate('news_detail_short', ['id' => $item['articleid']]);
                if (!empty($imageSize)) {
                    $newItem['avatar'] = $imageService->getImageSize($item['avatar'], $imageSize);
                }
                $new_data[] = $newItem;
            }
        }

        return $new_data;
    }

    /**
     * Get post url
     * author: ThanhDT
     * date:   2018-05-22 10:06 PM
     * @param $postName
     * @param $postId
     * @return string
     */
    public function getPostUrl($postId, $postName)
    {
        $postUrl = $this->router->generate('news_detail', ['slug' => $postName, 'postId' => $postId]);

        return $postUrl;
    }

    /**
     * Content: Exchange RSS
     * author: TrieuNT
     * create date: 2018-10-23 10:11 AM
     * @param $data
     * @param POST_AVATAR_LIST_SIZE
     * @return array
     * @throws \Exception
     */

    public function exchangeArticleRss($data, $imageSize = Constants::POST_AVATAR_LIST_SIZE)
    {
        $new_data = [];
        if ($data) {
            $imageService = $this->imageService;
            $domain = $this->domain;
            foreach ($data as $item) {
                $articleId = $item['postId'];
                $publishedDate = isset($item['publishedDate']) ? $item['publishedDate'] : new \DateTime(Constants::MIN_DATE);
                $newItem = [];
                $newItem['id'] = $articleId;
                $newItem['slug'] = $item['slug'];
                $newItem['s_publish_time'] = Lib::fomartRssDate($publishedDate);
                //$item['s_day_time'] = isset($item['publishtime']) ? Lib::sw_get_current_weekday($item['publishtime']) : '';
                $newItem['title'] = $item['title'];
                $newItem['sapo'] = $item['sapo'];
                $newItem['content'] = isset($item['content']) ? $item['content'] : '';
                $newItem['avatar'] = $imageService->getImageSize(isset($articleAvatars[$articleId]) ? $articleAvatars[$articleId] : null, $imageSize);
                $newItem['url'] = $this->getPostUrl($articleId, $item['slug']);
                //$item['short_url'] = $this->router->generate('news_detail_short', ['id' => $item['articleid']]);
                $newItem['category'] = isset($item['cates']) ? json_decode($item['cates']) : [];
                $newItem['displayName'] = $item['fullname'];
                $newItem['guid'] = $domain . '/?p=' . $articleId;
                $new_data[] = $newItem;
            }
        }


        return $new_data;
    }

    /**
     * Content: Exchange article detail
     * author: TrieuNT
     * create date: 2018-10-23 10:11 AM
     * @param $data
     * @param string $imageSize
     * @return array
     * @throws \Exception
     */
    public function exchangeArticleDetail($data, $imageSize = Constants::POST_AVATAR_LIST_SIZE)
    {
        $new_data = [];
        $tagHtml = '';
        if ($data) {
            $imageService = $this->imageService;
            $articleId = $data['postId'];
            $publishedDate = isset($data['publishedDate']) ? $data['publishedDate'] : new \DateTime(Constants::MIN_DATE);
            $new_data['id'] = $articleId;
            $new_data['slug'] = $data['slug'];
            $new_data['authorId'] = $data['authorId'];
            $new_data['reviewId'] = $data['reviewId'];
            $new_data['s_publish_time'] = Lib::fomartDate($publishedDate);
            //$new_data['meta_publish_time'] = Lib::fomartMetaDate($publishedDate);
            $new_data['title'] = $data['title'];
            //$new_data['sapo'] = $data['sapo'];
            $new_data['content'] = $data['content'];
            $tagIdList = [];
            $arrTagsId = [];
            $arrCatesId = [];
            $tagCateList = [];
            if ($data['tags']) {
                $data['tags'] = json_decode($data['tags'], true);
                foreach ($data['tags'] as $tagId => $item) {
                    $tagIdList[$item['n']] = $tagId;
                    $arrTagsId[] = $tagId;
                    $tagHtml .= ($tagHtml == '') ? '<a href="' . $this->router->generate('news_tag', ['tagSlug' => $item['s']]) . '" rel="tag">' . $item['n'] . '</a>' : ', &nbsp;' . '<a href="' . $this->router->generate('news_tag', ['tagSlug' => $item['s']]) . '" rel="tag">' . $item['n'] . '</a>';
                }
            }
            if ($data['cates']) {
                $data['cates'] = json_decode($data['cates'], true);
                foreach ($data['cates'] as $cateId => $item) {
                    $tagCateList[$item['n']] = $cateId;
                    $arrCatesId[] = $cateId;
                    $tagHtml .= ($tagHtml == '') ? '<a href="' . $this->router->generate('news_cate', ['cateSlug' => $item['s']]) . '" rel="cate">' . $item['n'] . '</a>' : ', &nbsp;' . '<a href="' . $this->router->generate('news_cate', ['cateSlug' => $item['s']]) . '" rel="tag">' . $item['n'] . '</a>';
                }
            }
            $new_data['tag_cat'] = $tagHtml;
            $new_data['tags'] = $tagIdList;
            $new_data['cates'] = $tagCateList;
            $new_data['arrTagsId'] = $arrTagsId;
            $new_data['arrCatesId'] = $arrCatesId;
            $new_data['avatar'] = $imageService->getImageSize($data['avatar'], $imageSize);
            $new_data['url'] = $this->getPostUrl($articleId, $data['slug']);
            // Add SEO info
            $seo = array(
                'title' => empty($data['seoTitle']) ? $data['title'] : $data['seoTitle'],
                'description' => empty($data['seoMetadesc']) ? $data['sapo'] : $data['seoMetadesc'],
                'image' => $imageService->getImageSize($data['avatar'], Constants::IMAGE_SHARE_SIZE),
                'image_amp' => $imageService->getImageSize($data['avatar'], Constants::IMAGE_AMP_SEO_SIZE),
                'url' => $this->domain . $new_data['url'],
                'mobile_url' => $this->mobile . $new_data['url'],
                'publish_time' => Lib::fomartMetaDate($publishedDate),
                'og_type' => 'article',
                'is_home' => false,
                'author' => 'Unknown',
                'amp' =>  $this->mobile . $new_data['url']
            );
            $new_data['seo'] = $seo;
        }

        return $new_data;
    }

    /**
     * Content: Exchange article detail
     * author: HaiHS
     * createdDate: 2018-05-19 10:42
     * @param $data
     * @param string $imageSize
     * @return array
     * @throws \Exception
     */
    public function exchangePageDetail($data, $imageSize = Constants::POST_AVATAR_LIST_SIZE)
    {
        $new_data = [];
        if ($data) {
            $imageService = $this->imageService;
            $articleId = $data['pageId'];
            $publishedDate = isset($data['publishedDate']) ? $data['publishedDate'] : new \DateTime(Constants::MIN_DATE);

            $new_data['id'] = $articleId;
            $new_data['slug'] = $data['slug'];
            $new_data['description'] = $data['sapo'];
            $new_data['displayName'] = $data['fullname'];
            $new_data['userNicename'] = $data['userSlug'];
            $new_data['author'] = $data['authorId'];
            $new_data['reviewId'] = $data['reviewId'];
            $new_data['parentTitle'] = $data['parentTitle'] != null ? $data['parentTitle'] : '';
            $new_data['parentSlug'] = $data['parentSlug'] != null ? $data['parentSlug'] : '';
            $new_data['s_publish_time'] = Lib::fomartDate($publishedDate);
            $new_data['meta_publish_time'] = Lib::fomartMetaDate($publishedDate);
            $new_data['title'] = $data['title'];
            $new_data['sapo'] = $data['sapo'];
            $new_data['postContent'] = $data['content'];
            $new_data['avatar'] = $imageService->getImageSize($data['avatar'], $imageSize);
            $new_data['url'] = $this->getPageUrl($data['slug'],$articleId);
        }

        return $new_data;
    }

    /**
     * Get page url
     * author: ThanhDT
     * date:   2018-08-25 10:19 AM
     * @param $postName
     * @param $articleId
     * @return string
     */
    public function getPageUrl($postName,$articleId)
    {
        $postUrl = $this->router->generate('page_detail', ['pageSlug' => $postName, 'page_id' => $articleId]);
        return $postUrl;
    }

    public function exchangeAuthorData($author)
    {
        $new_data = [];
        if ($author) {
            $new_data['id'] = $author['userId'];
            $imageService = $this->imageService;
            $new_data['imageUrl'] = $imageService->getImageSize($author['avatar'], Constants::IMAGE_GALLERY_LIST_SIZE); //sprintf('/images/authors/author%d.jpg', $author['userId']);
            //$new_data['authorUrl'] = $this->router->generate('news_author', ['username' => $author['slug']]);
            $new_data['displayName'] = $author['fullname'];
            $new_data['description'] = $author['description'];
            $new_data['slug'] = $author['slug'];
        } else {
            $new_data['id'] = 0;
            $new_data['imageUrl'] = "/images/authors/author20.jpg";
            $new_data['authorUrl'] = "#";
            $new_data['displayName'] = "Unknown";
            $new_data['description'] = $author['description'];
            $new_data['slug'] = '';
        }

        return $new_data;
    }

    /**
     * Content: Exchange comment
     * author: ThangPD
     * createdDate: 2018-06-04 16:30
     */
    public function exchangeCommentRss($comments, $data)
    {
        $new_data = [];
        if ($comments) {
            foreach ($comments as $item) {
                $commentId = $item['commentId'];
                $publishedDate = isset($item['commentDateGmt']) ? $item['commentDateGmt'] : new \DateTime(Constants::MIN_DATE);
                $newItem = [];
                $newItem['id'] = $commentId;
                $newItem['s_publish_time'] = Lib::fomartRssDate($publishedDate);
                //$item['s_day_time'] = isset($item['publishtime']) ? Lib::sw_get_current_weekday($item['publishtime']) : '';
                $newItem['title'] = 'By: ' . $item['commentAuthor'];
                $newItem['sapo'] = Lib::subString($item['commentContent'], 200, '');
                $newItem['content'] = '<div class="comment-text-inner"><p>' . Lib::subString($item['commentContent'], 200, '') . '</p></div>';
                $newItem['displayName'] = $item['commentAuthor'];
                $newItem['url'] = "#comment-" . $commentId;
                $newItem['guid'] = $data['attach'] . "#comment-" . $commentId;
                $new_data[] = $newItem;
            }
        }


        return $new_data;
    }

    /**
     * Content: Exchange GalleryPhoto
     * author: TrieuNT
     * create date: 2018-10-19 09:36 AM
     * @param  $galleryData
     * @return array
     */

    public function ExchangeGalleryPhotoData($galleryData)
    {
        $new_data = [];
        if ($galleryData) {
            $new_data['galleryId'] = $galleryData['galleryId'];
            $new_data['title'] = $galleryData['title'];
            $new_data['postId'] = $galleryData['postId'];
        }
        return $new_data;
    }

    /**
     * Content: Exchange GalleryPhoto
     * author: TrieuNT
     * create date: 2018-10-19 09:44 AM
     * @param  $galleryData
     * @return array
     * modifier: AnhPT4
     * modified date:   2018-10-31 03:07 PM
     */

    public function ExchangeAllDetailGalleryPhotoData($galleryData, $sizeImages = Constants::IMAGE_GALLERY_LIST_SIZE_2, $sizeImages2 = '')
    {
        $new_data = [];
        if ($galleryData) {
            $imageService = $this->imageService;
            foreach ($galleryData as $key => $item) {
                $link = $this->router->generate('galleries_detail_photos', ['slug' => $item['slug'], 'galleryId' => $item['galleryId']]);
                $new_data[] = [
                    'id' => $item['id'],
                    'galleryId' => $item['galleryId'],
                    'imageId' => $item['imageId'],
                    'title' => $item['title'],
                    'url' => $imageService->getImageSize($item['url'], $sizeImages),
                    'url_large' => $sizeImages2 ? $imageService->getImageSize($item['url'], $sizeImages2) : '',
                    'url_amp' =>  $imageService->getImageSize($item['url'], Constants::POST_AVATAR_FOCUS_TOP1_SIZE),
                    'url_mobile' =>  $imageService->getImageSize($item['url'], Constants::MOBILE_IMAGE_DETAIL_NEWS_GALLERY_ITEMS),
                    'url_large_mobile' =>  $imageService->getImageSize($item['url'], Constants::MOBILE_IMAGE_DETAIL_NEWS_GALLERY),
                    'slug' => $item['slug'],
                    'link' => $link,
                    'link_view' => "<a href='".$link."'>http://".$_SERVER['HTTP_HOST'].$link."</a>"
                ];
            }
        }
        return $new_data;
    }

    /**
     * Format Image data array
     * author: ThanhDT
     * date:   2018-05-20 08:39 PM
     * @param $data
     * @param null $parentData
     * @return array
     */
    public function exchangeImagesArray($data, $parentData = null, $size = Constants::IMAGE_GALLERY_LIST_SIZE)
    {
        $newData = [];
        if ($data) {
            foreach ($data as $item) {
                $newItem = [];
                $imageService = $this->imageService;
                $newItem['imageId'] = $item['imageId'];
                $newItem['imageTitle'] = $item['title'];

                $newItem['imageUrl'] = $imageService->getImageSize($item['imageUrl'], $size);
                if ($parentData !== null) {
                    $item['dateParent'] = $parentData['dateParent'];
                    $item['nameParent'] = $parentData['nameParent'];
                    $item['statusParent'] = $parentData['statusParent'];
                }
                $newItem['imageDetailUrl'] = $this->getImageDetailUrl($item);
                $newData[] = $newItem;
            }
        }

        return $newData;
    }

    /**
     * Get Image url
     * author: ThanhDT
     * date:   2018-05-23 10:05 AM
     * @param $image
     * @return string
     */
    private function getImageDetailUrl($image)
    {
        if ($image['statusParent'] == Constants::POST_STATUS_PUBLISH) {
            $year = $image['dateParent']->format('Y');
            $month = $image['dateParent']->format('m');
            $imageUrl = $this->router->generate('news_detail_gallery', ['year' => $year, 'month' => $month, 'slug' => $image['nameParent'], 'postId' => $image['postId'], 'imageSlug' => $image['slug']]);
        } else {
            $imageUrl = "/?attachment_id=" . $image['imageId'];
        }
        return $imageUrl;
    }

    /**
     * Content: get new review
     * author: HaiHS
     * createdDate: 2018-05-21 13:47
     */
    public function exchangeReview($data)
    {
        $newReview = [];
        if ($data) {
            // $review = [];
            foreach ($data as $value) {
                //$review[$value['metaKey']] = $value['metaValue'];
                switch ($value['metaKey']) {
                    case Constants::REVIEW_HEAD:
                        $newReview['head'] = $value['metaValue'];
                        break;
                    case Constants::REVIEW_TOTAL:
                        $newReview['total'] = $value['metaValue'];
                        break;
                    case Constants::REVIEW_TYPE:
                        $newReview['type'] = $value['metaValue'];
                        break;
                    case Constants::REVIEW_USER_VIEW:
                        $newReview['userView'] = $value['metaValue'];
                        break;
                    case Constants::REVIEW_VOTE_COUNT:
                        $newReview['voteCount'] = $value['metaValue'];
                        break;
                    case Constants::REVIEW_SCHEMA_OPTION:
                        //TH get product
                        $schema_options = unserialize($value['metaValue']);
                        if (isset($schema_options['Product'])) {
                            $imageService = $this->imageService;
                            $newReview['product'] = $schema_options['Product'];
                            $newReview['product']['image'] = $imageService->getImageSize($newReview['product']['image']['url'], Constants::IMAGE_REVIEW_SIZE);
                        }
                        break;
                    case Constants::REVIEW_DESC:
                        $libWp = new WordPressLib();
                        $newReview['summary'] = $libWp->wpautop($value['metaValue']);
                        break;
                    case Constants::REVIEW_ITEM:
                        $newReview['items'] = unserialize($value['metaValue']);
                        break;
                }
            }
            if (!isset($newReview['userView'])) {
                $newReview['userView'] = 4;
            }
            if (!isset($newReview['voteCount'])) {
                $newReview['voteCount'] = 0;
            }
        }

        return $newReview;
    }

    /**
     * Exchange image detail
     * author: ThangPD
     * createdDate: 2018-05-22 14:00 PM
     * @param $data
     * @return array
     */
    public function exchangeImageDetail($data)
    {
        $new_data = [];
        if ($data) {
            $imageId = $data['imageId'];
            $publishedDate = isset($data['createdDate']) ? $data['createdDate'] : new \DateTime(Constants::MIN_DATE);

            $new_data['id'] = $imageId;
            $new_data['slug'] = $data['slug'];
            $new_data['s_publish_time'] = $publishedDate->format('F d, Y');
            $new_data['meta_publish_time'] = Lib::fomartMetaDate($publishedDate);
            $new_data['title'] = $data['title'];
            $new_data['sapo'] = $data['description'];
            //$new_data['postContent'] = (new WordPressLib())->wpautop($data['postContent']);
            $new_data['postParent'] = $data['postId'];
            $new_data['nameParent'] = $data['nameParent'];
            $new_data['dateParent'] = $data['dateParent'];
            $new_data['statusParent'] = $data['statusParent'];
            $new_data['width'] = $data['width'];
            $new_data['height'] = $data['height'];
            $imageService = $this->imageService;
            $new_data['image_url'] = $imageService->getFullImage($data['imageUrl']);

            if (empty($new_data['alt'])) {
                $new_data['alt'] = $data['title'];
            }

            if ($data['titleParent'] == null) {
                $new_data['title_parent'] = $data['title'];
                $new_data['post_url'] = '#';
                $new_data['image_detail_url'] = '';
            } else {
                $new_data['title_parent'] = $data['titleParent'];
                if ($data['statusParent'] == Constants::POST_STATUS_PUBLISH) {
                    $new_data['post_url'] = $this->getPostUrl($new_data['postParent'], $data['nameParent']);
                    $new_data['image_detail_url'] = $this->getImageDetailUrl($data);
                } else {
                    $new_data['post_url'] = '#';
                    $new_data['image_detail_url'] = '';
                }
            }
        }

        return $new_data;
    }

    /**
     * Exchange all image
     * author: ThangPD
     * createdDate: 2018-05-22 16:00 PM
     */
    public function exchangeImages($datas, $slug)
    {
        $new_data = [];
        if ($datas) {
            foreach ($datas as $data) {
                $articleId = $data['id'];
                $publishedDate = isset($data['publishedDate']) ? $data['publishedDate'] : new \DateTime();
                $publishedDateParent = isset($data['dateParent']) ? $data['dateParent'] : new \DateTime();
                $year = $publishedDateParent->format('Y');
                $month = $publishedDateParent->format('m');

                $new_data[$data['postName']]['id'] = $articleId;
                $new_data[$data['postName']]['slug'] = $data['postName'];
                $new_data[$data['postName']]['s_publish_time'] = $publishedDate->format('F d, Y');
                $new_data[$data['postName']]['meta_publish_time'] = Lib::fomartMetaDate($publishedDate);
                $new_data[$data['postName']]['title'] = $data['postTitle'];
                $new_data[$data['postName']]['sapo'] = Lib::subString($data['postContent'], 200, '');
                $new_data[$data['postName']]['postContent'] = (new WordPressLib())->wpautop($data['postContent']);

                $imageService = $this->imageService;
                $new_data[$data['postName']]['image_url'] = $imageService->getImageSize('/' . $data['image'], Constants::IMAGE_GALLERY_LIST_SIZE);
                $new_data[$data['postName']]['url'] = $this->router->generate('news_detail_gallery', ['year' => $year, 'month' => $month, 'slug' => $slug, 'imageSlug' => $data['postName']]);
            }
        }

        return $new_data;
    }

    /**
     * Exchange group box info
     * @param $data
     * @return mixed
     */
    public function exchangeGroupBox($data)
    {
        if (!$data) {
            return null;
        }
        $newData = [];
        $newData['title'] = $data['title'];
        if ($data['itemJson']) {
            $newData['items'] = json_decode($data['itemJson'], true);
        } else {
            $newData['items'] = [];
        }

        return $newData;
    }

    /**
     * exchange Array Gallery Photos
     * author: AnhPT4
     * date:   2018-10-30 01:37 PM
     * @param $data
     * @param string $imageSize
     * @return array
     */
    public function exchangeArrayGallery($data, $imageSize = Constants::POST_AVATAR_LIST_SIZE)
    {
        $new_data = [];
        if ($data) {
            $imageService = $this->imageService;
            foreach ($data as $index => $item) {
                $gallery_id = $item['galleryId'];
                $created_date = $item['createdDate'];
                $newItem = [];
                $newItem['id'] = $gallery_id;
                $newItem['count_images'] = $item['photoCount'];
                $newItem['created_date'] = Lib::fomartDate($created_date);
                $newItem['title'] = $item['title'];
                $newItem['url'] = $imageService->getImageSize($item['avatar'], $imageSize);
                $newItem['link'] = $this->router->generate('galleries_detail_photos', ['slug'=>$item['slug']?$item['slug']:'photos','galleryId'=>$item['galleryId']]);
                if(!empty($item['post_slug']))
                    $newItem['url_post'] = $this->router->generate('news_detail', ['slug' => $item['post_slug'], 'postId' => $item['postId']]);
                else
                    $newItem['url_post'] = '';

                $new_data[] = $newItem;
            }
        }
        return $new_data;
    }

    /**
     * author: TrieuNT
     * create date: 2018-10-31 10:59 AM
     * @param $data
     * @param $type
     * @param string $imageSize
     * @return array
     */
    public function exchangeArrayVideosGallery($data, $imageSize = Constants::POST_AVATAR_LIST_SIZE, $type = 0)
    {
        $new_data = [];
        if ($data) {
            $imageService = $this->imageService;
            foreach ($data as $index => $item) {
                $newItem = [];
                $newItem['video_id'] = $item['videoId'];
                $newItem['description'] = $item['description'];
                $newItem['created_date'] = Lib::fomartDate($item['createdDate']);
                $newItem['title'] = $item['title'];
                $newItem['avatar'] = $imageService->getImageSize($item['avatar'], $imageSize);
                $newItem['avatar_mobile'] = ($type == 0) ? $imageService->getImageSize($item['avatar'], Constants::MOBILE_IMAGE_DETAIL_NEWS_GALLERY_ITEMS) : $imageService->getImageSize($item['avatar'], Constants::MOBILE_HOME_VIDEO_ITEMS_SIZE) ;
                $newItem['url'] = $item['url'];
                $newItem['creator_id'] = $item['creatorId'];
                $newItem['link'] = $this->router->generate('galleries_detail_videos', ['slug'=>Lib::convertToSlug($item['title']),'galleryId'=>$item['videoId']]);
                $newItem['firstAvatar'] = ($index == 0) ? $imageService->getImageSize($item['avatar'], Constants::VIDEO_HOME_FIRST) : '';
                $newItem['firstAvatarMobile'] = ($index == 0) ? ( ($type == 0 ) ? $imageService->getImageSize($item['avatar'], Constants::MOBILE_VIDEO_AVATAR_FIRST_SIZE) : $imageService->getImageSize($item['avatar'], Constants::MOBILE_HOME_VIDEO_FIRST_SIZE)) : '';
                $new_data[] = $newItem;
            }
        }
        return $new_data;
    }

    /**
     * author: TrieuNT
     * create date: 2018-11-02 10:44 AM
     * @param $data
     * @param $imageSize
     * @return array
     */

    public function exchangeArrayVideoDetail($data, $imageSize = Constants::VIDEO_AVATAR_FIRST_SIZE)
    {
        $newData = [];
        if ($data) {
            $imageService = $this->imageService;
            $newData['video_id'] = $data['videoId'];
            $newData['description'] = $data['description'];
            $newData['created_date'] = Lib::fomartDate($data['createdDate']);
            $newData['title'] = $data['title'];
            $newData['avatar'] = $imageService->getImageSize($data['avatar'], $imageSize);
            $newData['avatar_mobile'] = $imageService->getImageSize($data['avatar'], Constants::MOBILE_VIDEO_AVATAR_FIRST_SIZE);
            $newData['url'] = $data['url'];
            $newData['creator_id'] = $data['creatorId'];
            $newData['link'] = $this->router->generate('galleries_detail_videos', ['slug'=>Lib::convertToSlug($data['title']),'galleryId'=>$data['videoId']]);
            $newData['older_time'] = (isset($data['createdDate'])) ? $data['createdDate']->format('Y-m-d H:i:s') : Date('Y-m-d H:i:s');
        }
        return $newData;
    }

    /**
     * author: TrieuNT
     * create date: 2018-11-08 04:09 PM
     * @param $data
     * @param $imageSize
     * @return array
     */
    public function exchangeArraySearchPost($data, $imageSize = Constants::MOBILE_IMAGE_DETAIL_NEWS_GALLERY)
    {
        $mainData = [];
        if ($data) {
            $imageService = $this->imageService;
            foreach ($data as $key => $dt) {
                $publishedDate = isset($dt['_source']['published_date']) ? new \DateTime($dt['_source']['published_date']) : new \DateTime(Constants::MIN_DATE_SEARCH);
                $publishedDate->setTimezone(new \DateTimeZone('Asia/Kolkata'));
                $mainData[$key] = isset($dt['_source']) ? $dt['_source'] : [];
                $mainData[$key]['url'] = $this->router->generate('news_detail', ['slug' => $dt['_source']['slug'], 'postId' => $dt['_source']['post_id']]);
                $mainData[$key]['images'] = $imageService->getImageSize($dt['_source']['avatar'], $imageSize);
                $mainData[$key]['published_date'] = Lib::fomartDate($publishedDate);
            }
        }
        return $mainData;
    }
}
