<?php

namespace Customize\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Eccube\Event\EventArgs;

use Eccube\Event\EccubeEvents;
use Eccube\Common\EccubeConfig;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * 【最終的に使用しない】
 * ・すでに登録されているイベントに対してアクションを追加する際に使用する。
 * ・今回は、「EccubeEvents::ADMIN_CUSTOMER_EDIT_INDEX_INITIALIZE」に対してフォームの項目追加を行った。
 * 
 */
class CustomerSubscriber implements EventSubscriberInterface
{
  private $eccubeConfig;
  
    public function __construct(EccubeConfig $eccubeConfig) {
      $this->eccubeConfig = $eccubeConfig;
    }
  
    public function onAdminCustomerEditIndexInitialize(EventArgs $event)
    {
      $builder = $event->getArgument('builder');

      // 料金区分を追加
      $builder->add('CustomerRank', EntityType::class, [
                        'class' => 'Customize\Entity\XtbCustomerRank',
                        'required' => false,
                        'expanded' => false,
                        'multiple' => false,
                        'choice_label' => 'name'
                    ]
                );
    }

    public static function getSubscribedEvents()
    {
        return [
            //EccubeEvents::ADMIN_CUSTOMER_EDIT_INDEX_INITIALIZE => 'onAdminCustomerEditIndexInitialize',
        ];
    }
}