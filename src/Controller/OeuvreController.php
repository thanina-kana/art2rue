<?php

namespace App\Controller;

use App\Entity\Oeuvre;
use App\Form\OeuvreSupprimerType;
use App\Form\OeuvreType;
use App\Repository\OeuvreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class OeuvreController extends AbstractController
{
    #[Route('/oeuvres', name: 'app_oeuvres')]
    public function index(OeuvreRepository $oeuvreRepository): Response
    {
        //je vais chercher les œuvres triées par titre
        $oeuvres = $oeuvreRepository->findBy([], ['titre' => 'ASC']);

        return $this->render('oeuvre/index.html.twig', [
            "oeuvres" => $oeuvres,
        ]);
    }

    #[Route('/ajouter-oeuvre', name: 'app_ajouter_oeuvre')]
    #[IsGranted('ROLE_USER')]
    public function ajouter(Request $request, EntityManagerInterface $em): Response
    {
        //1 je crée une œuvre vide
        $oeuvre = new Oeuvre();
        //2 je crée le formulaire
        $form = $this->createForm(OeuvreType::class, $oeuvre);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($oeuvre); //je veux faire un insert
            $em->flush(); //je déclenche l'insert

            return $this->redirectToRoute('app_oeuvres');
        }

        return $this->render('oeuvre/ajouter.html.twig', [
            "formulaire" => $form->createView(),
        ]);
    }

    #[Route('/modifier-oeuvre/{id}', name: 'app_modifier_oeuvre')]
    #[IsGranted('ROLE_USER')]
    public function modifier(Request $request, OeuvreRepository $oeuvreRepository,
                             EntityManagerInterface $em, $id): Response
    {
        //1 je vais chercher l'œuvre
        $oeuvre = $oeuvreRepository->find($id);
        //2 je crée le formulaire
        $form = $this->createForm(OeuvreType::class, $oeuvre);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //une œuvre ne peut pas se recouvrir elle-même
            if ($oeuvre->getOeuvreRecouverte() === $oeuvre) {
                $oeuvre->setOeuvreRecouverte(null);
            }

            $em->persist($oeuvre); //je veux faire un update
            $em->flush(); //je déclenche l'update

            return $this->redirectToRoute('app_oeuvres');
        }

        return $this->render('oeuvre/modifier.html.twig', [
            "formulaire" => $form->createView(),
        ]);
    }

    #[Route('/supprimer-oeuvre/{id}', name: 'app_supprimer_oeuvre')]
    #[IsGranted('ROLE_USER')]
    public function supprimer(Request $request, OeuvreRepository $oeuvreRepository,
                              EntityManagerInterface $em, $id): Response
    {
        //1 je vais chercher l'œuvre
        $oeuvre = $oeuvreRepository->find($id);

        //on ne supprime pas une œuvre qui a des photos (ce sont des archives)
        if (count($oeuvre->getPhotos()) > 0) {
            $this->addFlash('erreur', 'Impossible de supprimer cette œuvre : elle a des photos. Cochez plutôt "détruite".');
            return $this->redirectToRoute('app_oeuvres');
        }

        //2 je crée le formulaire
        $form = $this->createForm(OeuvreSupprimerType::class, $oeuvre);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //si une autre œuvre recouvre celle-ci, on enlève le lien avant de supprimer
            $oeuvreQuiRecouvre = $oeuvreRepository->findOneBy(['oeuvreRecouverte' => $oeuvre]);
            if ($oeuvreQuiRecouvre) {
                $oeuvreQuiRecouvre->setOeuvreRecouverte(null);
            }

            $em->remove($oeuvre); //je veux faire un delete
            $em->flush(); //je déclenche le delete

            return $this->redirectToRoute('app_oeuvres');
        }

        return $this->render('oeuvre/supprimer.html.twig', [
            "oeuvre" => $oeuvre,
            "formulaire" => $form->createView(),
        ]);
    }
}
