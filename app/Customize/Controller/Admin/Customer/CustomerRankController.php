<?php

namespace Customize\Controller\Admin\Customer;

use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

// XtbCustomerRank データ操作
use Customize\Entity\XtbCustomerRank;
use Customize\Repository\XtbCustomerRankRepository;
use Customize\Form\Type\Admin\XtbCustomerRankType;

class CustomerRankController extends AbstractController
{
  
  /**
   * @var   XtbCustomerRankRepository
   */
  protected $xtbCustomerRankRepository;
  
  public function __construct( XtbCustomerRankRepository $xtbCustomerRankRepository)
  {
    $this->xtbCustomerRankRepository = $xtbCustomerRankRepository;
  }
    
  /**
   * @Route("/%eccube_admin_route%/customer/customer_rank", 
   *            name="admin_customer_rank")
   * @Template("@admin/Customer/customer_rank.twig")
   *
   * @param Request $request
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   */
	public function index(Request $request)
	{
    // 新規登録用
    $xtbCustomerRank = new XtbCustomerRank();
    
    // 一覧編集用
    $xtbCustomerRanks = $this->xtbCustomerRankRepository->getList();
    
    /**
     * 新規登録用フォーム（Typeをもとにフォーム作成）
     **/
    $builder = $this->formFactory->createBuilder(XtbCustomerRankType::class, $xtbCustomerRank);
    $form = $builder->getForm();

    /**
     * 編集用フォーム
     */
    $forms = [];
    foreach ($xtbCustomerRanks as $editXtbCustomerRank) {
      $id = $editXtbCustomerRank->getId();
      $forms[$id] = $this->formFactory->createNamed('customer_rank_'.$id, XtbCustomerRankType::class, $editXtbCustomerRank);
    }

    if ('POST' === $request->getMethod()) {
      /*
       * 登録処理
       */
      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {
        
        // 登録処理（各repository毎にsaveメソッドの作成が必要）
        $this->xtbCustomerRankRepository->save($form->getData());

        // 「保存しました」を表示する。
        $this->addSuccess('admin.common.save_complete', 'admin');

        // リダイレクト先を指定
        return $this->redirectToRoute('admin_customer_rank');
      }
      
      /*
       * 編集処理
       */
      foreach ($forms as $editForm) {
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
          
          // 編集処理（各repository毎にsaveメソッドの作成が必要）
          $this->xtbCustomerRankRepository->save($editForm->getData());

          // 「保存しました」を表示する。
          $this->addSuccess('admin.common.save_complete', 'admin');

          // リダイレクト先を指定
          return $this->redirectToRoute('admin_customer_rank');
        }
      }
    }

    $formViews = [];
    foreach ($forms as $key => $value) {
      $formViews[$key] = $value->createView();
    }

    return [
      'form' => $form->createView(),
      'customerRank' => $xtbCustomerRank,
      'customerRanks' => $xtbCustomerRanks,
      'forms' => $formViews,
    ];
	}
  
  /**
   * 削除処理
   * 
   * @Route("/%eccube_admin_route%/customer/customer_rank/{id}/delete", 
   *          requirements={"id" = "\d+"}, 
   *          name="admin_customer_rank_delete", 
   *          methods={"DELETE"})
   */
  public function delete(Request $request, XtbCustomerRank $xtbCustomerRank)
  {
    $this->isTokenValid();
    
    log_info('会員ランク削除開始', [$xtbCustomerRank->getId()]);

    try {
      
        // 削除処理（各repository毎にメソッドの作成不要）
        $this->xtbCustomerRankRepository->delete($xtbCustomerRank);
        
        // 削除時のメッセージ表示
        $this->addSuccess('admin.common.delete_complete', 'admin');

        log_info('会員ランク削除完了', [$xtbCustomerRank->getId()]);
        
    } catch (\Exception $e) {
      
        log_info('会員ランク削除エラー', [$xtbCustomerRank->getId(), $e]);
        
        // エラーメッセージ表示
        $message = trans('admin.common.delete_error_foreign_key', ['%name%' => $xtbCustomerRank->getName()]);
        $this->addError($message, 'admin');
    }

    return $this->redirectToRoute('admin_customer_rank');
  }
    
  /**
   * 並び替え処理
   * 
   * @Route("/%eccube_admin_route%/customer/customer_rank/sort_no/move", 
   *          name="admin_customer_rank_sort_no_move", 
   *          methods={"POST"})
   */
  public function moveSortNo(Request $request)
  {
    if ($request->isXmlHttpRequest() && $this->isTokenValid()) {
      $sortNos = $request->request->all();
      foreach ($sortNos as $customerRankId => $sortNo) {
        /* @var $xtbCustomerRank \Customize\Entity\Tag */
        $xtbCustomerRank = $this->xtbCustomerRankRepository->find($customerRankId);
        $xtbCustomerRank->setSortNo($sortNo);
        $this->entityManager->persist($xtbCustomerRank);
      }
      $this->entityManager->flush();
    }
    return new Response();
  }

}