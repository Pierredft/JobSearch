<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Platform;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Platform>
 */
class PlatformType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la plateforme',
                'attr' => ['class' => 'form-input', 'placeholder' => 'Ex: LinkedIn'],
            ])
            ->add('url', UrlType::class, [
                'label' => 'URL',
                'required' => false,
                'attr' => ['class' => 'form-input', 'placeholder' => 'https://...'],
            ])
            ->add('logoUrl', UrlType::class, [
                'label' => 'URL du logo',
                'required' => false,
                'attr' => ['class' => 'form-input', 'placeholder' => 'https://...'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Platform::class,
        ]);
    }
}
