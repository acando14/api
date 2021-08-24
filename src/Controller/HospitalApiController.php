<?php

namespace App\Controller;

use App\Entity\Hospital;
use App\Form\HospitalType;
use App\Repository\HospitalRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1", name="hospital_")
 */
class HospitalApiController extends AbstractFOSRestController
{
    /**
     * @Route("/hospitals", name="get_all", methods={"GET"})
     */
    public function index(HospitalRepository $hospitalRepository): Response
    {
        $hospitals = $hospitalRepository->findAll();
        $view = $this->view($hospitals, 200);

        return $this->handleView($view);
    }

    /**
     * @Route("/hospitals", name="create", methods={"POST"})
     */
    public function post(Request $request): Response
    {
        $hospital = new Hospital();
        $hospitalForm = $this->createForm(HospitalType::class, $hospital);
        $view = View::create();
        $data = json_decode($request->getContent(), true);
        $hospitalForm->submit($data);
        if ($hospitalForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($hospital);
            $em->flush();
            $view->setData($hospitalForm->getData());
        } else {
            $view->setData($hospitalForm);
        }
        return $this->handleView($view);
    }
}
