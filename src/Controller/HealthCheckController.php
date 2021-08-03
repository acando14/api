<?php

namespace App\Controller;

use App\Exceptions\EntityNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HealthCheckController extends AbstractController
{
    /**
     * @Route("/health_check", name="health_check")
     */
    public function index(EntityManagerInterface $entityManager): Response
    {
        throw new EntityNotFoundException();
        return $this->json([
            'database' => $entityManager->getConnection()->connect()
        ]);
    }
}
