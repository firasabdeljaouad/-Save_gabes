<?php

namespace App\Form;

use App\Entity\Evenement;
use App\Entity\TypeEvenement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le titre']
            ])
            ->add('lieu', TextType::class, [
                'label' => 'Lieu',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le lieu']
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez l\'adresse complète']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Décrivez l\'événement']
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('dateFin', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('nombreParticipants', IntegerType::class, [
                'label' => 'Nombre de participants',
                'attr' => ['class' => 'form-control', 'min' => 0],
                'required' => false
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image de l\'événement',
                'required' => false,
                'mapped' => false, // This is important - field is not mapped to entity
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG ou GIF)',
                    ])
                ],
                'help' => 'Formats supportés: JPG, PNG, GIF. Taille max: 2MB'
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'attr' => ['class' => 'form-control'],
                'choices' => [
                    'En attente' => 'en attente',
                    'Confirmé' => 'confirmé',
                    'Annulé' => 'annulé',
                    'Terminé' => 'terminé'
                ],
                'required' => false
            ])

            ->add('typeEvenement', EntityType::class, [
                'class' => TypeEvenement::class,
                'choice_label' => 'nom',
                'label' => 'Type d\'événement',
                'placeholder' => 'Sélectionnez un type',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Ajouter',
                'attr' => [
                    'class' => 'btn btn-success px-4 py-2 mt-3'
                ]
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}
