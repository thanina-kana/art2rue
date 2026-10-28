<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class MenuController extends AbstractController
{
    public function _menu(): Response
    {
        return $this->render('menu/_menu.html.twig');
    }
}
