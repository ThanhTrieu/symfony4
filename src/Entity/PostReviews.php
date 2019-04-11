<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PostReviews
 *
 * @ORM\Table(name="post_reviews")
 * @ORM\Entity(repositoryClass="App\Repository\PostReviewsRepository")
 */
class PostReviews
{
    /**
     * @var int
     *
     * @ORM\Column(name="review_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $reviewId;

    /**
     * @var string
     *
     * @ORM\Column(name="heading", type="string", length=255, nullable=false, options={"comment"="Tiêu đề Review"})
     */
    private $heading;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="string", length=500, nullable=true, options={"comment"="Mô tả Review"})
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(name="product_name", type="string", length=255, nullable=true, options={"comment"="Tên sản phẩm"})
     */
    private $productName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="product_description", type="string", length=500, nullable=true, options={"comment"="Mô tả sản phẩm"})
     */
    private $productDescription;

    /**
     * @var string|null
     *
     * @ORM\Column(name="product_image", type="string", length=255, nullable=true, options={"comment"="Ảnh sản phẩm"})
     */
    private $productImage;

    /**
     * @var string|null
     *
     * @ORM\Column(name="product_url", type="string", length=255, nullable=true)
     */
    private $productUrl;

    /**
     * @var string|null
     *
     * @ORM\Column(name="product_price", type="string", length=50, nullable=true, options={"comment"="Giá sản phẩm"})
     */
    private $productPrice;

    /**
     * @var string|null
     *
     * @ORM\Column(name="product_price_currency", type="string", length=5, nullable=true, options={"comment"="Đơn vị tiền tệ"})
     */
    private $productPriceCurrency;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="product_availability", type="boolean", nullable=true, options={"comment"="Tình trạng - 0: None, 1: Discontinued, 2: InStock, 3: InStoreOnly, 4: LimitedAvailability, 5: OnlineOnly, 6: OutOfStock, 7: PreSale, 8: SoldOut"})
     */
    private $productAvailability;

    /**
     * @var string
     *
     * @ORM\Column(name="total", type="decimal", precision=2, scale=1, nullable=false, options={"default"="0.0","comment"="Tổng số vote admin đánh giá"})
     */
    private $total = '0.0';

    /**
     * @var string
     *
     * @ORM\Column(name="user_reviews", type="decimal", precision=3, scale=2, nullable=false, options={"default"="0.00","comment"="Số vote của người dùng"})
     */
    private $userReviews = '0.00';

    /**
     * @var int
     *
     * @ORM\Column(name="vote_count", type="integer", nullable=false, options={"comment"="Tổng số vote"})
     */
    private $voteCount = '0';

    public function getReviewId(): ?int
    {
        return $this->reviewId;
    }

    public function getHeading(): ?string
    {
        return $this->heading;
    }

    public function setHeading(string $heading): self
    {
        $this->heading = $heading;

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

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(?string $productName): self
    {
        $this->productName = $productName;

        return $this;
    }

    public function getProductDescription(): ?string
    {
        return $this->productDescription;
    }

    public function setProductDescription(?string $productDescription): self
    {
        $this->productDescription = $productDescription;

        return $this;
    }

    public function getProductImage(): ?string
    {
        return $this->productImage;
    }

    public function setProductImage(?string $productImage): self
    {
        $this->productImage = $productImage;

        return $this;
    }

    public function getProductUrl(): ?string
    {
        return $this->productUrl;
    }

    public function setProductUrl(?string $productUrl): self
    {
        $this->productUrl = $productUrl;

        return $this;
    }

    public function getProductPrice(): ?string
    {
        return $this->productPrice;
    }

    public function setProductPrice(?string $productPrice): self
    {
        $this->productPrice = $productPrice;

        return $this;
    }

    public function getProductPriceCurrency(): ?string
    {
        return $this->productPriceCurrency;
    }

    public function setProductPriceCurrency(?string $productPriceCurrency): self
    {
        $this->productPriceCurrency = $productPriceCurrency;

        return $this;
    }

    public function getProductAvailability(): ?bool
    {
        return $this->productAvailability;
    }

    public function setProductAvailability(?bool $productAvailability): self
    {
        $this->productAvailability = $productAvailability;

        return $this;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function setTotal($total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getUserReviews()
    {
        return $this->userReviews;
    }

    public function setUserReviews($userReviews): self
    {
        $this->userReviews = $userReviews;

        return $this;
    }

    public function getVoteCount(): ?int
    {
        return $this->voteCount;
    }

    public function setVoteCount(int $voteCount): self
    {
        $this->voteCount = $voteCount;

        return $this;
    }


}
