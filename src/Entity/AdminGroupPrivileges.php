<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminGroupPrivileges
 *
 * @ORM\Table(name="admin_group_privileges", indexes={@ORM\Index(name="privilege_id", columns={"privilege_id"})})
 * @ORM\Entity
 */
class AdminGroupPrivileges
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
     * @ORM\Column(name="group_id", type="integer", nullable=false)
     */
    private $groupId;

    /**
     * @var int
     *
     * @ORM\Column(name="privilege_id", type="integer", nullable=false)
     */
    private $privilegeId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroupId(): ?int
    {
        return $this->groupId;
    }

    public function setGroupId(int $groupId): self
    {
        $this->groupId = $groupId;

        return $this;
    }

    public function getPrivilegeId(): ?int
    {
        return $this->privilegeId;
    }

    public function setPrivilegeId(int $privilegeId): self
    {
        $this->privilegeId = $privilegeId;

        return $this;
    }


}
