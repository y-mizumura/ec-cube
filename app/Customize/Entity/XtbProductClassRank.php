<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\ProductClass;

/**
 * @ORM\Entity(repositoryClass="Customize\Repository\XtbProductClassRankRepository")
 */
class XtbProductClassRank extends \Eccube\Entity\AbstractEntity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\ProductClass", inversedBy="ProductClassRanks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ProductClass;

    /**
     * @ORM\ManyToOne(targetEntity="Customize\Entity\XtbCustomerRank", inversedBy="ProductClassRanks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $CustomerRank;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=2)
     */
    private $price02;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductClass(): ?ProductClass
    {
        return $this->ProductClass;
    }

    public function setProductClass(?ProductClass $ProductClass): self
    {
        $this->ProductClass = $ProductClass;

        return $this;
    }

    public function getCustomerRank(): ?XtbCustomerRank
    {
        return $this->CustomerRank;
    }

    public function setCustomerRank(?XtbCustomerRank $CustomerRank): self
    {
        $this->CustomerRank = $CustomerRank;

        return $this;
    }

    public function getPrice02()
    {
        return $this->price02;
    }

    public function setPrice02($price02): self
    {
        $this->price02 = $price02;

        return $this;
    }
}
