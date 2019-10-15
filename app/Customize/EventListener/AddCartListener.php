<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Customize\EventListener;

use Eccube\Event\EccubeEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
//use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
//use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Eccube\Event\EventArgs;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AddCartListener implements EventSubscriberInterface
{
    private $router;
    
    public function __construct(UrlGeneratorInterface $router) {
        $this->router = $router;
    }

    public function onResponse(EventArgs $event)
    {
        return new RedirectResponse( $this->router->generate('shopping') );
        //$this->redirectToRoute('shopping');
    }

    public static function getSubscribedEvents()
    {
        return [
            EccubeEvents::FRONT_PRODUCT_CART_ADD_COMPLETE => 'onResponse',
        ];
    }
}