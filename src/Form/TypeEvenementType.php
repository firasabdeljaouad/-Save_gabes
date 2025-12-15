<?php

namespace App\Form;

use App\Entity\TypeEvenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypeEvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', ChoiceType::class, [
                'label' => 'Nom',
                'choices' => [
                    'Action de protection de la côte' => 'Action de protection de la côte',
                    'Préservation de l\'oasis' => 'Préservation de l\'oasis',
                    'Lutte contre la pollution industrielle' => 'Lutte contre la pollution industrielle',
                    'Recyclage et gestion des déchets' => 'Recyclage et gestion des déchets',
                    'Campagnes écologiques' => 'Campagnes écologiques',
                    'Conférences environnementales' => 'Conférences environnementales',
                ],
                'placeholder' => 'Choisissez un type d\'événement',
                'attr' => ['class' => 'form-control']
            ])
            ->add('organisateur', TextType::class, [
                'label' => 'Organisateur',
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'rows' => 4]
            ])
            ->add('partenaires', TextType::class, [
                'label' => 'Partenaires',
                'attr' => ['class' => 'form-control']
            ])
            ->add('materielNecessaire', TextType::class, [
                'label' => 'Materiel nécessaire',
                'attr' => ['class' => 'form-control']
            ])
            ->add('Ajouter', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary mt-3']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TypeEvenement::class,
        ]);
    }
}
