<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    #[Route('/hello/{name}')]
    public function hello($name): Response
    {
        return $this->render('default/name.html.twig', [
            'name' => $name,
        ]);
    }
}