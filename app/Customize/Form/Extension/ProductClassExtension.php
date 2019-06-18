<?php

namespace Customize\Form\Extension;

use Customize\Repository\XtbProductClassRankRepository;
use Customize\Repository\XtbCustomerRankRepository;

use Eccube\Form\Type\Admin\ProductType;
use Eccube\Form\Type\Admin\ProductClassType;
//use Customize\Form\Type\Admin\RankPriceType;
use Eccube\Form\Type\PriceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Customize\Form\Type\Admin\RankPriceType;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

use Customize\Entity\XtbProductClassRank;

// 入力チェック等で使用する → ないとエラーになる
use Symfony\Component\Validator\Constraints as Assert;

class ProductClassExtension extends AbstractTypeExtension
{
    
    // あとで使う？「rank_price_list」のラベル、価格を設定する。
    protected $xtbProductClassRankRepository;
    
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
                'prototype' => true,
//                'mapped' => false, // ProductClassEntityには「rank_prices」が存在しないため、falseとする。
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
      return ProductClassType::class;
    }
}