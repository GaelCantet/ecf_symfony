<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserCompetence;
use App\Form\UserCompetenceType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class UserCompetenceController extends AbstractController
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/user/competence/{id}", name="user_competence")
     */
    public function index(User $user): Response
    {
        $competences = $user->getUserCompetences();
        return $this->render('user_competence/index.html.twig', [
            'competences' => $competences,
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/user/competence/add_competence/{id}", name="add_user_competence")
     */
    public function addUserCompetence(User $user, Request $request): Response
    {
        $userCompetence = new UserCompetence();
        $addUserCompetence = $this->createForm(UserCompetenceType::class, $userCompetence);
        $addUserCompetence->handleRequest($request);

        if($addUserCompetence->isSubmitted() && $addUserCompetence->isValid()) {
            $userCompetence = $addUserCompetence->getData();
            $userCompetence->setUser($user);

            $this->entityManager->persist($userCompetence);
            $this->entityManager->flush();
            return $this->redirectToRoute('user_competence', [
                'id' => $user->getId()
            ]);
        }

        return $this->render('user_competence/add_competence.html.twig', [
            'add_competence' => $addUserCompetence->createView()
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/user/competence/update_competence/{id}", name="update_user_competence")
     */
    public function updateCompetence(UserCompetence $competence, Request $request): Response
    {
        $updateCompetence = $this->createForm(UserCompetenceType::class, $competence);
        $updateCompetence->handleRequest($request);

        if($updateCompetence->isSubmitted() && $updateCompetence->isValid()) {
            $competence = $updateCompetence->getData();
            $this->entityManager->persist($competence);
            $this->entityManager->flush();
            $user = $competence->getUser();
            return $this->redirectToRoute('user_competence', [
                'id' => $user->getId()
            ]);
        }

        return $this->render('user_competence/update_competence.html.twig', [
            'update_competence' => $updateCompetence->createView()
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/user/competence/delete_competence/{id}", name="delete_user_competence")
     */
    public function deleteCompetence(UserCompetence $competence, Request $request): Response
    {
        $user = $competence->getUser();
        $this->entityManager->remove($competence);
        $this->entityManager->flush();
        return $this->redirectToRoute('user_competence', [
            'id' => $user->getId()
        ]);
    }
}
