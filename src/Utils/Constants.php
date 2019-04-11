<?php
/**
 * Created by PhpStorm.
 * User: ThanhDT
 * Date: 5/17/2018
 * Time: 1:38 PM
 */
namespace App\Utils;

class Constants
{
    // Memcached Cache provider
    /*const SERVER_CACHE_ARTICLE = 'memcachedArticle';
    const SERVER_CACHE_FULL_PAGE = 'memcachedCachePage';*/
    // Redis Cache provider
    const SERVER_CACHE_ARTICLE = 'redisArticle';
    const SERVER_CACHE_FULL_PAGE = 'redisCachePage';
    // Sync view using only redis
    const SERVER_CACHE_UPDATE_VIEW = 'redisUpdateView';
    // Exchange min date
    const MIN_DATE = '1970-01-01';
    const FEATURED_STORIES_CAT_ID = 1836;

    const EMAIL_SUPPORT_CUSTOMER = 'tunn@daivietgroup.com';

    // Sync queue constants
    // const QuizKeyQueue = 'Queue:Quiz:playcount1';
    const QUIZ_KEY_QUEUE = 'Queue:Quiz:playcount1';
    //const FacebookViewKeyQueue = 'Queue:Facebook:Views';
    const FACEBOOK_VIEW_KEY_QUEUE = 'Queue:Facebook:Views';
    //const ArticleViewKeyQueue = 'Queue:Article:view1';
    const ARTICLE_VIEW_QUEUE = 'Queue:Article:view1';
    //const FollowingKeyQueue = 'Queue:Follow:action';
    const FOLLOWING_KEY_QUEUE = 'Queue:Follow:action';
    const COOKIE_NAME = 'dXNlcl92b3Rl_';
    //const FacebookLikeKeyQueue = 'Queue:Facebook:Likes';
    const FACEBOOK_LIKE_KEY_QUEUE = 'Queue:Facebook:Likes';


    // Enum value
    const FOCUS_HOME = 1;
    const FOCUS_CATEGORY = 2;

    // Mobile suffix
    const MOBILE = 'MOBILE:';
    const MOBILE_AMP = 'MOBILE:AMP:';
    const WEB_AMP = 'WEB:AMP:';

    // Images size
    const POST_AVATAR_LIST_SIZE = '360x201';
    const POST_AVATAR_HOME_CATE_SIZE = '165x101';
    const POST_AVATAR_HOME_CATE_FOCUS_SIZE = '555x312';
    const POST_AVATAR_FOCUS_TOP1_SIZE = '850x476';
    const POST_AVATAR_FOCUS_TOP2_SIZE = '531x236';
    const IMAGE_GALLERY_LIST_SIZE = '150x150';
    const IMAGE_GALLERY_LIST_SIZE_2 = '172x96';
    const IMAGE_DETAIL_NEWS = '262x147';
    const VIDEO_AVATAR_FIRST_SIZE = '751x422';
    const IMAGE_THUMB_POPUP = '186x104';
    const IMAGE_LARGE_POPUP = '974x551';
    const VIDEO_HOME_FIRST = '654x366';
    const IMAGE_LARGE_AMP = '320x600';
    const IMAGE_MOST_VIEW_SIZE = '242x135';
    const IMAGE_SHARE_SIZE = '620x324';
    const IMAGE_AMP_SEO_SIZE = '1200x675';

    // Homepage
    const HOMEPAGE_FOCUS_POST_LIMIT = 5;
    // TrieuNT
    const HOMEPAGE_MOBILE_FOCUS_POST_LIMIT = 4;
    const HOMEPAGE_LASTEST_LIMIT = 8;
    const HOMEPAGE_LASTEST_AJAX_LIMIT = 12;
    const HOME_FOCUS_POST_CENTER_COUNT = 1;
    const CACHE_HOMEPAGE_FOCUS = 'CACHE:HOMEPAGE:FOCUS';
    const CACHE_HOME_AMP_FOCUS = 'CACHE:AMP:HOME:FOCUS';
    const HOMEPAGE_LASTEST_PAGE_SIZE = 8;
    const HOMEPAGE_LASTEST_PAGE_INDEX = 0;
    const CACHE_AMP_LASTEST_PAGE = 'CACHE:AMP:LASTEST:PAGE:%s';
    const CACHE_HOMEPAGE_LASTEST_PAGE = 'CACHE:HOMEPAGE:LASTEST:PAGE_V2:%s';
    const CACHE_HOMEPAGE_LASTEST_TIMESTAMP = 'CACHE:HOMEPAGE:LASTEST:TIMESTAMP_V2:%s:%s';
    const HOME_MOST_VIEW_POST_LIMIT = 5;
    const DETAIL_MOST_VIEW_POST_LIMIT = 6;
    const HOME_MOST_VIEW_LAST_DAY = 7;
    const HOME_MOST_VIEW_LAST_DAY_2 = 60;
    const CACHE_HOME_MOST_VIEW = 'CACHE:HOME:MOST_VIEW';
    const CACHE_AMP_MOST_VIEW = 'CACHE:AMP:DETAIL:MOST_VIEW';
    const CACHE_HOME_AMP_MOST_VIEW = 'CACHE:AMP:HOME:MOST_VIEW';
    const CACHE_AMP_CATEGORY_PAGE = 'CACHE:AMP:CATEGORY:PAGE:%s:%s';
    const CACHE_AMP_CATE_LIST = 'CACHE:AMP:CATE:LIST:%s';
    const CACHE_HOMEPAGE_CATE_LIST = 'CACHE:HOMEPAGE:CATE:LIST:%s';
    const HOME_FEATURED_POSTS_LIMIT = 6;
    const HOME_OTHER_CATE_POSTS_LIMIT = 4;
    const OPTION_TRENDING_DATA = 'options_trending_data';
    const CACHE_HOME_VIDEOS_VIEW = 'CACHE:HOME:VIDEOS_VIEW';
    const LIMIT_TRENDING_HOME = 5;
    const DETAIL_LASTEST_NEWS = 4;
    const CACHE_DETAIL_TOP_LASTEST = 'CACHE:DETAIL:TOP:LASTEST';
    const CACHE_DETAIL_PAGE_TOP_LASTEST = 'CACHE:DETAIL:PAGE:TOP:LASTEST';
    const CACHE_AMP_TOP_LASTEST = 'CACHE:AMP:TOP:LASTEST';

    // Category
    const FEATURED_STORIES_CATE_ID = 1836;
    const CARS_REVIEW_CATE_ID = 228;
    const BIKES_REVIEW_CATE_ID = 229;
    const CATE_ID_CAR_NEWS = 11476;
    const CATE_ID_MOTO_NEWS = 11471;
    // Category List
    const CACHE_CATEGORY_ALL_SLUG = '_CACHE:CATEGORY:ALL:SLUG';
    const CACHE_CATEGORY_ALL_SLUG_CHILD = '_CACHE:CATEGORY:ALL:SLUG:CHILD';
    const CACHE_CATEGORY_ALL_CHILD = '_CACHE:CATEGORY:ALL:CHILD';
    const CACHE_CATEGORY_ALL_ID = '_CACHE:CATEGORY:ALL:ID';
    const CACHE_CATEGORY_ALL_TERM_ID = '_CACHE:CATEGORY:ALL:TERM:ID';
    const CACHE_CATEGORY_ALL_PARENT = '_CACHE:CATEGORY:ALL:PARENT';
    const CACHE_CATEGORY_ID = '_CACHE:CATEGORY:ID:%s';

    const CACHE_CATEGORY_LASTEST_PAGE = 'CACHE:CATEGORY:LASTEST:PAGE:%s';
    const CACHE_CATEGORY_LASTEST_TIMESTAMP = 'CACHE:CATEGORY:LASTEST:TIMESTAMP:%s:%s';

    const TABLE_POST_BY_CATE_PAGE = '_CACHE:POST:CATE:%s:PAGE:%s';
    const TABLE_POST_BY_CATE_COUNT = '_CACHE:POST:CATE:%s:COUNT';
    const TABLE_POST_CATE_HOMEBOX = '_CACHE:POST:CATE:HOMEBOX:%s';
    const TABLE_POST_BY_TAG_PAGE = '_CACHE:POST:TAG:%s:PAGE:%s';
    const TABLE_POST_BY_TAG_COUNT = '_CACHE:POST:TAG:%s:COUNT';

    // Constant config
    const PAGE_SIZE = 18;
    const START_PAGE = 1;
    const PAGE_SIZE_MOBILE = 10;


    // Tag
    const TABLE_ARTICLE_BY_TAG_PAGE = '_CACHE:POST:TAG:%s:PAGE:%s';

    const TABLE_TAG_ALL_SLUG = '_CACHE:TAG:ALL:SLUG';
    const TABLE_TAG_BY_SLUG = '_CACHE:TAG:SLUG:%s';
    const TABLE_TAG_ALL_ID = '_CACHE:TAG:ALL:ID';

    const CACHE_TAG_LASTEST_PAGE = 'CACHE:TAG:LASTEST:PAGE:%s';
    const CACHE_TAG_LASTEST_TIMESTAMP = 'CACHE:TAG:LASTEST:TIMESTAMP:%s:%s';

    // Author
    const TABLE_AUTHOR_USERNICENAME = '_CACHE:AUTHOR:USERNICENAME:%s';
    const TABLE_AUTHOR_USERID = '_CACHE:AUTHOR:USERID:%s';

    const CACHE_AUTHOR_LASTEST_PAGE = 'CACHE:AUTHOR:LASTEST:PAGE:%s';
    const CACHE_AUTHOR_LASTEST_TIMESTAMP = 'CACHE:AUTHOR:LASTEST:TIMESTAMP:%s:%s';

    // Post detail - TrieuNT added
    const TABLE_ARTICLE_DETAIL_BY_ID = '_CACHE:ARTICLE:DETAIL:ID:%s';
    const TABLE_ARTICLE_PREVIEW_BY_ID = '_CACHE:ARTICLE:PREVIEW:ID:%s';
    const TABLE_ARTICLE_DETAIL_URL_BY_SLUG = '_CACHE:ARTICLE:DETAIL:URL:SLUG:%s';
    const TABLE_ARTICLE_GALLERY_PHOTO_POSTID = '_CACHE:ARTICLE:GALLERY:PHOTO:POSTID:%s';
    const TABLE_ARTICLE_DETAIL_GALLERY_ID = '_CACHE:ARTICLE:DETAIL:GALLERY:ID:%s';
    const TABLE_ARTICLE_RELATED_BY_TAG = '_CACHE:ARTICLE:RELATED:TAG:%s:%s';
    const TABLE_ARTICLE_RELATED_BY_CATE = '_CACHE:ARTICLE:RELATED:CATE:%s:%s';
    const PAGE_SIZE_ARTILCE_RELATED_TAG = 6;
    const MOBILE_PAGE_SIZE_ARTILCE_RELATED_TAG = 4;
    const ARTICLE_RELATED_TAG_AVATAR_LIST_SIZE = '190x128';
    const TABLE_ARTICLE_FEATURED_STORIES_POST_ID = '_CACHE:ARTICLE:FEATURED:STORIES:POST:ID:%s:%s';
    const TABLE_ARTICLE_DETAIL_BY_SLUG_AMP = '_CACHE:ARTICLE:DETAIL:SLUG:AMP:%s';
    const TABLE_ARTICLE_DETAIL_BY_SLUG = '_CACHE:ARTICLE:DETAIL:SLUG:%s';
    const TABLE_ARTICLE_DETAIL_BY_ID_AMP = '_CACHE:ARTICLE:DETAIL:ID:AMP:%s';
    const TABLE_ARTICLE_DETAIL_URL_BY_SLUG_AMP = '_CACHE:ARTICLE:DETAIL:URL:SLUG:AMP:%s';
    const TABLE_ARTICLE_DETAIL_SHORT_BY_SLUG = '_CACHE:ARTICLE:DETAIL:SHORT:BY:SLUG:%s';
    const TABLE_ARTICLE_DETAIL_URL = '_CACHE:ARTICLE:DETAIL:URL:%s';
    const TABLE_ARTICLE_CHECK_POST_LINK = '_CACHE:ARTICLE:CHECK:POST:%s';
    const TABLE_ARTICLE_CHECK_PAGE_LINK = '_CACHE:ARTICLE:CHECK:PAGE:%s';
    const TABLE_ARTICLE_CHECK_PAGE_LINK_V2 = '_CACHE:ARTICLE:CHECK:SUB:PAGE:%s:%s';
    const TABLE_ARTICLE_CHECK_PAGE_LINK_V3 = '_CACHE:ARTICLE:CHECK:SUB:PAGE:SUB:%s:%s:%s';

    //RSS - TrieuNT added
    const FEED_RSS_LIMIT = 18;
    const TABLE_RSS_FEED = '_CACHE:RSS:FEED';
    const TABLE_RSS_CATE_FEED = '_CACHE:RSS:CATE:FEED:CATE%s';
    const TABLE_RSS_TAGS_FEED = '_CACHE:RSS:TAGS:FEED:SLUG%s';
    const TABLE_RSS_AUTHOR_FEED = '_CACHE:RSS:AUTHOR:FEED:%s';

    /*SITE MAP TrieuNT added*/
    const TABLE_ARTICLE_SITE_MAP = '_CACHE:SITE_MAP:HOME';
    const TABLE_ARTICLE_SITE_MAP_BY_DAY = '_CACHE:SITE_MAP:BY_DAY';
    const TABLE_ARTICLE_SITE_MAP_CATEGORY = '_CACHE:SITE_MAP:CATEGORY';
    const TABLE_ARTICLE_SITE_MAP_AUTHOR = '_CACHE:SITE_MAP:AUTHOR';
    const TABLE_ARTICLE_SITE_MAP_ARTICLE = '_CACHE:SITE_MAP:ARTICLE_NEW:%s';
    const TABLE_ARTICLE_SITE_MAP_TAGS = '_CACHE:SITE_MAP:ARTICLE_TAGS';
    const TABLE_ARTICLE_SITE_MAP_NEWS = '_CACHE:SITE_MAP:ARTICLE_NEWS';
    const TABLE_POST_URL_BY_SLUG = '_CACHE:POST:URL:SLUG:%s';
    const TABLE_PAGE_BY_SLUG = '_CACHE:PAGE:SLUG:%s';
    // News Sitemap
    const TABLE_ARTICLE_SITEMAP_NEWS = '_CACHE:SITE_MAP:HOME_NEWS';
    const TABLE_ARTICLE_SITEMAP_NEWS_DATE = '_CACHE:SITE_MAP:NEWS_DATE:%s';


    // galleries Photos
    const CACHE_PHOTOS_LASTEST_PAGE = 'CACHE:PHOTOS:LASTEST:PAGE:%s';
    const CACHE_PHOTOS_PAGE = 'CACHE:PHOTOS:ALL:PAGE:%s';
    const CACHE_PHOTOS_GALLERY_IMAGES_DATA = 'CACHE:PHOTOS:GALLERY_IMAGES_DATA:%s';

    //galleries videos - TrieuNT
    const CACHE_VIDEOS_LASTEST_PAGE = 'CACHE:VIDEOS:LASTEST:PAGE:%s';
    const CACHE_VIDEOS_PAGE = 'CACHE:VIDEOS:ALL:PAGE:%s';
    const CACHE_VIDEOS_DETAIL_BY_ID = 'CACHE:VIDEOS:DETAIL:ID:%s';
    const PAGE_SIZE_VIDEOS = 12;
    const MIN_DATE_SEARCH = '2018-01-01';

    /************************************ MOBILE ********************************************************/
    const MOBILE_HOME_OTHER_CATE_POSTS_LIMIT = 6;

    const MOBILE_IMAGE_DETAIL_NEWS_TAGS = '192x108';
    const MOBILE_IMAGE_DETAIL_NEWS_FEATURED = '130x73';
    const MOBILE_IMAGE_DETAIL_NEWS_GALLERY = '398x223';
    const MOBILE_IMAGE_DETAIL_NEWS_GALLERY_ITEMS = '97x54';
    const MOBILE_VIDEO_AVATAR_FIRST_SIZE = '414x233';

    const MOBILE_POST_AVATAR_LIST_SIZE = '304x171';
    const MOBILE_IMAGE_THUMB_POPUP = '72x40';

    const MOBILE_IMAGE_HOME_TOPIC = '480x360';
    const MOBILE_IMAGE_HOME_LATEST_NEWS = '464x260';
    const MOBILE_IMAGE_HOME_FEATURED_STORIES = '130x73';

    const MOBILE_HOME_VIDEO_FIRST_SIZE = '398x224';
    const MOBILE_HOME_VIDEO_ITEMS_SIZE = '97x59';

    // SEO config
    const TITLE_SEO_PAGING_FORMAT = '%s - Page %d of %s';

    // SEO TITLE
    const SEO_TITLE_CONTACT = 'Contact';
    const SEO_TITLE_ABOUT = 'ABOUT';
    const SEO_TITLE_PRIVACY_POLICY = 'Privacy policy';
    const SLUG_PRIVACY_POLICY = 'privacy-policy';
    const CACHE_HOMEPAGE_PRIVACY_POLICY = 'CACHE:HOMEPAGE:PRIVACY:POLICY';

    // Crawler log
    const ALLOW_TRACKING_CRAWLER = "AllowTrackingCrawler";
    const CRAWLER_IP_LIST = "CrawlerIPList";
    const IP_BLACKLIST_HASH = "IPBlacklistHash";
    
    // SEO build format Des + title
    const BUILD_FORM_SEO_TITLE_AUTHOR = '%s, Author at indianautosblog.com';
    const BUILD_FORM_SEO_META_DES_CATE = 'Latest news on %s - Indianautosblog - car and bike news, reviews, new and upcoming launches.';
    const BUILD_FORM_SEO_META_DES_TAG = 'Read all the news about %s Indianautosblog provides in-depth analysis of cars, bikes, new and upcoming launches.';
    const BUILD_FORM_SEO_META_DES_IMAGES = 'IndianAutosBlog photo gallery features the best car and bike images in India. See launch images, event and show gallery, news coverage and spy pictures for upcoming cars and bikes in India - page %d';
    const BUILD_FORM_SEO_META_DES_IMAGES_DETAIL = '%s, See all images';
    const BUILD_FORM_SEO_META_DES_AUTHOR = 'Read latest articles about cars and bikes by %s at indianautosblog.com';
}
