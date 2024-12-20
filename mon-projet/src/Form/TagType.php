<?php

namespace App\Form;

use App\Entity\Publication;
use App\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class TagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ label avec contrainte de longueur
            ->add('label', null, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le label ne peut pas être vide.']),
                    new Assert\Length(['max' => 255, 'maxMessage' => 'Le label ne peut pas dépasser 255 caractères.']),
                ],
                'attr' => [
                    'class' => 'block w-full p-3 mt-2 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent'
                ]
            ])
            // Champ publications avec relation entre les publications et les tags
            ->add('publications', EntityType::class, [
                'class' => Publication::class,
                'choice_label' => 'title', // Choix basés sur le titre de la publication
                'multiple' => true,
                'expanded' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez sélectionner au moins une publication.']),
                ],
                'attr' => [
                    'class' => 'block w-full p-3 mt-2 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tag::class,
        ]);
    }
}
