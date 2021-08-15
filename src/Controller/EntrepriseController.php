<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Form\EntrepriseType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class EntrepriseController extends AbstractController
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/entreprise", name="entreprise")
     */
    public function index(Request $request): Response
    {
        $entreprise = new Entreprise();
        $addEntreprise = $this->createForm(EntrepriseType::class, $entreprise);
        $addEntreprise->handleRequest($request);

        if($addEntreprise->isSubmitted() && $addEntreprise->isValid()) {
            $entreprise = $addEntreprise->getData();
            $this->entityManager->persist($entreprise);
            $this->entityManager->flush();
            return $this->redirectToRoute('entreprise');
        }

        $entreprises = $this->entityManager->getRepository(Entreprise::class)->findAll();
        return $this->render('entreprise/index.html.twig', [
            'entreprises' => $entreprises,
            'add_entreprise' => $addEntreprise->createView()
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/entreprise/update_entreprise/{id}", name="update_admin_entreprise")
     */
    public function updateEntreprise(Entreprise $entreprise, Request $request): Response
    {
        $updateEntreprise = $this->createForm(EntrepriseType::class, $entreprise);
        $updateEntreprise->handleRequest($request);

        if($updateEntreprise->isSubmitted() && $updateEntreprise->isValid()) {
            $entreprise = $updateEntreprise->getData();
            $this->entityManager->persist($entreprise);
            $this->entityManager->flush();
            return $this->redirectToRoute('entreprise');
        }

        return $this->render('entreprise/update_entreprise.html.twig', [
            'id' => $entreprise,
            'update_entreprise' => $updateEntreprise->createView()
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/entreprise/delete_entreprise/{id}", name="delete_admin_entreprise")
     */
    public function deleteCategorie(Entreprise $entreprise): Response
    {
        $this->entityManager->remove($entreprise);
        $this->entityManager->flush();
        return $this->redirectToRoute('entreprise');
    }
}
