<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SeoWords
 *
 * @ORM\Table(name="seo_words")
 * @ORM\Entity
 */
class SeoWords
{
    /**
     * @var int
     *
     * @ORM\Column(name="word_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $wordId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false, options={"comment"="Tên từ khóa"})
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="post_count", type="integer", nullable=false, options={"comment"="Tổng số bài viết"})
     */
    private $postCount = '0';

    public function getWordId(): ?int
    {
        return $this->wordId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPostCount(): ?int
    {
        return $this->postCount;
    }

    public function setPostCount(int $postCount): self
    {
        $this->postCount = $postCount;

        return $this;
    }


}
