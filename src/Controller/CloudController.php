<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Routing\Annotation\Route;

class CloudController extends AbstractController
{
    /**
     * @Route("/", name="cloud_index_home", methods={"GET"})
     * @Route("/{path}", name="cloud_index", methods={"GET"}, requirements={"path":".+","path":"^(?!static).+","path":"^(?!api).+"})
     */
    public function index()
    {
        return $this->render(
            'index.html.twig'
        );
    }
}