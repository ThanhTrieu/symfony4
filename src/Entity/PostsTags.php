<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PostsTags
 *
 * @ORM\Table(name="posts_tags", indexes={@ORM\Index(name="cms-post_id", columns={"post_id"}), @ORM\Index(name="cms-tag_id", columns={"tag_id"})})
 * @ORM\Entity
 */
class PostsTags
{
    /**
     * @var int
     *
     * @ORM\Column(name="post_tag_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $postTagId;

    /**
     * @var int
     *
     * @ORM\Column(name="post_id", type="integer", nullable=false)
     */
    private $postId;

    /**
     * @var int
     *
     * @ORM\Column(name="tag_id", type="integer", nullable=false)
     */
    private $tagId;

    public function getPostTagId(): ?int
    {
        return $this->postTagId;
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

    public function getTagId(): ?int
    {
        return $this->tagId;
    }

    public function setTagId(int $tagId): self
    {
        $this->tagId = $tagId;

        return $this;
    }


}
