<?php

namespace Customize\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Customize\Repository\XtbCustomerRankRepository")
 */
class XtbCustomerRank extends \Eccube\Entity\AbstractEntity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="smallint")
     */
    private $sort_no;
    
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="Eccube\Entity\Customer", mappedBy="id")
     */
    private $customers;

    /**
     * @ORM\OneToMany(targetEntity="Customize\Entity\XtbProductClassRank", mappedBy="CustomerRank")
     */
    private $ProductClassRanks;

    public function __construct()
    {
        $this->ProductClassRanks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSortNo(): ?int
    {
        return $this->sort_no;
    }

    public function setSortNo(int $sort_no): self
    {
        $this->sort_no = $sort_no;

        return $this;
    }
    
    public function getCustomers(): ?array
    {
        return $this->customers;
    }

    public function setCustomers(int $customers): self
    {
        $this->customers = $customers;

        return $this;
    }

    /**
     * @return Collection|XtbProductClassRank[]
     */
    public function getProductClassRanks(): Collection
    {
        return $this->ProductClassRanks;
    }

    public function addProductClassRank(XtbProductClassRank $productClassRank): self
    {
        if (!$this->ProductClassRanks->contains($productClassRank)) {
            $this->ProductClassRanks[] = $productClassRank;
            $productClassRank->setCustomerRank($this);
        }

        return $this;
    }

    public function removeProductClassRank(XtbProductClassRank $productClassRank): self
    {
        if ($this->ProductClassRanks->contains($productClassRank)) {
            $this->ProductClassRanks->removeElement($productClassRank);
            // set the owning side to null (unless already changed)
            if ($productClassRank->getCustomerRank() === $this) {
                $productClassRank->setCustomerRank(null);
            }
        }

        return $this;
    }
}
