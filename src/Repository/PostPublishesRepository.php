<?php

namespace App\Repository;

use App\Entity\PostPublishes;
use App\Entity\PostDatas;
use App\Entity\AdminUsers;
use App\Entity\PostsTags;
use App\Entity\PostsCates;
use App\Utils\Constants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PostPublishes|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostPublishes|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostPublishes[]    findAll()
 * @method PostPublishes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostPublishesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PostPublishes::class);
    }

    /**
     * Get top focus posts
     * author: ThanhDT
     * date:   2018-10-17 01:38 PM
     * @param $focus
     * @param $limit
     * @param $langId
     * @return array
     * @throws \Exception
     */
    public function getTopFocusPosts($focus, $limit, $langId = 0)
    {
        return $this->createQueryBuilder('a')
            ->select('a.postId, a.title, a.slug, a.sapo, a.publishedDate, a.avatar')
            ->where('a.langId = :langId AND a.focusStatus = :focus AND a.publishedDate <= :currentDate')
            ->setParameters([':langId' => $langId, ':focus' => $focus, ':currentDate' => new \DateTime()])
            ->orderBy('a.publishedDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Get lastest posts by timestamp
     * author: ThanhDT
     * date:   2018-10-23 04:08 PM
     * @param $limit
     * @param $lastPublishedStamp
     * @param $excludePostIds
     * @param int $langId
     * @return array
     */
    public function getLastestPosts($limit, $lastPublishedStamp, $excludePostIds = null, $langId = 0)
    {
        $query = $this->createQueryBuilder('a')
            ->select('a.postId, a.title, a.slug, a.sapo, a.publishedDate, a.publishedTimestamp, a.avatar, u.fullname, u.slug authorSlug')
            ->leftJoin(AdminUsers::class, 'u', 'WITH', 'a.authorId = u.userId');
        $parameters = [];
        if ($excludePostIds) {
            $query->where('a.postId NOT IN (:postIds)');//->setParameter(':postIds', $excludePostIds);
            $parameters[':postIds'] = $excludePostIds;
        }
        $query->andWhere('a.cateId NOT IN (:cateIds) AND a.langId = :langId AND a.publishedTimestamp <= :currentDate');
        $parameters[':currentDate'] = $lastPublishedStamp;
        $parameters[':langId'] = $langId;
        $parameters[':cateIds'] = [Constants::FEATURED_STORIES_CATE_ID, Constants::CARS_REVIEW_CATE_ID, Constants::BIKES_REVIEW_CATE_ID];
        $posts = $query->setParameters($parameters)
            ->orderBy('a.publishedTimestamp', 'DESC')
            ->addOrderBy('a.postId', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return $posts;
    }


    /**
     * Get lastest posts by timestamp
     * author: ThanhDT
     * date:   2018-10-23 04:08 PM
     * @param $limit
     * @param $lastPublishedStamp
     * @param $excludePostIds
     * @param int $langId
     * @return array
     */
    public function getLastestPostsAmp($limit, $lastPublishedStamp, $page, $excludePostIds = null, $langId = 0)
    {
        $start = ($page - 1) * $limit;
        $query = $this->createQueryBuilder('a')
            ->select('a.postId, a.title, a.slug, a.sapo, a.publishedDate, a.publishedTimestamp, a.avatar, u.fullname, u.slug authorSlug')
            ->leftJoin(AdminUsers::class, 'u', 'WITH', 'a.authorId = u.userId');
        $parameters = [];
        if ($excludePostIds) {
            $query->where('a.postId NOT IN (:postIds)');
            $parameters[':postIds'] = $excludePostIds;
        }
        $query->andWhere('a.cateId NOT IN (:cateIds) AND a.langId = :langId');
        $parameters[':langId'] = $langId;
        $parameters[':cateIds'] = [Constants::FEATURED_STORIES_CATE_ID, Constants::CARS_REVIEW_CATE_ID, Constants::BIKES_REVIEW_CATE_ID];
        $posts = $query->setParameters($parameters)
            ->setFirstResult($start)
            ->orderBy('a.publishedTimestamp', 'DESC')
            ->addOrderBy('a.postId', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return $posts;
    }

    /**
     * Get top posts in cate
     * author: ThanhDT
     * date:   2018-10-19 03:01 PM
     * @param $cateId
     * @param $limit
     * @param int $langId
     * @param $ignoreLastestIds
     * @return array
     * @throws \Exception
     */

    public function getTopPostsInCate($cateId, $limit, $langId = 0)
    {
        $queryBuilder = $this->createQueryBuilder('a');
        /*if ($ignoreLastestIds) {
            return $queryBuilder->select('a.postId, a.title, a.slug, a.sapo, a.publishedDate, a.avatar, u.fullname, u.slug authorSlug')
                ->innerJoin(PostsCates::class, 'c', 'WITH', 'a.postId = c.postId')
                ->leftJoin(AdminUsers::class, 'u', 'WITH', 'a.authorId = u.userId')
                ->where('a.langId = :langId AND c.cateId = :cateId AND a.publishedDate <= :currentDate')
                ->andWhere($queryBuilder->expr()->notIn('a.postId', ':postIds'))
                ->setParameters([':langId' => $langId, ':cateId' => $cateId, ':currentDate' => new \DateTime(), ':postIds' => $ignoreLastestIds])
                ->orderBy('a.publishedDate', 'DESC')
                ->setMaxResults($limit)
                ->getQuery()
                ->getArrayResult();
        } else {*/
            return $queryBuilder->select('a.postId, a.title, a.slug, a.sapo, a.publishedDate, a.avatar, u.fullname, u.slug authorSlug')
                //->innerJoin(PostsCates::class, 'c', 'WITH', 'a.postId = c.postId')
                ->leftJoin(AdminUsers::class, 'u', 'WITH', 'a.authorId = u.userId')
                ->where('a.langId = :langId AND a.cateId = :cateId AND a.publishedDate <= :currentDate')
                ->setParameters([':langId' => $langId, 'cateId' => $cateId, ':currentDate' => new \DateTime()])
                ->orderBy('a.publishedDate', 'DESC')
                ->setMaxResults($limit)
                ->getQuery()
                ->getArrayResult();
        /*}*/
    }


    /**
     * Get most view posts
     * author: ThanhDT
     * date:   2018-10-23 07:27 PM
     * @param $limit
     * @param $lastDays
     * @param int $langId
     * @return array
     */
    public function getMostViewPosts($limit, $lastDays, $langId = 0)
    {
        $lastDate = strtotime("-$lastDays days");
        return $this->createQueryBuilder('a')
            ->select('a.postId, a.title, a.slug, a.sapo, a.publishedDate, a.publishedTimestamp, a.viewCount, a.avatar, a.authorId, u.fullname, u.slug as authorSlug')
            ->leftJoin(AdminUsers::class, 'u', 'WITH', 'a.authorId = u.userId')
            ->where('a.langId = :langId AND a.publishedTimestamp BETWEEN :lastDate AND :currentDate')
            ->setParameters([':langId' => $langId, ':lastDate' => $lastDate, ':currentDate' => time()])
            ->orderBy('a.viewCount', 'DESC')
            ->addOrderBy('a.publishedTimestamp', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();
    }


    // ---------------------------AnhPT4----------------------------------------------------------------------------

    /**
     * get Articles in categories count
     * author: ThanhDT
     * date:   2018-07-11 08:09 PM
     * @param $cateId
     * @param int $langId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getArticleInCateCount($cateId, $langId = 0)
    {
        $count = $this->createQueryBuilder('a')
            ->select('count(a.postId)')
            ->innerJoin('App:PostsCates', 'c', 'WITH', 'c.postId = a.postId')
            ->where('a.langId = :langId AND c.cateId IN (:cateId) AND a.publishedDate <= :currentDate')
            ->setParameters([':langId' => $langId, ':cateId' => $cateId, ':currentDate' => new \DateTime()])
            ->getQuery()->getSingleScalarResult();

        return $count;
    }

    /**
     * get Articles in categories by paging
     * author: ThanhDT
     * date:   2018-07-11 08:09 PM
     * @param $cateId
     * @param $pageIndex
     * @param $pageSize
     * @param int $langId
     * @return array
     */
    public function getArticleInCatePaging($cateId, $pageIndex, $pageSize, $langId = 0)
    {
        $startIndex = ($pageIndex - 1) * $pageSize;
        $data = $this->createQueryBuilder('a')
            ->select('a.postId, a.title, a.slug, a.sapo, a.publishedDate,a.publishedTimestamp,a.avatar, u.slug as authorSlug, u.fullname')
            ->innerJoin(PostsCates::class, 'c', 'WITH', 'c.postId = a.postId')
            ->leftJoin(AdminUsers::class, 'u', 'WITH', 'a.authorId = u.userId')
            ->where('a.langId = :langId AND c.cateId IN (:cateId) AND a.publishedTimestamp <= :currentDate')
            ->setParameters([':langId' => $langId, ':cateId' => $cateId, ':currentDate' => time()])
            ->setFirstResult($startIndex)
            ->orderBy('a.publishedTimestamp', 'DESC')
            ->setMaxResults($pageSize)
            ->getQuery()->getArrayResult();

        return $data;
    }

    /**
     * get data Post in categories by paging ajax
     * author: AnhPT4
     * date:   2018-10-24 03:25 PM
     * @param $cateIds
     * @param $limit
     * @param $lastPublishedStamp
     * @param null $excludePostIds
     * @param int $langId
     * @return array
     */
    public function getArticleInCateTimestamp($cateIds, $limit, $lastPublishedStamp, $excludePostIds = null, $langId = 0)
    {
        $query = $this->createQueryBuilder('a')
            ->select('a.postId, a.title, a.slug, a.sapo, a.publishedDate, a.publishedTimestamp, a.avatar, u.fullname, u.slug authorSlug')
            ->distinct()
            ->innerJoin(PostsCates::class, 'c', 'WITH', 'c.postId = a.postId')
            ->leftJoin(AdminUsers::class, 'u', 'WITH', 'a.authorId = u.userId');
        $parameters = [];
        if ($excludePostIds) {
            $query->where('a.postId NOT IN (:postIds)');//->setParameter(':postIds', $excludePostIds);
            $parameters[':postIds'] = $excludePostIds;
        }
        $query->andWhere('c.cateId IN (:cateIds) AND a.langId = :langId AND a.publishedTimestamp <= :currentDate');
        $parameters[':currentDate'] = $lastPublishedStamp;
        $parameters[':langId'] = $langId;
        $parameters[':cateIds'] = $cateIds;
        $posts = $query->setParameters($parameters)
            ->orderBy('a.publishedTimestamp', 'DESC')
            ->addOrderBy('a.postId', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return $posts;
    }

    /**
     * Get count Article in Tag
     * author: ThanhDT
     * date:   2018-07-12 01:25 PM
     * @param $tagId
     * @param int $langId
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getArticleInTagCount($tagId, $langId = 0)
    {
        $count = $this->createQueryBuilder('a')
            ->select('count(a.postId)')
            ->innerJoin('App:PostsTags', 'c', 'WITH', 'c.postId = a.postId')
            ->where('a.langId = :langId AND c.tagId IN (:tagId) AND a.publishedDate <= :currentDate')
            ->setParameters([':langId' => $langId, ':tagId' => $tagId, ':currentDate' => new \DateTime()])
            ->getQuery()->getSingleScalarResult();

        return $count;
    }

    /**
     * get Articles in Tag by paging
     * author: ThanhDT
     * date:   2018-07-11 08:09 PM
     * @param $tagIds : Array
     * @param $pageIndex
     * @param $pageSize
     * @param int $langId
     * @return array
     */
    /*public function getArticleInTagPaging($tagIds, $pageIndex, $pageSize, $langId = 0)
    {
        $startIndex = ($pageIndex - 1) * $pageSize;
        $data = $this->createQueryBuilder('a')
            ->select('a.postId, a.title, a.slug, a.sapo, a.publishedDate,a.publishedTimestamp,a.avatar, u.slug as authorSlug, u.fullname')
            ->innerJoin(PostsTags::class, 'c', 'WITH', 'c.postId = a.postId')
            ->leftJoin(AdminUsers::class, 'u', 'WITH', 'a.authorId = u.userId')
            ->where('a.langId = :langId AND c.tagId IN (:tagIds) AND a.publishedTimestamp <= :currentDate')
            ->setParameters([':langId' => $langId, ':tagIds' => $tagIds, ':currentDate' => time()])
            ->setFirstResult($startIndex)
            ->orderBy('a.publishedTimestamp', 'DESC')
            ->setMaxResults($pageSize)
            ->getQuery()->getArrayResult();

        return $data;
    }*/

    /**
     * get Post in Tag by paging ajax
     * author: AnhPT4
     * date:   2018-10-24 03:53 PM
     * @param $tagId
     * @param $limit
     * @param $lastPublishedStamp
     * @param null $excludePostIds
     * @param int $langId
     * @return array
     */
    public function getArticleInTagTimestamp($tagId, $limit, $lastPublishedStamp, $excludePostIds = null, $langId = 0)
    {
        $query = $this->createQueryBuilder('a')
            ->select('a.postId, a.title, a.slug, a.sapo, a.publishedDate, a.publishedTimestamp, a.avatar, u.fullname, u.slug authorSlug')
            ->innerJoin(PostsTags::class, 'c', 'WITH', 'c.postId = a.postId')
            ->leftJoin(AdminUsers::class, 'u', 'WITH', 'a.authorId = u.userId');
        $parameters = [];
        if ($excludePostIds) {
            $query->where('a.postId NOT IN (:postIds)');//->setParameter(':postIds', $excludePostIds);
            $parameters[':postIds'] = $excludePostIds;
        }
        $query->andWhere('a.langId = :langId AND a.publishedTimestamp <= :currentDate AND c.tagId = :tagId');
        $parameters[':currentDate'] = $lastPublishedStamp;
        $parameters[':langId'] = $langId;
        $parameters[':tagId'] = $tagId;
        $posts = $query->setParameters($parameters)
            ->orderBy('a.publishedTimestamp', 'DESC')
            ->addOrderBy('a.postId', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return $posts;
    }


    /**
     * get Articles in Author by paging
     * author: AnhPT4
     * date:   2018-10-25 09:31 AM
     * @param $authorId
     * @param $pageIndex
     * @param $pageSize
     * @param int $langId
     * @return array
     */
    /*public function getArticleByAuthorPaging($authorId, $pageIndex, $pageSize, $langId = 0)
    {
        $startIndex = ($pageIndex - 1) * $pageSize;
        $data = $this->createQueryBuilder('a')
            ->select('a.postId, a.title, a.slug, a.sapo, a.publishedDate,a.publishedTimestamp,a.avatar, u.slug as authorSlug, u.fullname')
            //->innerJoin(PostsTags::class, 'c', 'WITH', 'c.postId = a.postId')
            ->innerJoin(AdminUsers::class, 'u', 'WITH', 'a.authorId = u.userId')
            ->where('a.langId = :langId AND a.authorId = :authorId AND a.publishedTimestamp <= :currentDate')
            ->setParameters([':langId' => $langId, ':authorId' => $authorId, ':currentDate' => time()])
            ->setFirstResult($startIndex)
            ->orderBy('a.publishedTimestamp', 'DESC')
            ->setMaxResults($pageSize)
            ->getQuery()->getArrayResult();

        return $data;
    }*/

    /**
     * get Articles in Author by paging ajax
     * author: AnhPT4
     * date:   2018-10-25 09:31 AM
     * @param $authorId
     * @param $limit
     * @param $lastPublishedStamp
     * @param $excludePostIds
     * @param int $langId
     * @return array
     */
    public function getArticleByAuthorTimestamp($authorId, $limit, $lastPublishedStamp, $excludePostIds = null, $langId = 0)
    {
        $query = $this->createQueryBuilder('a')
            ->select('a.postId, a.title, a.slug, a.sapo, a.publishedDate, a.publishedTimestamp, a.avatar, u.fullname, u.slug authorSlug')
            ->leftJoin(AdminUsers::class, 'u', 'WITH', 'a.authorId = u.userId');
        $parameters = [];
        if ($excludePostIds) {
            $query->where('a.postId NOT IN (:postIds)');//->setParameter(':postIds', $excludePostIds);
            $parameters[':postIds'] = $excludePostIds;
        }
        $query->andWhere('a.authorId = :authorId AND a.langId = :langId AND a.publishedTimestamp <= :currentDate');
        $parameters[':currentDate'] = $lastPublishedStamp;
        $parameters[':langId'] = $langId;
        $parameters[':authorId'] = $authorId;
        $posts = $query->setParameters($parameters)
            ->orderBy('a.publishedTimestamp', 'DESC')
            ->addOrderBy('a.postId', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return $posts;
    }
    // ---------------------------END AnhPT4----------------------------------------------------------------------------

    // TrieuNT
    /**
     * Get Article by Id
     * author: TrieuNT
     * create date: 2018-10-18 10:33 AM
     * @param $postId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getDetail($postId)
    {
        $data = $this->createQueryBuilder('a')
            ->select('a.postId, a.authorId, a.title, a.slug, a.avatar, a.sapo, pd.content, pd.cates, pd.tags, a.publishedDate, a.reviewId, a.seoTitle, a.seoMetadesc')
            ->innerJoin(PostDatas::class, 'pd', 'WITH', 'a.postId = pd.postId')
            //->leftJoin(AdminUsers::class, 'u', 'WITH', 'a.authorId = u.userId')
            ->where('a.postId = :postId')
            ->setParameters([':postId' => $postId])
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        return $data;
    }

    /**
     * Get post by id
     * author: ThanhDT
     * date:   2018-07-14 12:20 AM
     * @param $postId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getShortDetailById($postId)
    {
        $data = $this->createQueryBuilder('a')
            ->select('a.postId, a.title, a.slug')
            ->where('a.postId = :postId')
            ->setParameters([':postId' => $postId])
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        return $data;
    }

    /**
     * Get Article by Id
     * author: TrieuNT
     * create date: 2018-10-18 10:33 AM
     * @param $postId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */

    public function getShortDetailBySlug($slug)
    {
        $data = $this->createQueryBuilder('a')
            ->select('a.postId, a.slug, a.publishedDate')
            ->where('a.slug = :slug')
            ->setParameters([':slug' => $slug])
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        return $data;
    }

    /**
     * author: TrieuNT
     * create date: 2018-10-19 10:26 AM
     * Get Article in tag exclude articleId
     * @param $tagIds
     * @param $articleId
     * @param $pageSize
     * @param $langId
     * @return array
     * @throws \Exception
     */

    public function getArticleInTagExclude($tagIds, $articleId, $pageSize, $langId = 0)
    {
        $data = $this->createQueryBuilder('p')
            ->select('p.postId, p.title, p.slug, p.sapo, p.publishedDate, p.avatar')
            ->innerJoin(PostsTags::class, 't', 'WITH', 'p.postId = t.postId')
            ->where('p.postId <> :articleId AND p.langId = :langId AND t.tagId IN (:tagIds) AND p.publishedDate <= :currentDate')
            ->setParameters([':tagIds' => $tagIds, ':langId' => $langId, ':articleId' => $articleId, ':currentDate' => new \DateTime()])
            ->orderBy('p.publishedDate', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults($pageSize)
            ->getQuery()->getArrayResult();

        return $data;
    }

    /**
     * author: TrieuNT
     * create date: 2018-10-19 10:26 AM
     * update time : 2019-03-16 10:20 AM
     * Get Article in tag exclude articleId
     * @param $cateIds
     * @param $pageSize
     * @param $ignoreId
     * @param $langId
     * @return array
     * @throws \Exception
     */
    public function getArticleInCateExclude($cateIds, $ignoreId, $pageSize, $langId = 0)
    {
        $data = $this->createQueryBuilder('p')
            ->select('p.postId, p.title, p.slug, p.sapo, p.publishedDate, p.avatar, p.cateId primaryCateId')
            ->where('p.postId NOT IN (:articleId) AND p.langId = :langId AND p.cateId IN (:cateIds) AND p.publishedDate <= :currentDate')
            ->setParameters([':cateIds' => $cateIds, ':langId' => $langId, ':articleId' => $ignoreId, ':currentDate' => new \DateTime()])
            ->orderBy('p.publishedDate', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults($pageSize)
            ->getQuery()->getArrayResult();

        return $data;
    }

    /**
     * author: TrieuNT
     * create date: 2018-10-19 01:47 PM
     * @param $cateId
     * @param $strIgnoreId
     * @param $pageSize
     * @param $langId
     * @return array
     */

    public function getArticleFeaturedStories($cateId, $strIgnoreId, $pageSize, $langId = 0)
    {
        $data = $this->createQueryBuilder('p')
            ->select('p.postId, p.title, p.slug, p.sapo, p.publishedDate, p.avatar, p.authorId,  u.fullname, u.slug as authorSlug, p.cateId primaryCate')
            ->leftJoin(AdminUsers::class, 'u', 'WITH', 'p.authorId = u.userId')
            ->where('p.cateId = :cateId AND p.postId NOT IN (:postId) AND p.langId = :langId')
            ->setParameters([':cateId' => $cateId, ':postId' => $strIgnoreId, ':langId' => $langId])
            ->orderBy('p.publishedDate', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults($pageSize)
            ->getQuery()
            ->getArrayResult();
        return $data;
    }

    /**
     * author: TrieuNT
     * create date: 2018-10-23 09:32 AM
     * @param int $pageSize
     * @return array
     * @throws \Exception
     */

    public function getArticleRssLatest($pageSize = 9)
    {
        // RSSController
        $data = $this->createQueryBuilder('a')
            ->select('a.postId, a.title, a.slug, a.sapo, pd.content, pd.cates, a.publishedDate, u.fullname, a.avatar')
            ->innerJoin(PostDatas::class, 'pd', 'WITH', 'a.postId = pd.postId')
            ->leftJoin(AdminUsers::class, 'u', 'WITH', 'u.userId = a.authorId')
            ->where('a.publishedDate <= :currentDate')
            ->setParameters([':currentDate' => new \DateTime()])
            ->orderBy('a.publishedDate', 'DESC')
            ->setMaxResults($pageSize)
            ->getQuery()->getArrayResult();

        return $data;
    }

    /**
     * Get Article by slug
     * author: TrieuNT
     * create date: 2018-10-23 09:32 AM
     * @param $slug
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */

    public function getDetailBySlug($slug)
    {
        //RSSController and others
        $data = $this->createQueryBuilder('a')
            ->select('a.postId, a.authorId, a.title, a.slug, a.avatar, a.sapo, pd.content, pd.cates, pd.tags, a.publishedDate, a.reviewId, a.seoTitle, a.seoMetadesc')
            ->innerJoin(PostDatas::class, 'pd', 'WITH', 'a.postId = pd.postId')
            //->leftJoin('NewsBundle:AdminUsers', 'u', 'WITH', 'a.authorId = u.userId')
            ->where('a.slug = :slug')
            ->setParameters([':slug' => $slug])
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        return $data;
    }


    /**
     * author: TrieuNT
     * create date: 2018-10-24 09:32 AM
     * Get news for RssFeed
     * @param $tagIds
     * @param $limit
     * @return array
     * @throws \Exception
     */
    public function getArticleInTagRss($tagIds, $limit)
    {
        $data = $this->createQueryBuilder('a')
            ->select('a.postId, a.authorId, a.title, a.slug, a.avatar, a.sapo, pd.content, pd.cates, pd.tags, a.publishedDate, a.seoTitle, a.seoMetadesc, u.fullname')
            ->innerJoin(PostDatas::class, 'pd', 'WITH', 'a.postId = pd.postId')
            ->innerJoin(PostsTags::class, 'c', 'WITH', 'c.postId = a.postId')
            ->leftJoin(AdminUsers::class, 'u', 'WITH', 'u.userId = a.authorId')
            ->where('a.publishedDate <= :currentDate AND c.tagId IN (:tagIds)')
            ->setParameters([':tagIds' => $tagIds, ':currentDate' => new \DateTime()])
            ->orderBy('a.publishedDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getArrayResult();

        return $data;
    }


    /**
     * Get Post today
     * author: TrieuNT
     * create date: 2018-10-24 10:59 AM
     * @return array
     * @throws \Exception
     */
    public function getArticleByCurrentTime()
    {
        $qp = $this->createQueryBuilder('a')
            ->select('a.postId')
            ->where('a.publishedDate BETWEEN :currentMonth AND :currentDate')
            ->setParameters([':currentMonth' => date('Y-m'), ':currentDate' => new \DateTime()])
            ->setMaxResults(1)
            ->getQuery();
        return $qp->getResult();
    }

    /**
     * Get article by date
     * author: TrieuNT
     * create date: 2018-10-24 01:27 PM
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public function getArticleByDate($startDate, $endDate)
    {
        $data = $this->createQueryBuilder('a')
            ->select('a.postId, a.title, a.slug, a.publishedDate,u.fullname displayName,a.avatar')
            ->leftJoin(AdminUsers::class, 'u', 'WITH', 'a.authorId = u.userId')
            ->where('a.publishedTimestamp BETWEEN :startDate AND :endDate')
            ->setParameters([':startDate' => $startDate, ':endDate' => $endDate])
            ->orderBy('a.publishedTimestamp', 'DESC')
            ->getQuery()->getArrayResult();

        return $data;
    }

    /**
     * @param $statDate
     * @param $endDate
     * @return array
     */
    public function getPostsBetweenDates($statDate, $endDate)
    {/*var_dump(date('Y-m-d H:i:s', $statDate));
        var_dump(date('Y-m-d H:i:s', $endDate));die;*/
        $data = $this->createQueryBuilder('a')
            ->select('a.postId, a.title, a.slug, a.publishedDate,a.modifiedDate,u.fullname displayName,a.avatar')
            ->leftJoin(AdminUsers::class, 'u', 'WITH', 'a.authorId = u.userId')
            ->where('a.publishedTimestamp BETWEEN :startDate AND :endDate')
            ->setParameters([':startDate' => $statDate, ':endDate' => $endDate])
            ->orderBy('a.publishedTimestamp', 'DESC')
            ->getQuery()->getArrayResult();

        return $data;
    }

    /**
     * Get detail article for amp page
     * author: TrieuNT
     * create date: 2018-11-09 10:30 AM
     * @param $postId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getDetailAmp($postId)
    {
        $data = $this->createQueryBuilder('a')
            ->select('a.postId, a.authorId, a.title, a.slug, a.avatar, a.sapo, pd.contentAmp content, pd.cates, pd.tags, a.publishedDate, a.reviewId')
            ->innerJoin(PostDatas::class, 'pd', 'WITH', 'a.postId = pd.postId')
            ->where('a.postId = :postId')
            ->setParameters([':postId' => $postId])
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        return $data;
    }

    /**
     * Get article by author for rss
     * @param $authorId
     * @param $limit
     * @return array
     * @throws \Exception
     */
    public function getArticleByAuthorRss($authorId, $limit)
    {
        $data = $this->createQueryBuilder('a')
            ->select('a.postId, a.title, a.slug, a.sapo, pd.content, pd.cates, a.publishedDate, u.fullname, a.avatar')
            ->innerJoin(PostDatas::class, 'pd', 'WITH', 'a.postId = pd.postId')
            ->leftJoin(AdminUsers::class, 'u', 'WITH', 'u.userId = a.authorId')
            ->where('a.authorId = :authorId AND a.publishedTimestamp <= :currentDate')
            ->setParameters([':authorId' => $authorId, ':currentDate' => time()])
            ->orderBy('a.publishedDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getArrayResult();

        return $data;
    }

    /**
     * Get top lastest posts
     * author: ThanhDT
     * date:   2018-12-19 09:01 AM
     * @param $limit
     * @param int $langId
     * @return array
     */
    public function getTopLastestPosts($limit, $langId = 0)
    {
        $queryBuilder = $this->createQueryBuilder('a');
        return $queryBuilder->select('a.postId, a.title, a.slug, a.sapo, a.publishedDate, a.publishedTimestamp, a.avatar, u.fullname, u.slug authorSlug')
            ->leftJoin(AdminUsers::class, 'u', 'WITH', 'a.authorId = u.userId')
            ->where('a.langId = :langId AND a.publishedTimestamp <= :currentDate')
            ->andWhere('a.focusStatus = 0')
            ->setParameters([':langId' => $langId, ':currentDate' => time()])
            ->orderBy('a.publishedTimestamp', 'DESC')
            ->addOrderBy('a.postId', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();
    }
}
