<?php

namespace App\Controller;

use App\Entity\Technique;
use App\Form\TechniqueSupprimerType;
use App\Form\TechniqueType;
use App\Repository\TechniqueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class TechniqueController extends AbstractController
{
    #[Route('/techniques', name: 'app_techniques')]
    public function index(TechniqueRepository $techniqueRepository): Response
    {
        //je vais chercher les techniques triées par nom
        $techniques = $techniqueRepository->findBy([], ['nom' => 'ASC']);

        return $this->render('technique/index.html.twig', [
            "techniques" => $techniques,
        ]);
    }

    #[Route('/ajouter-technique', name: 'app_ajouter_technique')]
    #[IsGranted('ROLE_USER')]
    public function ajouter(Request $request, EntityManagerInterface $em): Response
    {
        //1 je crée une technique vide
        $technique = new Technique();
        //2 je crée le formulaire
        $form = $this->createForm(TechniqueType::class, $technique);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($technique); //je veux faire un insert
            $em->flush(); //je déclenche l'insert

            return $this->redirectToRoute('app_techniques');
        }

        return $this->render('technique/ajouter.html.twig', [
            "formulaire" => $form->createView(),
        ]);
    }

    #[Route('/modifier-technique/{id}', name: 'app_modifier_technique')]
    #[IsGranted('ROLE_USER')]
    public function modifier(Request $request, TechniqueRepository $techniqueRepository,
                             EntityManagerInterface $em, $id): Response
    {
        //1 je vais chercher la technique
        $technique = $techniqueRepository->find($id);
        //2 je crée le formulaire
        $form = $this->createForm(TechniqueType::class, $technique);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($technique); //je veux faire un update
            $em->flush(); //je déclenche l'update

            return $this->redirectToRoute('app_techniques');
        }

        return $this->render('technique/modifier.html.twig', [
            "formulaire" => $form->createView(),
        ]);
    }

    #[Route('/supprimer-technique/{id}', name: 'app_supprimer_technique')]
    #[IsGranted('ROLE_USER')]
    public function supprimer(Request $request, TechniqueRepository $techniqueRepository,
                              EntityManagerInterface $em, $id): Response
    {
        //1 je vais chercher la technique
        $technique = $techniqueRepository->find($id);

        //une œuvre doit avoir au moins une technique : on ne supprime pas une technique utilisée
        if (count($technique->getOeuvres()) > 0) {
            $this->addFlash('erreur', 'Impossible de supprimer cette technique : elle est utilisée par des œuvres.');
            return $this->redirectToRoute('app_techniques');
        }

        //2 je crée le formulaire
        $form = $this->createForm(TechniqueSupprimerType::class, $technique);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->remove($technique); //je veux faire un delete
            $em->flush(); //je déclenche le delete

            return $this->redirectToRoute('app_techniques');
        }

        return $this->render('technique/supprimer.html.twig', [
            "technique" => $technique,
            "formulaire" => $form->createView(),
        ]);
    }
}
