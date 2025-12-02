<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AdminUserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'First Name',
                'attr' => ['class' => 'form-control', 'placeholder' => 'First Name'],
                'constraints' => [new NotBlank(['message' => 'Please enter the first name'])],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last Name',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Last Name'],
                'constraints' => [new NotBlank(['message' => 'Please enter the last name'])],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Email address'],
                'constraints' => [new NotBlank(['message' => 'Please enter the email'])],
            ])
            ->add('phoneNumber', TelType::class, [
                'label' => 'Phone Number',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Phone number'],
                'constraints' => [new NotBlank(['message' => 'Please enter the phone number'])],
            ])
            ->add('sexe', ChoiceType::class, [
                'label' => 'Gender',
                'choices' => [
                    'Male' => 'male',
                    'Female' => 'female',
                ],
                'placeholder' => 'Select gender',
                'attr' => ['class' => 'form-select'],
                'constraints' => [new NotBlank(['message' => 'Please select the gender'])],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Active' => 'active',
                    'Inactive' => 'inactive',
                ],
                'placeholder' => 'Select status',
                'attr' => ['class' => 'form-select'],
                'constraints' => [new NotBlank(['message' => 'Please select the status'])],
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Role',
                'mapped' => false,
                'choices' => [
                    'User' => 'ROLE_USER',
                    'Administrator' => 'ROLE_ADMIN',
                ],
                'placeholder' => 'Select role',
                'attr' => ['class' => 'form-select'],
                'constraints' => [new NotBlank(['message' => 'Please select a role'])],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Password',
                'mapped' => false,
                'required' => !$options['is_edit'],
                'attr' => ['class' => 'form-control', 'placeholder' => 'Password (min 8 chars with uppercase)'],
                'constraints' => $options['is_edit']
                    ? []
                    : [new NotBlank(['message' => 'Please enter a password'])],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
        ]);
    }
}


