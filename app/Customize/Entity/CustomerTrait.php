<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

use Eccube\Annotation as Eccube;

/**
  * @EntityExtension("Eccube\Entity\Customer")
 */
trait CustomerTrait
{
    
    /**
     * @var \Customize\Entity\XtbCustomerRank
     *
     * @ORM\ManyToOne(targetEntity="Customize\Entity\XtbCustomerRank", inversedBy="CustomerRank")
     * @ORM\JoinColumn(name="customer_rank_id", referencedColumnName="id", nullable=false)
     */
    private $CustomerRank;
    
    /**
     * Set XtbCustomerRank.
     *
     * @param \Customize\Entity\XtbCustomerRank|null $xtbCustomerRank
     *
     * @return Customer
     */
    public function setCustomerRank(\Customize\Entity\XtbCustomerRank $xtbCustomerRank = null)
    {
        $this->CustomerRank = $xtbCustomerRank;

        return $this;
    }

    /**
     * Get XtbCustomerRank.
     *
     * @return \Customize\Entity\XtbCustomerRank|null
     */
    public function getCustomerRank()
    {
        return $this->CustomerRank;
    }
    
}
