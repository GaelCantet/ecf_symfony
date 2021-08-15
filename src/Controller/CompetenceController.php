<?php

namespace App\Controller;

use App\Entity\Competence;
use App\Form\CompetenceType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class CompetenceController extends AbstractController
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/competence", name="competence")
     */
    public function index(Request $request): Response
    {
        $competence = new Competence();
        $addCompetence = $this->createForm(CompetenceType::class, $competence);
        $addCompetence->handleRequest($request);

        if($addCompetence->isSubmitted() && $addCompetence->isValid()) {
            $competence = $addCompetence->getData();
            $this->entityManager->persist($competence);
            $this->entityManager->flush();
            return $this->redirectToRoute('competence');
        }

        $competences = $this->entityManager->getRepository(Competence::class)->findAll();
        return $this->render('competence/index.html.twig', [
            'competences' => $competences,
            'add_competence' => $addCompetence->createView()
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/competence/update_competence/{id}", name="update_admin_competence")
     */
    public function updateCompetence(Competence $competence, Request $request): Response
    {
        $updateCompetence = $this->createForm(CompetenceType::class, $competence);
        $updateCompetence->handleRequest($request);

        if($updateCompetence->isSubmitted() && $updateCompetence->isValid()) {
            $competence = $updateCompetence->getData();
            $this->entityManager->persist($competence);
            $this->entityManager->flush();
            return $this->redirectToRoute('competence');
        }

        return $this->render('competence/update_competence.html.twig', [
            'id' => $competence,
            'update_competence' => $updateCompetence->createView()
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/competence/delete_competence/{id}", name="delete_admin_competence")
     */
    public function deleteCompetence(Competence $competence): Response
    {
        $this->entityManager->remove($competence);
        $this->entityManager->flush();
        return $this->redirectToRoute('competence');
    }
}
