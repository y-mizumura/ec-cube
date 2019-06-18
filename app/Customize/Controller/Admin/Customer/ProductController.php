<?php

namespace Customize\Controller\Admin\Customer;

use Eccube\Entity\Master\ProductStatus;
use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;
use Eccube\Entity\ProductImage;
use Eccube\Entity\ProductStock;
use Eccube\Entity\ProductTag;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\Admin\ProductType;
use Eccube\Form\Type\Admin\SearchProductType;
use Eccube\Util\CacheUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

// コンストラクタ等を用意無いといけないため、元クラスを拡張する。
// TODO
// 　この方法でも大丈夫か、黒住さんに要確認
use Eccube\Controller\Admin\Product\ProductController as ParentClass;
//use Eccube\Controller\AbstractController;

// Repository
use Eccube\Service\CsvExportService; 
use Eccube\Repository\ProductClassRepository; 
use Eccube\Repository\ProductImageRepository; 
use Eccube\Repository\TaxRuleRepository; 
use Eccube\Repository\CategoryRepository; 
use Eccube\Repository\ProductRepository; 
use Eccube\Repository\BaseInfoRepository; 
use Eccube\Repository\Master\PageMaxRepository; 
use Eccube\Repository\Master\ProductStatusRepository; 
use Eccube\Repository\TagRepository;
use Customize\Repository\XtbCustomerRankRepository;
use Customize\Repository\XtbProductClassRankRepository;

// Entity
use Customize\Entity\XtbProductClassRank;

class ProductController extends ParentClass
{
  
  protected $xtbCustomerRankRepository;
  
  protected $xtbProductClassRankRepository;
  
  public function __construct(
        CsvExportService $csvExportService,
        ProductClassRepository $productClassRepository,
        ProductImageRepository $productImageRepository,
        TaxRuleRepository $taxRuleRepository,
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository,
        BaseInfoRepository $baseInfoRepository,
        PageMaxRepository $pageMaxRepository,
        ProductStatusRepository $productStatusRepository,
        TagRepository $tagRepository,
        XtbCustomerRankRepository $xtbCustomerRankRepository,
        XtbProductClassRankRepository $xtbProductClassRankRepository
    ) {
      // 親のコンストラクタ呼び出し
      parent::__construct($csvExportService, $productClassRepository, $productImageRepository, $taxRuleRepository, $categoryRepository, $productRepository, $baseInfoRepository, $pageMaxRepository, $productStatusRepository, $tagRepository);
      $this->xtbCustomerRankRepository = $xtbCustomerRankRepository;
      $this->xtbProductClassRankRepository = $xtbProductClassRankRepository;
    
  }
    
    /**
     * @Route("/%eccube_admin_route%/product/product/new", name="admin_product_product_new")
     * @Route("/%eccube_admin_route%/product/product/{id}/edit", requirements={"id" = "\d+"}, name="admin_product_product_edit")
     * @Template("@admin/Product/product.twig")
     */
    public function edit(Request $request, $id = null, RouterInterface $router, CacheUtil $cacheUtil)
    {
        $has_class = false;
        
        if (is_null($id)) {
            $Product = new Product();
            $ProductClass = new ProductClass();
            $ProductStatus = $this->productStatusRepository->find(ProductStatus::DISPLAY_HIDE);
            $Product
                ->addProductClass($ProductClass)
                ->setStatus($ProductStatus);
            $ProductClass
                ->setVisible(true)
                ->setStockUnlimited(true)
                ->setProduct($Product);
            $ProductStock = new ProductStock();
            $ProductClass->setProductStock($ProductStock);
            $ProductStock->setProductClass($ProductClass);
        } else {
            $Product = $this->productRepository->find($id);
            if (!$Product) {
                throw new NotFoundHttpException();
            }
            // 規格無しの商品の場合は、デフォルト規格を表示用に取得する
            $has_class = $Product->hasProductClass();
            if (!$has_class) {
                $ProductClasses = $Product->getProductClasses();
                foreach ($ProductClasses as $pc) {
                    if (!is_null($pc->getClassCategory1())) {
                        continue;
                    }
                    if ($pc->isVisible()) {
                        $ProductClass = $pc;
                        break;
                    }
                }
                if ($this->BaseInfo->isOptionProductTaxRule() && $ProductClass->getTaxRule()) {
                    $ProductClass->setTaxRate($ProductClass->getTaxRule()->getTaxRate());
                }
                $ProductStock = $ProductClass->getProductStock();
            }
        }
        
        // FormType: RankPriceの生成 START
        // 会員ランクテーブル 全件取得
        $CustomerRanks = $this->xtbCustomerRankRepository->findAll();
        
        // 会員別ランクの枠表示用
        // 会員ランクマスタを取得
        $Ranks = $this->xtbCustomerRankRepository->findAll();
        
        foreach($Product->getProductClasses() as $ProductClass) {
          foreach($Ranks as $Rank) {
            
            $rank_ids = array_map( function($ProductClassRank){
                          return $ProductClassRank->getId();
                        },$ProductClass->getProductClassRanks()->toArray());
            
            // 対象の商品クラスオブジェクトに紐づくProductClassRankオブジェクトが存在しない場合、
            // 新規にProductClassRankオブジェクトをフォームに追加する。
            // ※Dataがある場合、Doctorineが自動でデータのヒモ付をおこなう。
            if ( !in_array($Rank->getId(), $rank_ids)) {
               
              // 商品クラスランクデータが必要
                $ProductClassRank = new XtbProductClassRank();
                $ProductClassRank->setProductClass($ProductClass);
                $ProductClassRank->setCustomerRank($Rank);
                $ProductClass->addProductClassRank($ProductClassRank);
            }
            
          }
        }
        
        // FormType: RankPriceの生成 END

        $builder = $this->formFactory
            ->createBuilder(ProductType::class, $Product);

        // 規格あり商品の場合、規格関連情報をFormから除外
        if ($has_class) {
            $builder->remove('class');
        }

        $event = new EventArgs(
            [
                'builder' => $builder,
                'Product' => $Product,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_PRODUCT_EDIT_INITIALIZE, $event);

        $form = $builder->getForm();

        if (!$has_class) {
            $ProductClass->setStockUnlimited($ProductClass->isStockUnlimited());
            
            // 【追加】
//            var_dump($ProductClass->getProductClassRanks());
            
            $form['class']->setData($ProductClass);
            
            // 【追加】test
//            $ProductClassRank_test = new XtbProductClassRank();
//            $ProductClassRank_test->setPrice02(10000);
//            $ProductClassRank_test->setCustomerRank($this->xtbCustomerRankRepository->find(1));
//            $ProductClassRank_test->setProductClass($this->productClassRepository->find(26));
//            $form['class']['rank_prices']->setData($ProductClassRank_test);
        }

        // ファイルの登録
        $images = [];
        $ProductImages = $Product->getProductImage();
        foreach ($ProductImages as $ProductImage) {
            $images[] = $ProductImage->getFileName();
        }
        $form['images']->setData($images);

        $categories = [];
        $ProductCategories = $Product->getProductCategories();
        foreach ($ProductCategories as $ProductCategory) {
            /* @var $ProductCategory \Eccube\Entity\ProductCategory */
            $categories[] = $ProductCategory->getCategory();
        }
        $form['Category']->setData($categories);

        $Tags = $Product->getTags();
        $form['Tag']->setData($Tags);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                log_info('商品登録開始', [$id]);
                $Product = $form->getData();

                if (!$has_class) {
                    $ProductClass = $form['class']->getData();

                    // 個別消費税
                    if ($this->BaseInfo->isOptionProductTaxRule()) {
                        if ($ProductClass->getTaxRate() !== null) {
                            if ($ProductClass->getTaxRule()) {
                                $ProductClass->getTaxRule()->setTaxRate($ProductClass->getTaxRate());
                            } else {
                                $taxrule = $this->taxRuleRepository->newTaxRule();
                                $taxrule->setTaxRate($ProductClass->getTaxRate());
                                $taxrule->setApplyDate(new \DateTime());
                                $taxrule->setProduct($Product);
                                $taxrule->setProductClass($ProductClass);
                                $ProductClass->setTaxRule($taxrule);
                            }

                            $ProductClass->getTaxRule()->setTaxRate($ProductClass->getTaxRate());
                        } else {
                            if ($ProductClass->getTaxRule()) {
                                $this->taxRuleRepository->delete($ProductClass->getTaxRule());
                                $ProductClass->setTaxRule(null);
                            }
                        }
                    }
                    $this->entityManager->persist($ProductClass);

                    // 在庫情報を作成
                    if (!$ProductClass->isStockUnlimited()) {
                        $ProductStock->setStock($ProductClass->getStock());
                    } else {
                        // 在庫無制限時はnullを設定
                        $ProductStock->setStock(null);
                    }
                    $this->entityManager->persist($ProductStock);
                }

                // カテゴリの登録
                // 一度クリア
                /* @var $Product \Eccube\Entity\Product */
                foreach ($Product->getProductCategories() as $ProductCategory) {
                    $Product->removeProductCategory($ProductCategory);
                    $this->entityManager->remove($ProductCategory);
                }
                $this->entityManager->persist($Product);
                $this->entityManager->flush();

                $count = 1;
                $Categories = $form->get('Category')->getData();
                $categoriesIdList = [];
                foreach ($Categories as $Category) {
                    foreach ($Category->getPath() as $ParentCategory) {
                        if (!isset($categoriesIdList[$ParentCategory->getId()])) {
                            $ProductCategory = $this->createProductCategory($Product, $ParentCategory, $count);
                            $this->entityManager->persist($ProductCategory);
                            $count++;
                            /* @var $Product \Eccube\Entity\Product */
                            $Product->addProductCategory($ProductCategory);
                            $categoriesIdList[$ParentCategory->getId()] = true;
                        }
                    }
                    if (!isset($categoriesIdList[$Category->getId()])) {
                        $ProductCategory = $this->createProductCategory($Product, $Category, $count);
                        $this->entityManager->persist($ProductCategory);
                        $count++;
                        /* @var $Product \Eccube\Entity\Product */
                        $Product->addProductCategory($ProductCategory);
                        $categoriesIdList[$ParentCategory->getId()] = true;
                    }
                }

                // 画像の登録
                $add_images = $form->get('add_images')->getData();
                foreach ($add_images as $add_image) {
                    $ProductImage = new \Eccube\Entity\ProductImage();
                    $ProductImage
                        ->setFileName($add_image)
                        ->setProduct($Product)
                        ->setSortNo(1);
                    $Product->addProductImage($ProductImage);
                    $this->entityManager->persist($ProductImage);

                    // 移動
                    $file = new File($this->eccubeConfig['eccube_temp_image_dir'].'/'.$add_image);
                    $file->move($this->eccubeConfig['eccube_save_image_dir']);
                }

                // 画像の削除
                $delete_images = $form->get('delete_images')->getData();
                foreach ($delete_images as $delete_image) {
                    $ProductImage = $this->productImageRepository
                        ->findOneBy(['file_name' => $delete_image]);

                    // 追加してすぐに削除した画像は、Entityに追加されない
                    if ($ProductImage instanceof ProductImage) {
                        $Product->removeProductImage($ProductImage);
                        $this->entityManager->remove($ProductImage);
                    }
                    $this->entityManager->persist($Product);

                    // 削除
                    $fs = new Filesystem();
                    $fs->remove($this->eccubeConfig['eccube_save_image_dir'].'/'.$delete_image);
                }
                $this->entityManager->persist($Product);
                $this->entityManager->flush();

                $sortNos = $request->get('sort_no_images');
                if ($sortNos) {
                    foreach ($sortNos as $sortNo) {
                        list($filename, $sortNo_val) = explode('//', $sortNo);
                        $ProductImage = $this->productImageRepository
                            ->findOneBy([
                                'file_name' => $filename,
                                'Product' => $Product,
                            ]);
                        $ProductImage->setSortNo($sortNo_val);
                        $this->entityManager->persist($ProductImage);
                    }
                }
                $this->entityManager->flush();

                // 商品タグの登録
                // 商品タグを一度クリア
                $ProductTags = $Product->getProductTag();
                foreach ($ProductTags as $ProductTag) {
                    $Product->removeProductTag($ProductTag);
                    $this->entityManager->remove($ProductTag);
                }

                // 商品タグの登録
                $Tags = $form->get('Tag')->getData();
                foreach ($Tags as $Tag) {
                    $ProductTag = new ProductTag();
                    $ProductTag
                        ->setProduct($Product)
                        ->setTag($Tag);
                    $Product->addProductTag($ProductTag);
                    $this->entityManager->persist($ProductTag);
                }

                $Product->setUpdateDate(new \DateTime());
                $this->entityManager->flush();

                log_info('商品登録完了', [$id]);

                $event = new EventArgs(
                    [
                        'form' => $form,
                        'Product' => $Product,
                    ],
                    $request
                );
                $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_PRODUCT_EDIT_COMPLETE, $event);

                $this->addSuccess('admin.common.save_complete', 'admin');

                if ($returnLink = $form->get('return_link')->getData()) {
                    try {
                        // $returnLinkはpathの形式で渡される. pathが存在するかをルータでチェックする.
                        $pattern = '/^'.preg_quote($request->getBasePath(), '/').'/';
                        $returnLink = preg_replace($pattern, '', $returnLink);
                        $result = $router->match($returnLink);
                        // パラメータのみ抽出
                        $params = array_filter($result, function ($key) {
                            return 0 !== \strpos($key, '_');
                        }, ARRAY_FILTER_USE_KEY);

                        // pathからurlを再構築してリダイレクト.
                        return $this->redirectToRoute($result['_route'], $params);
                    } catch (\Exception $e) {
                        // マッチしない場合はログ出力してスキップ.
                        log_warning('URLの形式が不正です。');
                    }
                }

                $cacheUtil->clearDoctrineCache();

                return $this->redirectToRoute('admin_product_product_edit', ['id' => $Product->getId()]);
            }
        }

        // 検索結果の保持
        $builder = $this->formFactory
            ->createBuilder(SearchProductType::class);

        $event = new EventArgs(
            [
                'builder' => $builder,
                'Product' => $Product,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_PRODUCT_EDIT_SEARCH, $event);

        $searchForm = $builder->getForm();

        if ('POST' === $request->getMethod()) {
            $searchForm->handleRequest($request);
        }

        // Get Tags
        $TagsList = $this->tagRepository->getList();

        // ツリー表示のため、ルートからのカテゴリを取得
        $TopCategories = $this->categoryRepository->getList(null);
        $ChoicedCategoryIds = array_map(function ($Category) {
            return $Category->getId();
        }, $form->get('Category')->getData());
        

        return [
            'Product' => $Product,
            'Tags' => $Tags,
            'TagsList' => $TagsList,
            'form' => $form->createView(),
            'searchForm' => $searchForm->createView(),
            'has_class' => $has_class,
            'id' => $id,
            'TopCategories' => $TopCategories,
            'ChoicedCategoryIds' => $ChoicedCategoryIds
        ];
    }


}