<?php

namespace App\Form;

use App\Entity\Artiste;
use App\Entity\Lieu;
use App\Entity\Oeuvre;
use App\Entity\Technique;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;

class OeuvreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', null, ['label' => 'Titre'])
            ->add('dateCreation', DateType::class, [
                'label' => 'Date de création (si connue)',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['rows' => 4],
            ])
            ->add('estDetruite', CheckboxType::class, [
                'label' => "L'œuvre a été détruite",
                'required' => false,
            ])
            //une œuvre se trouve dans UN SEUL lieu : liste déroulante
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'label' => 'Lieu',
                'placeholder' => '-- Choisir un lieu --',
            ])
            //0 ou plusieurs artistes : cases à cocher
            ->add('artistes', EntityType::class, [
                'class' => Artiste::class,
                'choice_label' => 'pseudo',
                'label' => 'Artiste(s) — ne rien cocher si anonyme',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
            //1 ou plusieurs techniques : cases à cocher + au moins une obligatoire
            ->add('techniques', EntityType::class, [
                'class' => Technique::class,
                'choice_label' => 'nom',
                'label' => 'Technique(s)',
                'multiple' => true,
                'expanded' => true,
                'constraints' => [
                    new Count(min: 1, minMessage: 'Choisissez au moins une technique.'),
                ],
            ])
            //l'œuvre recouvre-t-elle une autre œuvre ?
            ->add('oeuvreRecouverte', EntityType::class, [
                'class' => Oeuvre::class,
                'choice_label' => 'titre',
                'label' => 'Œuvre recouverte (facultatif)',
                'required' => false,
                'placeholder' => '-- Aucune --',
            ])
            ->add('enregistrer', SubmitType::class, ['attr' => ['class' => 'btn btn-primary']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Oeuvre::class,
        ]);
    }
}
