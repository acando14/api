<?php

namespace App\Controller;

use App\Repository\HospitalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1", name="hospital_")
 */
class HospitalApiController extends AbstractController
{
    /**
     * @Route("/hospitals", name="get_all", methods={"GET"})
     */
    public function index(HospitalRepository $hospitalRepository): Response
    {
        $hospitals = $hospitalRepository->findAll();
        return $this->json($hospitals);
    }
}
