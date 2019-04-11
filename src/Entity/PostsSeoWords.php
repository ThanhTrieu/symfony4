<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PostsSeoWords
 *
 * @ORM\Table(name="posts_seo_words")
 * @ORM\Entity
 */
class PostsSeoWords
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="post_id", type="integer", nullable=false)
     */
    private $postId;

    /**
     * @var int
     *
     * @ORM\Column(name="word_id", type="integer", nullable=false)
     */
    private $wordId;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getWordId(): ?int
    {
        return $this->wordId;
    }

    public function setWordId(int $wordId): self
    {
        $this->wordId = $wordId;

        return $this;
    }


}
