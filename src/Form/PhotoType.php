<?php

namespace App\Form;

use App\Entity\Oeuvre;
use App\Entity\Photo;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class PhotoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('oeuvre', EntityType::class, [
                'class' => Oeuvre::class,
                'choice_label' => 'titre',
                'label' => 'Œuvre photographiée',
                'placeholder' => '-- Choisir une œuvre --',
            ])
            ->add('datePrise', DateTimeType::class, [
                'label' => 'Date de prise de vue',
                'widget' => 'single_text',
            ])
            //ici je force le type de champ : c'est un fichier
            ->add('nomFichier', FileType::class, [
                'label' => 'Image (jpg, png, webp - 2 Mo max)',
                'mapped' => false, //je vais le gérer à la main dans le controller
                'required' => false, //pas obligatoire quand on modifie une photo
                'constraints' => [
                    new Image(
                        maxSize: '2M',
                        maxSizeMessage: "L'image est trop lourde (2 Mo maximum).",
                        mimeTypesMessage: 'Le fichier doit être une image.',
                    ),
                ],
            ])
            ->add('etatOeuvre', null, [
                'label' => "État de l'œuvre (facultatif)",
                'attr' => ['placeholder' => 'ex : en bon état, tags par-dessus, partiellement effacée...'],
            ])
            ->add('enregistrer', SubmitType::class, ['attr' => ['class' => 'btn btn-primary']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Photo::class,
        ]);
    }
}
