<?php

namespace Customize\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Eccube\Controller\AbstractController;
use Eccube\Entity\Member;
use Eccube\Form\Type\Admin\MemberType;
use Eccube\Repository\MemberRepository;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class MemberController extends AbstractController
{
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
        EncoderFactoryInterface $encoderFactory,
        MemberRepository $memberRepository
    ) {
        $this->encoderFactory = $encoderFactory;
        $this->memberRepository = $memberRepository;
    }
  
    /**
     * @Method("GET")
     * @Route("/member/new", name="member_new")
     * @Template("Member/index.twig")
     */
    public function create(Request $request)
    {

      // 新規メンバー作成
      $Member = new Member();
      $builder = $this->formFactory->createBuilder(MemberType::class, $Member);

      $form = $builder->getForm();
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {
          $encoder = $this->encoderFactory->getEncoder($Member);
          $salt = $encoder->createSalt();
          $rawPassword = $Member->getPassword();
          $encodedPassword = $encoder->encodePassword($rawPassword, $salt);
          $Member
              ->setSalt($salt)
              ->setPassword($encodedPassword);

          $this->memberRepository->save($Member);

          $event = new EventArgs(
              [
                  'form' => $form,
                  'Member' => $Member,
              ],
              $request
          );
          $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_SETTING_SYSTEM_MEMBER_EDIT_COMPLETE, $event);

          $this->addSuccess('admin.common.save_complete', 'admin');

          return $this->redirectToRoute('admin_setting_system_member_edit', ['id' => $Member->getId()]);
      }

//      $this->tokenStorage->getToken()->setUser($LoginMember);

      return [
          'form' => $form->createView(),
          'Member' => $Member,
      ];
    }
}