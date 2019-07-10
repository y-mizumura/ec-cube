<?php

namespace Customize\Form\Type;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\Master\Authority;
use Eccube\Entity\Master\Work;
use Eccube\Entity\Member;
use Eccube\Repository\MemberRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Eccube\Form\Type\RepeatedPasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class MemberType extends AbstractType
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @var MemberRepository
     */
    protected $memberRepository;

    /**
     * MemberType constructor.
     *
     * @param EccubeConfig $eccubeConfig
     * @param MemberRepository $memberRepository
     */
    public function __construct(
        EccubeConfig $eccubeConfig,
        MemberRepository $memberRepository
    ) {
        $this->eccubeConfig = $eccubeConfig;
        $this->memberRepository = $memberRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_stext_len']]),
                ],
            ])
            ->add('department', ChoiceType::class, [
                'choices' => [
                        'クリエーター' => '出品者（クリエーター）',
                        'ギャラリーオーナー' => '出品者（ギャラリーオーナー）',
                        'その他' => '出品者（その他）'
                    ],
                'constraints' => [
                        new Assert\NotBlank()
                    ]
            ])
            ->add('login_id', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => $this->eccubeConfig['eccube_id_min_len'],
                        'max' => $this->eccubeConfig['eccube_id_max_len'],
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[[:graph:][:space:]]+$/i',
                        'message' => 'form_error.graph_only',
                    ]),
                ],
            ])
            ->add('password', RepeatedPasswordType::class, [
                'first_options' => [
                    'label' => 'admin.setting.system.member.password',
                ],
                'second_options' => [
                    'label' => 'admin.setting.system.member.password',
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Eccube\Entity\Member',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'member_new';
    }
}
