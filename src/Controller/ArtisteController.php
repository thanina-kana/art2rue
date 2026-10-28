<?php

namespace App\Controller;

use App\Entity\Artiste;
use App\Form\ArtisteSupprimerType;
use App\Form\ArtisteType;
use App\Repository\ArtisteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ArtisteController extends AbstractController
{
    #[Route('/artistes', name: 'app_artistes')]
    public function index(ArtisteRepository $artisteRepository): Response
    {
        //je vais chercher les artistes triés par pseudo
        $artistes = $artisteRepository->findBy([], ['pseudo' => 'ASC']);

        return $this->render('artiste/index.html.twig', [
            "artistes" => $artistes,
        ]);
    }

    #[Route('/ajouter-artiste', name: 'app_ajouter_artiste')]
    #[IsGranted('ROLE_USER')]
    public function ajouter(Request $request, EntityManagerInterface $em): Response
    {
        //1 je crée un artiste vide
        $artiste = new Artiste();
        //2 je crée le formulaire
        $form = $this->createForm(ArtisteType::class, $artiste);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($artiste); //je veux faire un insert
            $em->flush(); //je déclenche l'insert

            return $this->redirectToRoute('app_artistes');
        }

        return $this->render('artiste/ajouter.html.twig', [
            "formulaire" => $form->createView(),
        ]);
    }

    #[Route('/modifier-artiste/{id}', name: 'app_modifier_artiste')]
    #[IsGranted('ROLE_USER')]
    public function modifier(Request $request, ArtisteRepository $artisteRepository,
                             EntityManagerInterface $em, $id): Response
    {
        //1 je vais chercher l'artiste
        $artiste = $artisteRepository->find($id);
        //2 je crée le formulaire
        $form = $this->createForm(ArtisteType::class, $artiste);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($artiste); //je veux faire un update
            $em->flush(); //je déclenche l'update

            return $this->redirectToRoute('app_artistes');
        }

        return $this->render('artiste/modifier.html.twig', [
            "formulaire" => $form->createView(),
        ]);
    }

    #[Route('/supprimer-artiste/{id}', name: 'app_supprimer_artiste')]
    #[IsGranted('ROLE_USER')]
    public function supprimer(Request $request, ArtisteRepository $artisteRepository,
                              EntityManagerInterface $em, $id): Response
    {
        //1 je vais chercher l'artiste
        $artiste = $artisteRepository->find($id);
        //2 je crée le formulaire
        $form = $this->createForm(ArtisteSupprimerType::class, $artiste);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //on détache l'artiste de ses œuvres avant de le supprimer :
            //les œuvres restent dans l'archive (elles deviennent anonymes)
            foreach ($artiste->getOeuvres() as $oeuvre) {
                $oeuvre->removeArtiste($artiste);
            }

            $em->remove($artiste); //je veux faire un delete
            $em->flush(); //je déclenche le delete

            return $this->redirectToRoute('app_artistes');
        }

        return $this->render('artiste/supprimer.html.twig', [
            "artiste" => $artiste,
            "formulaire" => $form->createView(),
        ]);
    }
}
