<?php

namespace App\Controller\Cloud;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Routing\Annotation\Route;

class CloudController extends AbstractController
{
    /**
     * @Route("/cloud", name="cloud_index_home", methods={"GET"})
     * @Route("/cloud/{path}", name="cloud_index", methods={"GET"}, requirements={"path"=".+"})
     */
    public function index()
    {
        return $this->render(
            'Cloud/index.html.twig'
        );
    }
}