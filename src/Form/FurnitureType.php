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
    /**
     * ArtworkType constructor.
     */
    public function __construct(
        private readonly TagService $tagService,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /* @var Furniture $furniture */
        $furniture = $options['data'];

        $classname = $furniture::class;

        $builder
            ->add('name', null, [
                'label' => 'item.name',
            ])
            ->add('description', null, [
                'label' => 'item.description',
            ])
            ->add('purchasePrice', null, [
                'label' => 'item.purchase_price',
            ])
            ->add('status', ChoiceType::class, [
                'attr' => [
                    'class' => 'tag-select-edit',
                ],
                'choices' => $this->tagService->getChoices($classname, 'status'),
                'required' => false,
                'label' => 'item.status',
            ])
            ->add('building', ChoiceType::class, [
                'attr' => [
                    'class' => 'tag-select-edit',
                ],
                'choices' => $this->tagService->getChoices($classname, 'building'),
                'required' => false,
                'label' => 'item.building',
            ])
            ->add('department', ChoiceType::class, [
                'attr' => [
                    'class' => 'tag-select-edit',
                ],
                'choices' => $this->tagService->getChoices($classname, 'department'),
                'required' => false,
                'label' => 'item.department',
            ])
            ->add('organization', ChoiceType::class, [
                'attr' => [
                    'class' => 'tag-select-edit',
                ],
                'choices' => $this->tagService->getChoices($classname, 'organization'),
                'required' => false,
                'label' => 'item.organization',
            ])
            ->add('type', ChoiceType::class, [
                'attr' => [
                    'class' => 'tag-select-edit',
                ],
                'choices' => $this->tagService->getChoices($classname, 'type'),
                'required' => false,
                'label' => 'item.type',
            ])
            ->add('address', ChoiceType::class, [
                'attr' => [
                    'class' => 'tag-select-edit',
                ],
                'choices' => $this->tagService->getChoices($classname, 'address'),
                'required' => false,
                'label' => 'item.address',
            ])
            ->add('location', ChoiceType::class, [
                'attr' => [
                    'class' => 'tag-select-edit',
                ],
                'choices' => $this->tagService->getChoices($classname, 'location'),
                'required' => false,
                'label' => 'item.location',
            ])
            ->add('locationDate', null, [
                'label' => 'item.location_date',
                'html5' => true,
                'widget' => 'single_text',
                'required' => false,
                'help' => 'item.location_date.helpText',
            ])
            ->add('room', ChoiceType::class, [
                'attr' => [
                    'class' => 'tag-select-edit',
                ],
                'choices' => $this->tagService->getChoices($classname, 'room'),
                'required' => false,
                'label' => 'item.room',
            ])
            ->add('city', ChoiceType::class, [
                'attr' => [
                    'class' => 'tag-select-edit',
                ],
                'choices' => $this->tagService->getChoices($classname, 'city'),
                'required' => false,
                'label' => 'item.city',
            ])
            ->add('postalCode', null, [
                'label' => 'item.postal_code',
            ])
            ->add('publiclyAccessible', null, [
                'label' => 'item.publicly_accessible',
            ])
            ->add('geo', null, [
                'label' => 'item.geo',
            ])
            ->add('comment', null, [
                'label' => 'item.comment',
            ])
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
                    'label' => false,
                ]
            )
            ->add('barcode', null, [
                'label' => 'item.barcode',
            ]);

        // Allow for new options from the user.
        $builder->get('building')->resetViewTransformers();
        $builder->get('department')->resetViewTransformers();
        $builder->get('organization')->resetViewTransformers();
        $builder->get('type')->resetViewTransformers();
        $builder->get('address')->resetViewTransformers();
        $builder->get('location')->resetViewTransformers();
        $builder->get('room')->resetViewTransformers();
        $builder->get('city')->resetViewTransformers();
        $builder->get('status')->resetViewTransformers();
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Furniture::class,
            ]
        );
    }
}
