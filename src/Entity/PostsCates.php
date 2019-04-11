<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PostsCates
 *
 * @ORM\Table(name="posts_cates", indexes={@ORM\Index(name="cms-post_id", columns={"post_id"}), @ORM\Index(name="cms-cate_id", columns={"cate_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\PostsCatesRepository")
 */
class PostsCates
{
    /**
     * @var int
     *
     * @ORM\Column(name="post_cate_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $postCateId;

    /**
     * @var int
     *
     * @ORM\Column(name="post_id", type="integer", nullable=false)
     */
    private $postId;

    /**
     * @var int
     *
     * @ORM\Column(name="cate_id", type="integer", nullable=false)
     */
    private $cateId;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_primary", type="boolean", nullable=false, options={"comment"="Là chuyên mục chính"})
     */
    private $isPrimary = '0';

    public function getPostCateId(): ?int
    {
        return $this->postCateId;
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

    public function getCateId(): ?int
    {
        return $this->cateId;
    }

    public function setCateId(int $cateId): self
    {
        $this->cateId = $cateId;

        return $this;
    }

    public function getIsPrimary(): ?bool
    {
        return $this->isPrimary;
    }

    public function setIsPrimary(bool $isPrimary): self
    {
        $this->isPrimary = $isPrimary;

        return $this;
    }


}
