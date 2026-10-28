<?php

namespace App\Form;

use App\Entity\Lieu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, ['label' => 'Nom'])
            ->add('adresse', null, ['label' => 'Adresse (facultative)'])
            ->add('quartier', null, ['label' => 'Quartier'])
            ->add('latitude', null, ['label' => 'Latitude'])
            ->add('longitude', null, ['label' => 'Longitude'])
            ->add('enregistrer', SubmitType::class, ['attr' => ['class' => 'btn btn-primary']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
