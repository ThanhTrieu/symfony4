<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PhotoGalleriesImages
 *
 * @ORM\Table(name="photo_galleries_images")
 * @ORM\Entity(repositoryClass="App\Repository\PhotoGalleriesImagesRepository")
 */
class PhotoGalleriesImages
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="gallery_id", type="integer", nullable=false)
     */
    private $galleryId;

    /**
     * @var int
     *
     * @ORM\Column(name="image_id", type="integer", nullable=false)
     */
    private $imageId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGalleryId(): ?int
    {
        return $this->galleryId;
    }

    public function setGalleryId(int $galleryId): self
    {
        $this->galleryId = $galleryId;

        return $this;
    }

    public function getImageId(): ?int
    {
        return $this->imageId;
    }

    public function setImageId(int $imageId): self
    {
        $this->imageId = $imageId;

        return $this;
    }


}
