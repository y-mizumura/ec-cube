<?php

namespace Customize\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Eccube\Controller\AbstractController;
use Eccube\Entity\Member;
use Eccube\Repository\MemberRepository;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

use Customize\Form\Type\MemberType;

use Eccube\Repository\Master\AuthorityRepository;

class MemberController extends AbstractController
{
    /**
     * @var AuthorityRepository
     */
    protected $authorityMasterRepository;
  
    /**
     * @var MemberRepository
     */
    protected $memberRepository;

    /**
     * @var EncoderFactoryInterface
     */
    protected $encoderFactory;
  
    /**
     * MemberController constructor.
     *
     * @param EncoderFactoryInterface $encoderFactory
     * @param MemberRepository $memberRepository
     */
    public function __construct(
        AuthorityRepository $authorityMasterRepository,
        EncoderFactoryInterface $encoderFactory,
        MemberRepository $memberRepository
    ) {
        $this->authorityMasterRepository = $authorityMasterRepository;
        $this->encoderFactory = $encoderFactory;
        $this->memberRepository = $memberRepository;
    }
  
    /**
     * 出品者登録申請 申請画面
     * 
     * @Route("/member/new", name="member_new")
     * @Template("Member/index.twig")
     */
    public function create(Request $request)
    {
      // 新規メンバー作成
      $Member = new Member();
      
      // フォーム作成
      $builder = $this->formFactory->createBuilder(MemberType::class, $Member);

      $form = $builder->getForm();
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {
          
          // 登録処理
          $encoder = $this->encoderFactory->getEncoder($Member);
          $salt = $encoder->createSalt();
          $rawPassword = $Member->getPassword();
          $encodedPassword = $encoder->encodePassword($rawPassword, $salt);
          $Member
              ->setSalt($salt)
              ->setPassword($encodedPassword);

          // フォームにない項目追加
          $Work = $this->entityManager->find(\Eccube\Entity\Master\Work::class, 0);
          $Authority = $this->entityManager->find(\Eccube\Entity\Master\Authority::class, 1);
          $Creator = $this->entityManager->find(\Eccube\Entity\Member::class, 1);
          $Member->setWork($Work);
          $Member->setAuthority($Authority);
          $Member->setCreator($Creator);

          $this->memberRepository->save($Member);
          
          // 申請完了画面にリダイレクト
          return $this->redirectToRoute('member_complete');
      }

      return [
          'form' => $form->createView(),
          'Member' => $Member,
      ];
    }
    
    /**
     * 出品者登録申請 完了画面
     *
     * @Route("/member/complete", name="member_complete")
     * @Template("Member/complete.twig")
     */
    public function complete()
    {
        return [];
    }

}