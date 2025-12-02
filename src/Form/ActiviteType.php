<?php

namespace App\Form;

use App\Entity\Activite;
use App\Entity\Benevole;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActiviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class)
            ->add('description')
            ->add('date', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('lieu', TextType::class)
            ->add('benevoles', EntityType::class, [
                'class' => Benevole::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => true, // checkboxes
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Activite::class,
        ]);
    }
}
