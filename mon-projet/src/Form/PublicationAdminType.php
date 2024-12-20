<?php
// src/Form/PublicationType.php
namespace App\Form;

use App\Entity\Publication;
use App\Entity\Tag;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PublicationAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr' => ['class' => 'block w-full p-3 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent']
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Content',
                'attr' => ['class' => 'block w-full p-3 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent']
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'multiple' => true,
                'expanded' => true, // pour afficher les tags comme des cases à cocher
                'choice_label' => 'label',
                'label' => 'Tags',
                'attr' => ['class' => 'space-y-2']
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save Publication',
                'attr' => ['class' => 'w-full bg-blue-600 text-white p-3 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Publication::class,
        ]);
    }
}
