<?php

namespace App\Controller;

use App\Exceptions\EntityNotFoundException;
use App\Repository\HospitalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api/v1", name="hospital_")
 */
class HospitalApiController extends AbstractController
{
    /**
     * @Route("/hospitals", name="get_all", methods={"GET"})
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function index(HospitalRepository $hospitalRepository, SerializerInterface $serializer): Response
    {
        $hospitals = $hospitalRepository->findAll();
        $hospitalsJson = $serializer->normalize(
            $hospitals,
            'json',
            ['groups' => 'list']
        );
        return $this->json($hospitalsJson);
    }
}
