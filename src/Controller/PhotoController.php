<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Form\PhotoSupprimerType;
use App\Form\PhotoType;
use App\Repository\PhotoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class PhotoController extends AbstractController
{
    #[Route('/photos', name: 'app_photos')]
    public function index(PhotoRepository $photoRepository): Response
    {
        //je vais chercher les photos, les plus récentes d'abord
        $photos = $photoRepository->findBy([], ['datePrise' => 'DESC']);

        return $this->render('photo/index.html.twig', [
            "photos" => $photos,
        ]);
    }

    #[Route('/ajouter-photo', name: 'app_ajouter_photo')]
    #[IsGranted('ROLE_USER')]
    public function ajouter(Request $request, EntityManagerInterface $em): Response
    {
        //1 je crée une photo vide, datée de maintenant par défaut
        $photo = new Photo();
        $photo->setDatePrise(new \DateTime());
        //2 je crée le formulaire
        $form = $this->createForm(PhotoType::class, $photo);

        $form->handleRequest($request);

        //à l'ajout, l'image est obligatoire
        if ($form->isSubmitted() && !$form->get('nomFichier')->getData()) {
            $form->get('nomFichier')->addError(new FormError('Choisissez une image.'));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            //le photographe, c'est le bénévole connecté
            $photo->setBenevole($this->getUser());
            //il faut gérer le fichier image
            $this->gererLaPhoto($form, $photo);

            $em->persist($photo); //je veux faire un insert
            $em->flush(); //je déclenche l'insert

            return $this->redirectToRoute('app_photos');
        }

        return $this->render('photo/ajouter.html.twig', [
            "formulaire" => $form->createView(),
        ]);
    }

    #[Route('/modifier-photo/{id}', name: 'app_modifier_photo')]
    #[IsGranted('ROLE_USER')]
    public function modifier(Request $request, PhotoRepository $photoRepository,
                             EntityManagerInterface $em, $id): Response
    {
        //1 je vais chercher la photo
        $photo = $photoRepository->find($id);

        //seul le bénévole qui a pris la photo peut la modifier
        if ($photo->getBenevole() !== $this->getUser()) {
            $this->addFlash('erreur', 'Vous ne pouvez modifier que vos propres photos.');
            return $this->redirectToRoute('app_photos');
        }

        //2 je crée le formulaire
        $form = $this->createForm(PhotoType::class, $photo);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //si une nouvelle image a été choisie, elle remplace l'ancienne
            $this->gererLaPhoto($form, $photo);

            $em->persist($photo); //je veux faire un update
            $em->flush(); //je déclenche l'update

            return $this->redirectToRoute('app_photos');
        }

        return $this->render('photo/modifier.html.twig', [
            "photo" => $photo,
            "formulaire" => $form->createView(),
        ]);
    }

    #[Route('/supprimer-photo/{id}', name: 'app_supprimer_photo')]
    #[IsGranted('ROLE_USER')]
    public function supprimer(Request $request, PhotoRepository $photoRepository,
                              EntityManagerInterface $em, $id): Response
    {
        //1 je vais chercher la photo
        $photo = $photoRepository->find($id);

        //seul le bénévole qui a pris la photo peut la supprimer
        if ($photo->getBenevole() !== $this->getUser()) {
            $this->addFlash('erreur', 'Vous ne pouvez supprimer que vos propres photos.');
            return $this->redirectToRoute('app_photos');
        }

        //2 je crée le formulaire
        $form = $this->createForm(PhotoSupprimerType::class, $photo);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //je supprime le fichier image du dossier Photos
            $this->supprimerLaPhoto($photo);

            $em->remove($photo); //je veux faire un delete
            $em->flush(); //je déclenche le delete

            return $this->redirectToRoute('app_photos');
        }

        return $this->render('photo/supprimer.html.twig', [
            "photo" => $photo,
            "formulaire" => $form->createView(),
        ]);
    }

    public function gererLaPhoto(FormInterface $form, Photo $photo)
    {
        $fichier = $form->get('nomFichier')->getData();
        if ($fichier) {
            //si la photo avait déjà une image, je la supprime
            $this->supprimerLaPhoto($photo);

            //je récupère le nom original du fichier
            $nomOriginal = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $fichier->guessExtension() ?? $fichier->getClientOriginalExtension();

            //je crée un nom unique pour ne pas écraser une autre image
            $nomUnique = $nomOriginal . '_' . uniqid() . '.' . $extension;

            //je range l'image dans public/Photos
            $fichier->move('Photos', $nomUnique);
            //je remplis ce champ à la main puisque j'ai mis mapped=false dans PhotoType
            //(on stocke juste le nom du fichier, comme dans l'énoncé : "mur_01.jpg")
            $photo->setNomFichier($nomUnique);
        }
    }

    public function supprimerLaPhoto(Photo $photo)
    {
        $nomFichier = $photo->getNomFichier();
        if ($nomFichier) {
            $fs = new Filesystem();
            if ($fs->exists('Photos/' . $nomFichier)) {
                $fs->remove('Photos/' . $nomFichier);
            }
        }
    }
}
