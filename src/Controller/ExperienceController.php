<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Entity\Experience;
use App\Entity\User;
use App\Form\ExperienceType;
use App\Form\InnerExperienceType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class ExperienceController extends AbstractController
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/experience/{id}", name="experience")
     */
    public function index(User $user, Request $request): Response
    {
        $experiences = $user->getExperiences();

        return $this->render('experience/index.html.twig', [
            'experiences' => $experiences,
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/experience/add_experience/{id}", name="add_experience")
     */
    public function addExperience(User $user, Request $request): Response
    {
        $experience = new Experience();
        $addExperience = $this->createForm(ExperienceType::class, $experience);
        $addExperience->handleRequest($request);

        $innerExperience = $this->createForm(InnerExperienceType::class, $experience);
        $innerExperience->handleRequest($request);

        if($addExperience->isSubmitted() && $addExperience->isValid()) {
            $experience = $addExperience->getData();
            $experience->setUser($user);
            $entreprise = $this->entityManager->getRepository(Entreprise::class)->findBy(array('nom' => $experience->getEntreprise()->getNom()));
            if($entreprise) {
                $experience->setEntreprise($entreprise[0]);
            } else {
                $entreprise = $experience->getEntreprise();
                $this->entityManager->persist($entreprise);
            }
            $this->entityManager->persist($experience);
            $this->entityManager->flush();
            $experiences = $user->getExperiences();
            return $this->redirectToRoute('experience', [
                'id' => $user->getId(),
                'experiences' => $experiences
            ]);
        } elseif($innerExperience->isSubmitted() && $innerExperience->isValid()) {
            $experience = $innerExperience->getData();
            $experience->setUser($user);
            $entreprise = $innerExperience->get('entreprise')->getData();
            $this->entityManager->persist($experience);
            $this->entityManager->flush();
            $experiences = $user->getExperiences();
            return $this->redirectToRoute('experience', [
                'id' => $user->getId(),
                'experiences' => $experiences
            ]);
        }

        return $this->render('experience/add_experience.html.twig', [
            'add_experience' => $addExperience->createView(),
            'add_inner_experience' => $innerExperience->createView()
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/experience/update_experience/{id}", name="update_experience")
     */
    public function updateExperience(Experience $experience, Request $request): Response
    {
        $user = $experience->getUser();
        $updateExperience = $this->createForm(ExperienceType::class, $experience);
        $updateExperience->handleRequest($request);

        $updateInnerExperience = $this->createForm(InnerExperienceType::class, $experience);
        $updateInnerExperience->handleRequest($request);

        if($updateExperience->isSubmitted() && $updateExperience->isValid()) {
            $experience = $updateExperience->getData();
            $this->entityManager->persist($experience);
            $this->entityManager->flush();
            $experiences = $user->getExperiences();
            return $this->redirectToRoute('experience', [
                'id' => $user->getId(),
                'experiences' => $experiences
            ]);
        } elseif($updateInnerExperience->isSubmitted() && $updateInnerExperience->isValid()) {
            $experience = $updateInnerExperience->getData();
            $this->entityManager->persist($experience);
            $this->entityManager->flush();
            $experiences = $user->getExperiences();
            return $this->redirectToRoute('experience', [
                'id' => $user->getId(),
                'experiences' => $experiences
            ]);
        }

        return $this->render('experience/update_experience.html.twig', [
            'update_experience' => $updateExperience->createView(),
            'update_inner_experience' => $updateInnerExperience->createView()
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/experience/delete_experience/{id}", name="delete_experience")
     */
    public function deleteExperience(Experience $experience, Request $request): Response
    {
        $user = $experience->getUser();
        $this->entityManager->remove($experience);
        $this->entityManager->flush();
        return $this->redirectToRoute('experience', [
            'id' => $user->getId(),
            'experiences' => $user->getExperiences()
        ]);
    }
}
