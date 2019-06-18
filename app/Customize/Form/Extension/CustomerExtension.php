<?php

namespace Customize\Form\Extension;

use Eccube\Form\Type\Admin\CustomerType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

// Entityを使用する場合、指定
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

// 入力チェック等で使用する → ないとエラーになる
use Symfony\Component\Validator\Constraints as Assert;

class CustomerExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      // 会員ランクを追加
      $builder->add('CustomerRank', EntityType::class, [
                      'class' => 'Customize\Entity\XtbCustomerRank',
                      'required' => false,
                      'expanded' => false,
                      'multiple' => false,
                      'choice_label' => 'name',
                      'placeholder' => 'x.admin.customer.customer_rank.default',
                      'constraints' => [ new Assert\NotBlank() ],
                    ]
                );
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return CustomerType::class;
    }
}