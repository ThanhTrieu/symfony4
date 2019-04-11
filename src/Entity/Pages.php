<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Pages
 *
 * @ORM\Table(name="pages")
 * @ORM\Entity(repositoryClass="App\Repository\PagesRepository")
 */
class Pages
{
    /**
     * @var int
     *
     * @ORM\Column(name="page_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $pageId;

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
     * @var int|null
     *
     * @ORM\Column(name="author_id", type="integer", nullable=true, options={"comment"="Tác giả bài viết"})
     */
    private $authorId;

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
     * @ORM\Column(name="content_source", type="text", length=65535, nullable=true, options={"comment"="Nội dung bài gốc"})
     */
    private $contentSource;

    /**
     * @var string|null
     *
     * @ORM\Column(name="content", type="text", length=65535, nullable=true, options={"comment"="Nội dung bài được chuẩn hóa html"})
     */
    private $content;

    /**
     * @var string|null
     *
     * @ORM\Column(name="content_amp", type="text", length=65535, nullable=true, options={"comment"="Nội dung bài được chuẩn hóa AMP"})
     */
    private $contentAmp;

    /**
     * @var int|null
     *
     * @ORM\Column(name="old_id", type="integer", nullable=true, options={"comment"="Old post ID in WP"})
     */
    private $oldId;

    /**
     * @var bool
     *
     * @ORM\Column(name="status", type="boolean", nullable=false, options={"comment"="0: Draft, 1: Pending, 2: Published, 3: Trash"})
     */
    private $status = '0';

    public function getPageId(): ?int
    {
        return $this->pageId;
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

    public function setAuthorId(?int $authorId): self
    {
        $this->authorId = $authorId;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getContentAmp(): ?string
    {
        return $this->contentAmp;
    }

    public function setContentAmp(?string $contentAmp): self
    {
        $this->contentAmp = $contentAmp;

        return $this;
    }

    public function getOldId(): ?int
    {
        return $this->oldId;
    }

    public function setOldId(?int $oldId): self
    {
        $this->oldId = $oldId;

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
