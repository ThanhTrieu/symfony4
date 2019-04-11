<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GalleryImages
 *
 * @ORM\Table(name="gallery_images", indexes={@ORM\Index(name="cms-post_id", columns={"post_id"}), @ORM\Index(name="cms-creator_id", columns={"creator_id"}), @ORM\Index(name="cms-created_date", columns={"created_date"})})
 * @ORM\Entity(repositoryClass="App\Repository\GalleryImagesRepository")
 */
class GalleryImages
{
    /**
     * @var int
     *
     * @ORM\Column(name="image_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $imageId;

    /**
     * @var int
     *
     * @ORM\Column(name="post_id", type="integer", nullable=false, options={"comment"="Bài viết đính kèm ảnh"})
     */
    private $postId = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false, options={"comment"="Tiêu đề ảnh"})
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
     * @ORM\Column(name="alt", type="string", length=255, nullable=true, options={"comment"="Thuộc tính alt của thẻ ảnh"})
     */
    private $alt;

    /**
     * @var string|null
     *
     * @ORM\Column(name="caption", type="string", length=255, nullable=true, options={"comment"="Caption nhúng vào editor"})
     */
    private $caption;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=false, options={"comment"="Đường dẫn ảnh"})
     */
    private $url;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="string", length=500, nullable=true, options={"comment"="Mô tả ảnh"})
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP","comment"="Ngày tạo ảnh"})
     */
    private $createdDate = 'CURRENT_TIMESTAMP';

    /**
     * @var int
     *
     * @ORM\Column(name="creator_id", type="integer", nullable=false, options={"comment"="Người tạo ảnh"})
     */
    private $creatorId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="mime_type", type="string", length=20, nullable=true, options={"comment"="Loại file ảnh"})
     */
    private $mimeType;

    /**
     * @var int
     *
     * @ORM\Column(name="width", type="integer", nullable=false, options={"comment"="Chiều dài ảnh"})
     */
    private $width = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="height", type="integer", nullable=false, options={"comment"="Chiều cao ảnh"})
     */
    private $height = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="file_size", type="integer", nullable=false, options={"comment"="Dung lượng file tính theo Kb"})
     */
    private $fileSize = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="status", type="boolean", nullable=false, options={"default"="1","comment"="Trạng thái ảnh, 0: Chưa duyệt, 1: Đã duyệt"})
     */
    private $status = '1';

    public function getImageId(): ?int
    {
        return $this->imageId;
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

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function setAlt(?string $alt): self
    {
        $this->alt = $alt;

        return $this;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCaption(?string $caption): self
    {
        $this->caption = $caption;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    public function getCreatorId(): ?int
    {
        return $this->creatorId;
    }

    public function setCreatorId(int $creatorId): self
    {
        $this->creatorId = $creatorId;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(int $fileSize): self
    {
        $this->fileSize = $fileSize;

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
