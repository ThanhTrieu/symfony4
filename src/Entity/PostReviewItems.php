<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PostReviewItems
 *
 * @ORM\Table(name="post_review_items")
 * @ORM\Entity
 */
class PostReviewItems
{
    /**
     * @var int
     *
     * @ORM\Column(name="review_item_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $reviewItemId;

    /**
     * @var int
     *
     * @ORM\Column(name="review_id", type="integer", nullable=false)
     */
    private $reviewId;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="decimal", precision=3, scale=2, nullable=false, options={"default"="0.00"})
     */
    private $value = '0.00';

    public function getReviewItemId(): ?int
    {
        return $this->reviewItemId;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }


}
