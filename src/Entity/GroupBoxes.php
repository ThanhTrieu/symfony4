<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GroupBoxes
 *
 * @ORM\Table(name="group_boxes")
 * @ORM\Entity(repositoryClass="App\Repository\GroupBoxesRepository")
 */
class GroupBoxes
{
    /**
     * @var int
     *
     * @ORM\Column(name="box_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $boxId;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false, options={"comment"="Tiêu đề box"})
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="key", type="string", length=50, nullable=false, options={"comment"="Khóa để hiển thị"})
     */
    private $key;

    /**
     * @var bool
     *
     * @ORM\Column(name="type", type="boolean", nullable=false, options={"comment"="0:Box short link, 1: Box full link, 2: Box posts"})
     */
    private $type = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="item_json", type="string", length=2000, nullable=true, options={"comment"="type=[0,1]: json; type=2:post_id list"})
     */
    private $itemJson;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP","comment"="Ngày tạo"})
     */
    private $createdDate = 'CURRENT_TIMESTAMP';

    public function getBoxId(): ?int
    {
        return $this->boxId;
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

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function getType(): ?bool
    {
        return $this->type;
    }

    public function setType(bool $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getItemJson(): ?string
    {
        return $this->itemJson;
    }

    public function setItemJson(?string $itemJson): self
    {
        $this->itemJson = $itemJson;

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


}
