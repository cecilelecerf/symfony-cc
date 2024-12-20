<?php

namespace App\Form;

use App\Entity\Publication;
use App\Entity\Tag;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

class PublicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'title',
                TextType::class,
                ['constraints' => [
                    new Assert\NotBlank([
                        'message' => 'The title cannot be empty.',
                    ]),
                    new Assert\Length([
                        'min' => 5,
                        'minMessage' => 'The title must be at least {{ limit }} characters long.',
                        'max' => 60,
                        'maxMessage' => 'The title cannot be longer than {{ limit }} characters.',
                    ])
                ]]
            )
            ->add(
                'content',
                TextType::class,
                ['constraints' => [
                    new Assert\NotBlank([
                        'message' => 'The content cannot be empty.',
                    ]),
                    new Assert\Length([
                        'min' => 5,
                        'minMessage' => 'The content must be at least {{ limit }} characters long.',
                        'max' => 255,
                        'maxMessage' => 'The content cannot be longer than {{ limit }} characters.',
                    ])
                ]]
            )
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'label',
                'multiple' => true,
                'expanded' => true,   // Ajoute des cases à cocher pour chaque tag (optionnelle)

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Publication::class,
        ]);
    }
}
