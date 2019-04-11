<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PostDatas
 *
 * @ORM\Table(name="post_datas")
 * @ORM\Entity
 */
class PostDatas
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
     * @var string|null
     *
     * @ORM\Column(name="content_source", type="text", length=65535, nullable=true)
     */
    private $contentSource;

    /**
     * @var string|null
     *
     * @ORM\Column(name="content", type="text", length=65535, nullable=true)
     */
    private $content;

    /**
     * @var string|null
     *
     * @ORM\Column(name="content_mobile", type="text", length=65535, nullable=true)
     */
    private $contentMobile;

    /**
     * @var string|null
     *
     * @ORM\Column(name="content_amp", type="text", length=65535, nullable=true)
     */
    private $contentAmp;

    /**
     * @var string|null
     *
     * @ORM\Column(name="cates", type="string", length=1000, nullable=true)
     */
    private $cates;

    /**
     * @var string|null
     *
     * @ORM\Column(name="tags", type="string", length=1000, nullable=true)
     */
    private $tags;

    /**
     * @var string|null
     *
     * @ORM\Column(name="source_tags", type="string", length=1000, nullable=true, options={"comment"="Tag từ nguồn crawl"})
     */
    private $sourceTags;

    public function getPostId(): ?int
    {
        return $this->postId;
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

    public function getContentMobile(): ?string
    {
        return $this->contentMobile;
    }

    public function setContentMobile(?string $contentMobile): self
    {
        $this->contentMobile = $contentMobile;

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

    public function getCates(): ?string
    {
        return $this->cates;
    }

    public function setCates(?string $cates): self
    {
        $this->cates = $cates;

        return $this;
    }

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setTags(?string $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function getSourceTags(): ?string
    {
        return $this->sourceTags;
    }

    public function setSourceTags(?string $sourceTags): self
    {
        $this->sourceTags = $sourceTags;

        return $this;
    }


}
