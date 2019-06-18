<?php

namespace Customize\Controller\Block;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Controller\AbstractController;
use Eccube\Repository\ProductRepository;

/*
    2019.05.30 y.mizumura 作成
    新着情報（カスタマイズ）
 */
class NewItemController extends AbstractController
{

  /**
   * @var ProductRepository
   */
  protected $productRepository;

  public function __construct( ProductRepository $productRepository) {
    $this->productRepository = $productRepository;
  }
  
  /**
   * @Route("/block/x_new_item", name="block_x_new_item")
   * @Template("Block/x_new_item.twig")
   */
  public function index()
  {
    $Products = $this->productRepository->findBy([], null, 10);
    return ["Products" => $Products];
  }
}
