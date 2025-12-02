<?php

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProjectFromType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('image', FileType::class, [
                'label' => 'Votre image de projet  (Des fichiers images uniqument)',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File(
                        maxSize: '1024k',
                        extensions: ['jpg', 'jpeg', 'png'],
                        extensionsMessage: 'Please upload a valid image document',
                    )
                ],
            ])
            ->add('name')
            ->add('alldescription')
            ->add('description')
            ->add('TargetAmount')
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'html5'  => true,
                'attr' => [
                    'min' => (new \DateTime())->format('Y-m-d'),
                ],
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'html5'  => true,
                'attr' => [
                    'min' => (new \DateTime())->format('Y-m-d'),
                ],
            ])
            ->add('status')
            ->add('sauvegarder', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
