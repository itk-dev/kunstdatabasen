<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Form;

use App\Entity\Furniture;
use App\Service\TagService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FurnitureType.
 */
class FurnitureType extends AbstractType
{
    /* @var TagService $tagService */
    private $tagService;

    /**
     * ArtworkType constructor.
     *
     * @param \App\Service\TagService $tagService
     */
    public function __construct(TagService $tagService)
    {
        $this->tagService = $tagService;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* @var Furniture $furniture */
        $furniture = $options['data'];

        $classname = \get_class($furniture);

        $builder
            ->add('name')
            ->add('description')
            ->add('status', ChoiceType::class, [
                'attr' => [
                    'class' => 'tag-select-edit',
                ],
                'choices' => $this->tagService->getChoices($classname, 'status'),
                'required' => false,
            ])
            ->add('building', ChoiceType::class, [
                'attr' => [
                    'class' => 'tag-select-edit',
                ],
                'choices' => $this->tagService->getChoices($classname, 'building'),
                'required' => false,
            ])
            ->add('organization', ChoiceType::class, [
                'attr' => [
                    'class' => 'tag-select-edit',
                ],
                'choices' => $this->tagService->getChoices($classname, 'organization'),
                'required' => false,
            ])
            ->add('type', ChoiceType::class, [
                'attr' => [
                    'class' => 'tag-select-edit',
                ],
                'choices' => $this->tagService->getChoices($classname, 'type'),
                'required' => false,
            ])
            ->add('address', ChoiceType::class, [
                'attr' => [
                    'class' => 'tag-select-edit',
                ],
                'choices' => $this->tagService->getChoices($classname, 'address'),
                'required' => false,
            ])
            ->add('location', ChoiceType::class, [
                'attr' => [
                    'class' => 'tag-select-edit',
                ],
                'choices' => $this->tagService->getChoices($classname, 'location'),
                'required' => false,
            ])
            ->add('room', ChoiceType::class, [
                'attr' => [
                    'class' => 'tag-select-edit',
                ],
                'choices' => $this->tagService->getChoices($classname, 'room'),
                'required' => false,
            ])
            ->add('city', ChoiceType::class, [
                'attr' => [
                    'class' => 'tag-select-edit',
                ],
                'choices' => $this->tagService->getChoices($classname, 'city'),
                'required' => false,
            ])
            ->add('postalCode')
            ->add('geo')
            ->add('comment')
            ->add('publiclyAccessible')
            ->add(
                'images',
                CollectionType::class,
                [
                    'entry_type' => ImageType::class,
                    'entry_options' => [
                        'label' => false,
                        'required' => false,
                    ],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                ]
            );

        // Allow for new options from the user.
        $builder->get('building')->resetViewTransformers();
        $builder->get('organization')->resetViewTransformers();
        $builder->get('type')->resetViewTransformers();
        $builder->get('address')->resetViewTransformers();
        $builder->get('location')->resetViewTransformers();
        $builder->get('room')->resetViewTransformers();
        $builder->get('city')->resetViewTransformers();
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Furniture::class,
            ]
        );
    }
}
