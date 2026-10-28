<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuSupprimerType;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class LieuController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    #[Route('/lieux', name: 'app_lieux')]
    public function index(LieuRepository $lieuRepository): Response
    {
        //je vais chercher les lieux triés par quartier (select * order by quartier)
        $lieux = $lieuRepository->findBy([], ['quartier' => 'ASC']);

        return $this->render('lieu/index.html.twig', [
            "lieux" => $lieux,
        ]);
    }

    #[Route('/ajouter-lieu', name: 'app_ajouter_lieu')]
    #[IsGranted('ROLE_USER')]
    public function ajouter(Request $request, EntityManagerInterface $em): Response
    {
        //1 je crée un lieu vide
        $lieu = new Lieu();
        //2 je crée le formulaire
        $form = $this->createForm(LieuType::class, $lieu);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($lieu); //je veux faire un insert
            $em->flush(); //je déclenche l'insert

            return $this->redirectToRoute('app_lieux');
        }

        return $this->render('lieu/ajouter.html.twig', [
            "formulaire" => $form->createView(),
        ]);
    }

    #[Route('/modifier-lieu/{id}', name: 'app_modifier_lieu')]
    #[IsGranted('ROLE_USER')]
    public function modifier(Request $request, LieuRepository $lieuRepository,
                             EntityManagerInterface $em, $id): Response
    {
        //1 je vais chercher le lieu
        $lieu = $lieuRepository->find($id);
        //2 je crée le formulaire
        $form = $this->createForm(LieuType::class, $lieu);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($lieu); //je veux faire un update
            $em->flush(); //je déclenche l'update

            return $this->redirectToRoute('app_lieux');
        }

        return $this->render('lieu/modifier.html.twig', [
            "formulaire" => $form->createView(),
        ]);
    }

    #[Route('/supprimer-lieu/{id}', name: 'app_supprimer_lieu')]
    #[IsGranted('ROLE_USER')]
    public function supprimer(Request $request, LieuRepository $lieuRepository,
                              EntityManagerInterface $em, $id): Response
    {
        //1 je vais chercher le lieu
        $lieu = $lieuRepository->find($id);

        //on ne supprime pas un lieu qui contient encore des œuvres
        if (count($lieu->getOeuvres()) > 0) {
            $this->addFlash('erreur', 'Impossible de supprimer ce lieu : il contient encore des œuvres.');
            return $this->redirectToRoute('app_lieux');
        }

        //2 je crée le formulaire
        $form = $this->createForm(LieuSupprimerType::class, $lieu);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->remove($lieu); //je veux faire un delete
            $em->flush(); //je déclenche le delete

            return $this->redirectToRoute('app_lieux');
        }

        return $this->render('lieu/supprimer.html.twig', [
            "lieu" => $lieu,
            "formulaire" => $form->createView(),
        ]);
    }
}
