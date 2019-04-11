<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminGroups
 *
 * @ORM\Table(name="admin_groups")
 * @ORM\Entity
 */
class AdminGroups
{
    /**
     * @var int
     *
     * @ORM\Column(name="group_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $groupId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="title", type="string", length=200, nullable=true, options={"comment"="Tên nhóm quản trị"})
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(name="slug", type="string", length=200, nullable=true, options={"comment"="Key name dùng để phân quyền: administrator, editor, author, contributor"})
     */
    private $slug;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="status", type="boolean", nullable=true)
     */
    private $status = '0';

    public function getGroupId(): ?int
    {
        return $this->groupId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): self
    {
        $this->status = $status;

        return $this;
    }


}
