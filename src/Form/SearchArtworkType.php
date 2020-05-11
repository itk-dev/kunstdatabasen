<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Form;

use App\Entity\Artwork;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SearchArtworkType.
 */
class SearchArtworkType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('search', SearchType::class, [
            'required' => false,
        ])
            ->add('type', ChoiceType::class, [
            'required' => false,
            'choices' => [
                '1' => '1',
                '2' => '2'
            ]
        ])
            ->add('category', ChoiceType::class, [
            'required' => false,
            'choices' => [
                '1' => '1',
                '2' => '2'
            ]
        ])
            ->add('building', ChoiceType::class, [
            'required' => false,
            'choices' => [
                'building 1' => 1,
                'building 2' => 2,
            ]
        ])
            ->add('width', ChoiceType::class, [
            'required' => false,
            'choices' => [
                "0 - 50" => "0 - 50",
                "50 - 100" => "50 - 100",
                "100 <" => "100 <"
            ]
        ])
            ->add('height', ChoiceType::class, [
            'required' => false,
            'choices' => [
                "0 - 50" => "0 - 50",
                "50 - 100" => "50 - 100",
                "100 <" => "100 <"
            ]
        ])->add('yearFrom', NumberType::class, [
            'required' => false,
        ]);
        $builder->add('yearTo', NumberType::class, [
            'required' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Artwork::class,
            ]
        );
    }
}
