<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ResetForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ],
                "attr" => [
                    "placeholder" => "Email"
                ],
                "disabled" => true,
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,

                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'required' => true,
                'first_options'  => ['label' => false,  'attr' => ['placeholder' => 'Mot de passe', 'class' => 'w-full px-8 py-4 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white mb-3']],
                'second_options' => ['label' => false,  'attr' => ['placeholder' => 'Répetez le mot de passe', 'class' => 'w-full px-8 py-4 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white']],

            ])
            ->add('submit', SubmitType::class, ['label' => 'Reset']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
