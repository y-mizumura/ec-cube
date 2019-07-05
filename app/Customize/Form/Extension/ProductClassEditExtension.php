<?php

namespace Customize\Form\Extension;

use Customize\Repository\XtbCustomerRankRepository;
use Customize\Form\Type\Admin\RankPriceType;
use Eccube\Form\Type\Admin\ProductClassEditType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class ProductClassEditExtension extends AbstractTypeExtension
{
    
    protected $xtbCustomerRankRepository;
    
    public function __construct(XtbCustomerRankRepository $xtbCustomerRankRepository) {
      $this->xtbCustomerRankRepository = $xtbCustomerRankRepository;
    }
  
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      
      // DeliveryTypeを参考に作成
      $builder->add('product_class_ranks', CollectionType::class, [
                'required' => true,
                'entry_type' => RankPriceType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
      return ProductClassEditType::class;
    }
}