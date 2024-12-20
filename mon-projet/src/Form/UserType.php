<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 3, 'max' => 100]),
                ],
                'attr' => [
                    'class' => 'block w-full p-3 mt-2 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent'
                ]
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ],
                'attr' => [
                    'class' => 'block w-full p-3 mt-2 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent'
                ]
            ])
            ->add('password', PasswordType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 6]),
                ],
                'attr' => [
                    'class' => 'block w-full p-3 mt-2 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent'
                ]
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Admin' => 'ROLE_ADMIN',
                    'User' => 'ROLE_USER',
                    'User' => 'ROLE_USER',
                ],
                'multiple' => true,
                'expanded' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'attr' => [
                    'class' => 'block w-full p-3 mt-2 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
