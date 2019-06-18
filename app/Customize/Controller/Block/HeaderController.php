<?php

namespace Customize\Controller\Block;

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/*
 * DEBUGで作成
 * ※dtb_block.use_controller -> trueに設定が必要
 */
class HeaderController extends AbstractController
{
    /**
     * @Route("/block/header", name="block_header")
     * @Template("Block/header.twig")
     */
    public function index(Request $request)
    {
        echo "DEBUG";
        return [];
    }
}
