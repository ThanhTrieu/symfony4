<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PostRevisions
 *
 * @ORM\Table(name="post_revisions", indexes={@ORM\Index(name="cms-post_id-post_type", columns={"post_id", "post_type"}), @ORM\Index(name="cms-post_id-post_type-modifier_id", columns={"post_id", "post_type", "modifier_id"})})
 * @ORM\Entity
 */
class PostRevisions
{
    /**
     * @var int
     *
     * @ORM\Column(name="revision_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $revisionId;

    /**
     * @var int
     *
     * @ORM\Column(name="post_id", type="integer", nullable=false, options={"unsigned"=true,"comment"="ID bài viết"})
     */
    private $postId;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false, options={"comment"="Tiêu đề bài viết"})
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255, nullable=false, options={"comment"="Slug bài viết"})
     */
    private $slug;

    /**
     * @var string|null
     *
     * @ORM\Column(name="avatar", type="string", length=255, nullable=true, options={"comment"="Ảnh đại diện bài viết"})
     */
    private $avatar;

    /**
     * @var string|null
     *
     * @ORM\Column(name="sapo", type="string", length=255, nullable=true, options={"comment"="Mô tả ngắn"})
     */
    private $sapo;

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
     * @ORM\Column(name="created_date", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP","comment"="Ngày tạo bài"})
     */
    private $createdDate = 'CURRENT_TIMESTAMP';

    /**
     * @var int|null
     *
     * @ORM\Column(name="modifier_id", type="integer", nullable=true, options={"comment"="Ngưới sửa bài"})
     */
    private $modifierId;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="modified_date", type="datetime", nullable=true, options={"comment"="Ngày sửa bài"})
     */
    private $modifiedDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="published_date", type="datetime", nullable=true, options={"comment"="Ngày xuất bản"})
     */
    private $publishedDate;

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
     * @var string|null
     *
     * @ORM\Column(name="content_source", type="text", length=65535, nullable=true, options={"comment"="Nội dung bài viết"})
     */
    private $contentSource;

    /**
     * @var bool
     *
     * @ORM\Column(name="revision_type", type="boolean", nullable=false, options={"comment"="0: Revision, 1: AutoSave"})
     */
    private $revisionType = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="status", type="boolean", nullable=false, options={"comment"="0: Draft, 1: Pending, 2: Published, 3: Trash"})
     */
    private $status = '0';

    public function getRevisionId(): ?int
    {
        return $this->revisionId;
    }

    public function getPostId(): ?int
    {
        return $this->postId;
    }

    public function setPostId(int $postId): self
    {
        $this->postId = $postId;

        return $this;
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

    public function getContentSource(): ?string
    {
        return $this->contentSource;
    }

    public function setContentSource(?string $contentSource): self
    {
        $this->contentSource = $contentSource;

        return $this;
    }

    public function getRevisionType(): ?bool
    {
        return $this->revisionType;
    }

    public function setRevisionType(bool $revisionType): self
    {
        $this->revisionType = $revisionType;

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


}
