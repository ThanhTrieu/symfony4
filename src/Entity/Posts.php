<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Posts
 *
 * @ORM\Table(name="posts")
 * @ORM\Entity(repositoryClass="App\Repository\PostsRepository")
 */
class Posts
{
    /**
     * @var int
     *
     * @ORM\Column(name="post_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $postId;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255, nullable=false)
     */
    private $slug;

    /**
     * @var string|null
     *
     * @ORM\Column(name="avatar", type="string", length=255, nullable=true, options={"default"="NULL","comment"="Ảnh đại diện bài viết"})
     */
    private $avatar = 'NULL';

    /**
     * @var string|null
     *
     * @ORM\Column(name="sapo", type="string", length=255, nullable=true, options={"default"="NULL","comment"="Mô tả ngắn"})
     */
    private $sapo = 'NULL';

    /**
     * @var int
     *
     * @ORM\Column(name="cate_id", type="integer", nullable=false, options={"comment"="Primary category"})
     */
    private $cateId = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="lang_id", type="boolean", nullable=false, options={"comment"="Ngôn ngữ: 0-English,1-Hindi"})
     */
    private $langId = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="creator_id", type="integer", nullable=false, options={"comment"="Người tạo bài"})
     */
    private $creatorId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetime", nullable=false, options={"comment"="Ngày tạo bài"})
     */
    private $createdDate;

    /**
     * @var int|null
     *
     * @ORM\Column(name="modifier_id", type="integer", nullable=true, options={"default"="NULL","comment"="Ngưới sửa bài"})
     */
    private $modifierId = 'NULL';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="modified_date", type="datetime", nullable=true, options={"default"="NULL","comment"="Ngày sửa bài"})
     */
    private $modifiedDate = 'NULL';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="published_date", type="datetime", nullable=true, options={"default"="NULL","comment"="Ngày xuất bản"})
     */
    private $publishedDate = 'NULL';

    /**
     * @var int
     *
     * @ORM\Column(name="author_id", type="integer", nullable=false, options={"comment"="Tác giả"})
     */
    private $authorId;

    /**
     * @var bool
     *
     * @ORM\Column(name="post_type", type="boolean", nullable=false, options={"comment"="0: Bài viết, 1: Trang web"})
     */
    private $postType = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="avatar_image_id", type="integer", nullable=false, options={"comment"="ID ảnh đại diện"})
     */
    private $avatarImageId = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="review_id", type="integer", nullable=false, options={"comment"="Review ID"})
     */
    private $reviewId = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=false, options={"comment"="Page parent ID"})
     */
    private $parentId = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="notify_status", type="boolean", nullable=false, options={"comment"="0: Không gửi notify, 1: Cho phép Push notification, 2: Đã gửi notification"})
     */
    private $notifyStatus = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="status", type="boolean", nullable=false, options={"comment"="0: Draft, 1: Pending, 2: Published, 3: Trash"})
     */
    private $status = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="seo_title", type="string", length=255, nullable=true, options={"default"="NULL","comment"="Tiêu đề SEO"})
     */
    private $seoTitle = 'NULL';

    /**
     * @var string|null
     *
     * @ORM\Column(name="seo_metadesc", type="string", length=500, nullable=true, options={"default"="NULL","comment"="Mô tả SEO"})
     */
    private $seoMetadesc = 'NULL';

    /**
     * @var string|null
     *
     * @ORM\Column(name="seo_focus_keyword", type="string", length=255, nullable=true, options={"default"="NULL","comment"="Keyword chính cho SEO"})
     */
    private $seoFocusKeyword = 'NULL';

    /**
     * @var string|null
     *
     * @ORM\Column(name="seo_focus_others", type="string", length=1000, nullable=true, options={"default"="NULL","comment"="Keywords phụ khác"})
     */
    private $seoFocusOthers = 'NULL';

    /**
     * @var bool|null
     *
     * @ORM\Column(name="seo_keyword_score", type="boolean", nullable=true, options={"default"="NULL","comment"="Điểm keyword - SEO score"})
     */
    private $seoKeywordScore = 'NULL';

    /**
     * @var bool|null
     *
     * @ORM\Column(name="seo_content_score", type="boolean", nullable=true, options={"default"="NULL","comment"="Điểm content - Readability score"})
     */
    private $seoContentScore = 'NULL';

    /**
     * @var string|null
     *
     * @ORM\Column(name="src_link", type="string", length=255, nullable=true, options={"default"="NULL","comment"="Link bài gốc"})
     */
    private $srcLink = 'NULL';

    /**
     * @var bool
     *
     * @ORM\Column(name="focus_status", type="boolean", nullable=false, options={"comment"="Trạng thái nổi bật: 0-Tin thường, 1-Nổi bật trang chủ, 2-Nổi bật chuyên mục"})
     */
    private $focusStatus = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="focus_avatar", type="string", length=255, nullable=true, options={"default"="NULL","comment"="Ảnh nổi bật"})
     */
    private $focusAvatar = 'NULL';

    /**
     * @var int
     *
     * @ORM\Column(name="view_count", type="integer", nullable=false, options={"comment"="Lượt xem"})
     */
    private $viewCount = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="word_count", type="integer", nullable=false, options={"comment"="Số từ trong bài viết"})
     */
    private $wordCount = '0';

    public function getPostId(): ?int
    {
        return $this->postId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getSapo(): ?string
    {
        return $this->sapo;
    }

    public function setSapo(?string $sapo): self
    {
        $this->sapo = $sapo;

        return $this;
    }

    public function getCateId(): ?int
    {
        return $this->cateId;
    }

    public function setCateId(int $cateId): self
    {
        $this->cateId = $cateId;

        return $this;
    }

    public function getLangId(): ?bool
    {
        return $this->langId;
    }

    public function setLangId(bool $langId): self
    {
        $this->langId = $langId;

        return $this;
    }

    public function getCreatorId(): ?int
    {
        return $this->creatorId;
    }

    public function setCreatorId(int $creatorId): self
    {
        $this->creatorId = $creatorId;

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getModifierId(): ?int
    {
        return $this->modifierId;
    }

    public function setModifierId(?int $modifierId): self
    {
        $this->modifierId = $modifierId;

        return $this;
    }

    public function getModifiedDate(): ?\DateTimeInterface
    {
        return $this->modifiedDate;
    }

    public function setModifiedDate(?\DateTimeInterface $modifiedDate): self
    {
        $this->modifiedDate = $modifiedDate;

        return $this;
    }

    public function getPublishedDate(): ?\DateTimeInterface
    {
        return $this->publishedDate;
    }

    public function setPublishedDate(?\DateTimeInterface $publishedDate): self
    {
        $this->publishedDate = $publishedDate;

        return $this;
    }

    public function getAuthorId(): ?int
    {
        return $this->authorId;
    }

    public function setAuthorId(int $authorId): self
    {
        $this->authorId = $authorId;

        return $this;
    }

    public function getPostType(): ?bool
    {
        return $this->postType;
    }

    public function setPostType(bool $postType): self
    {
        $this->postType = $postType;

        return $this;
    }

    public function getAvatarImageId(): ?int
    {
        return $this->avatarImageId;
    }

    public function setAvatarImageId(int $avatarImageId): self
    {
        $this->avatarImageId = $avatarImageId;

        return $this;
    }

    public function getReviewId(): ?int
    {
        return $this->reviewId;
    }

    public function setReviewId(int $reviewId): self
    {
        $this->reviewId = $reviewId;

        return $this;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setParentId(int $parentId): self
    {
        $this->parentId = $parentId;

        return $this;
    }

    public function getNotifyStatus(): ?bool
    {
        return $this->notifyStatus;
    }

    public function setNotifyStatus(bool $notifyStatus): self
    {
        $this->notifyStatus = $notifyStatus;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getSeoTitle(): ?string
    {
        return $this->seoTitle;
    }

    public function setSeoTitle(?string $seoTitle): self
    {
        $this->seoTitle = $seoTitle;

        return $this;
    }

    public function getSeoMetadesc(): ?string
    {
        return $this->seoMetadesc;
    }

    public function setSeoMetadesc(?string $seoMetadesc): self
    {
        $this->seoMetadesc = $seoMetadesc;

        return $this;
    }

    public function getSeoFocusKeyword(): ?string
    {
        return $this->seoFocusKeyword;
    }

    public function setSeoFocusKeyword(?string $seoFocusKeyword): self
    {
        $this->seoFocusKeyword = $seoFocusKeyword;

        return $this;
    }

    public function getSeoFocusOthers(): ?string
    {
        return $this->seoFocusOthers;
    }

    public function setSeoFocusOthers(?string $seoFocusOthers): self
    {
        $this->seoFocusOthers = $seoFocusOthers;

        return $this;
    }

    public function getSeoKeywordScore(): ?bool
    {
        return $this->seoKeywordScore;
    }

    public function setSeoKeywordScore(?bool $seoKeywordScore): self
    {
        $this->seoKeywordScore = $seoKeywordScore;

        return $this;
    }

    public function getSeoContentScore(): ?bool
    {
        return $this->seoContentScore;
    }

    public function setSeoContentScore(?bool $seoContentScore): self
    {
        $this->seoContentScore = $seoContentScore;

        return $this;
    }

    public function getSrcLink(): ?string
    {
        return $this->srcLink;
    }

    public function setSrcLink(?string $srcLink): self
    {
        $this->srcLink = $srcLink;

        return $this;
    }

    public function getFocusStatus(): ?bool
    {
        return $this->focusStatus;
    }

    public function setFocusStatus(bool $focusStatus): self
    {
        $this->focusStatus = $focusStatus;

        return $this;
    }

    public function getFocusAvatar(): ?string
    {
        return $this->focusAvatar;
    }

    public function setFocusAvatar(?string $focusAvatar): self
    {
        $this->focusAvatar = $focusAvatar;

        return $this;
    }

    public function getViewCount(): ?int
    {
        return $this->viewCount;
    }

    public function setViewCount(int $viewCount): self
    {
        $this->viewCount = $viewCount;

        return $this;
    }

    public function getWordCount(): ?int
    {
        return $this->wordCount;
    }

    public function setWordCount(int $wordCount): self
    {
        $this->wordCount = $wordCount;

        return $this;
    }


}
