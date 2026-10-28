<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        //déjà connecté(e) ? on va directement sur la liste des lieux
        if ($this->getUser()) {
            return $this->redirectToRoute('app_lieux');
        }

        //l'erreur de connexion s'il y en a une (mauvais mot de passe...)
        $error = $authenticationUtils->getLastAuthenticationError();

        //le dernier email tapé, pour ne pas avoir à le retaper
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        //cette méthode reste vide : Symfony gère la déconnexion tout seul
        throw new \LogicException('Cette méthode est interceptée par la clé logout du firewall.');
    }
}
