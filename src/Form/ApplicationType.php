<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Application;
use App\Entity\Company;
use App\Entity\Platform;
use App\Enum\ApplicationStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Application>
 */
class ApplicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('company', EntityType::class, [
                'class' => Company::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisir une entreprise',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('platform', EntityType::class, [
                'class' => Platform::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisir une plateforme',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('jobTitle', TextType::class, [
                'label' => 'Titre du poste',
                'attr' => ['class' => 'form-input', 'placeholder' => 'Ex: Développeur Full-Stack'],
            ])
            ->add('jobDescription', TextareaType::class, [
                'label' => 'Description du poste',
                'required' => false,
                'attr' => ['class' => 'form-textarea', 'rows' => 5],
            ])
            ->add('status', EnumType::class, [
                'class' => ApplicationStatus::class,
                'choice_label' => fn ($choice) => $choice->label(),
                'attr' => ['class' => 'form-select'],
            ])
            ->add('applicationDate', DateType::class, [
                'label' => 'Date de candidature',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-input'],
            ])
            ->add('responseDate', DateType::class, [
                'label' => 'Date de réponse',
                'required' => false,
                'widget' => 'single_text',
                'attr' => ['class' => 'form-input'],
            ])
            ->add('jobUrl', UrlType::class, [
                'label' => 'URL de l\'offre',
                'required' => false,
                'attr' => ['class' => 'form-input', 'placeholder' => 'https://...'],
            ])
            ->add('salary', TextType::class, [
                'label' => 'Salaire proposé',
                'required' => false,
                'attr' => ['class' => 'form-input', 'placeholder' => 'Ex: 45-50K€'],
            ])
            ->add('location', TextType::class, [
                'label' => 'Localisation',
                'required' => false,
                'attr' => ['class' => 'form-input', 'placeholder' => 'Ex: Paris, Remote'],
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => ['class' => 'form-textarea', 'rows' => 3],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Application::class,
        ]);
    }
}
