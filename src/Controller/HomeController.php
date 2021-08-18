<?php

namespace App\Controller;

use App\Entity\Competence;
use App\Entity\Experience;
use App\Entity\User;
use App\Entity\UserCompetence;
use App\Form\InnerExperienceType;
use App\Form\ProfilType;
use App\Form\RegistrationType;
use App\Form\UserCompetenceType;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class HomeController extends AbstractController
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="home")
     */
    public function index(HttpFoundationRequest $request, UserPasswordHasherInterface $passwordEncoder): Response
    {
        //Formulaire de recherche par profil
        $searchByNom = $this->createFormBuilder()
            ->add('nom', TextType::class, [
                'required' => false
            ])
            ->add('disponibilite', ChoiceType::class, [
                'label' => 'Disponibilité',
                'expanded' => true,
                'multiple' => true,
                'choices' => ['disponible' => true]
            ])
            ->add('qualite', ChoiceType::class, [
                'label' => 'Qualité',
                'expanded' => true,
                'multiple' => true,
                'choices' => [
                    'candidat' => 0,
                    'collaborateur' => 1
                ]
            ])
            ->add('competences', EntityType::class, [
                'class' => Competence::class,
                'expanded' => true,
                'multiple' => true
            ])
            ->add('rechercher' , SubmitType::class)
            ->getForm();
        $searchByNom->handleRequest($request);

        //Formulaire de recherche par compétence
        $searchByCompetence = $this->createFormBuilder()
            ->add('competence', EntityType::class, [
                'class' => Competence::class
            ])
            ->add('niveau', RangeType::class, [
                'attr' => [
                    'min' => 1,
                    'max' => 5
                ]
            ])
            ->add('favori', ChoiceType::class, [
                'choices' => [
                    'Indifférent' => null,
                    'Oui' => 1,
                    'Non' => 0
                ]
            ])
            ->add('rechercher' , SubmitType::class)
            ->getForm();
        $searchByCompetence->handleRequest($request);
        
        //Si le formulaire de recherche par profil est soumis
        if($searchByNom->isSubmitted() && $searchByNom->isValid()) {
            $users = [];
            if($searchByNom->get('competences')->getData()) {
                $competences = $searchByNom->get('competences')->getData();
                foreach($competences as $key => $competence) {
                    foreach($competence->getUserCompetences() as $key => $userCompetence) {
                        $users[] = $userCompetence->getUser();
                    }
                }
                $users = array_unique($users);
            }
            if(count($users) === 0) {
                $users = $this->entityManager->getRepository(User::class)->findAll();
                
            }
            if(!empty($searchByNom->get('nom')->getData())) {
                $nom = $searchByNom->get('nom')->getData();
                foreach($users as $key => $user) {
                    if(!str_contains($user->getNom(), $nom)) {
                        unset($users[$key]);
                    }
                }
            }
            if($searchByNom->get('disponibilite')->getData() && $searchByNom->get('disponibilite')->getData()[0]) {
                foreach($users as $key => $user) {
                    if(!$user->getDisponibilite()) {
                        unset($users[$key]);
                    }
                }
            }
            if(count($searchByNom->get('qualite')->getData()) === 1) {
                if($searchByNom->get('qualite')->getData()[0]) {
                    foreach($users as $key => $user) {
                        if (!in_array("ROLE_COLLABORATEUR", $user->getRoles())) {
                            unset($users[$key]);
                        }
                    }
                } else {
                    foreach($users as $key => $user) {
                        if (in_array("ROLE_COLLABORATEUR", $user->getRoles())) {
                            unset($users[$key]);
                        }
                    }
                }
            }
        //Sinon, si le formulaire de recherche par compétence est soumis
        } elseif($searchByCompetence->isSubmitted() && $searchByCompetence->isValid()) {
            $favori = $searchByCompetence->get('favori')->getData();
            $niveau = $searchByCompetence->get('niveau')->getData();
            $competenceId = $searchByCompetence->get('competence')->getData()->getId();
            if($favori === 'null') {
                $query = $this->entityManager->createQuery(
                    'SELECT userCompetence
                    FROM App\Entity\UserCompetence userCompetence
                    WHERE userCompetence.competence = :competence_id
                        AND userCompetence.niveau >= :niveau'
                )->setParameters([
                    'competence_id' => $competenceId,
                    'niveau' => $niveau
                ]);
                $userCompetence = $query->getResult();
                $users = [];
                foreach($userCompetence as $user) {
                    $users[] = $user->getUser();
                }
            } else {
                $query = $this->entityManager->createQuery(
                    'SELECT userCompetence
                    FROM App\Entity\UserCompetence userCompetence
                    WHERE userCompetence.competence = :competence_id
                        AND userCompetence.fav = :favori
                        AND userCompetence.niveau >= :niveau'
                )->setParameters([
                    'competence_id' => $competenceId,
                    'favori' => $favori,
                    'niveau' => $niveau
                ]);
                $userCompetence = $query->getResult();
                $users = [];
                foreach($userCompetence as $user) {
                    $users[] = $user->getUser();
                }
            }
        //Sinon on affiche l'intégratlité des profils
        } else {
            $users = $this->entityManager->getRepository(User::class)->findAll();
        }

        //Gestion de l'inscription à la plateforme
        $user = new User();
        $addUser = $this->createForm(RegistrationType::class, $user);
        $addUser->handleRequest($request);

        if($addUser->isSubmitted() && $addUser->isValid()) {
            $user = $addUser->getData();
            $user->setRoles(["ROLE_USER"]);
            $user->setPassword($passwordEncoder->hashPassword($user, $user->getPassword()));

            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return $this->redirectToRoute('home', [
                'users' => $users,
                'search_nom' => $searchByNom->createView(),
                'search_competence' => $searchByCompetence->createView()
            ]);
        }

        return $this->render('home/index.html.twig', [
            'add_user' => $addUser->createView(),
            'users' => $users,
            'search_nom' => $searchByNom->createView(),
            'search_competence' => $searchByCompetence->createView()
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/update_profil/{id}", name="update_profil")
     */
    public function updateProfil(User $user, HttpFoundationRequest $request): Response
    {
        $updateProfil = $this->createForm(UserType::class, $user);
        $updateProfil->handleRequest($request);

        if($updateProfil->isSubmitted() && $updateProfil->isValid()) {
            $user = $updateProfil->getData();
            $roles = $user->getRoles();
            if(count($roles) == 1 && in_array("ROLE_USER", $roles)) {
                $user->setVisibilite(1);
            }
            $user->setPassword($user->getPassword());
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return $this->redirectToRoute('home');
        }

        return $this->render('home/update_profil.html.twig', [
            'update_profil' => $updateProfil->createView()
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("home/delete_profil/{id}", name="delete_profil")
     */
    public function deleteProfil(User $user): Response
    {
        $this->container->get('security.token_storage')->setToken(null);
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_logout');
    }

    /**
     * @IsGranted("ROLE_COMMERCIAL")
     * @Route("/control_profil/{id}", name="control_profil")
     */
    public function controlProfil(User $user, HttpFoundationRequest $request): Response
    {
        $experience = new Experience();
        $addExperience = $this->createForm(InnerExperienceType::class, $experience);
        $addExperience->handleRequest($request);

        $competence = new UserCompetence();
        $addCompetence = $this->createForm(UserCompetenceType::class, $competence);
        $addCompetence->handleRequest($request);

        $controlProfil = $this->createForm(ProfilType::class, $user);
        $controlProfil->handleRequest($request);

        if($addExperience->isSubmitted() && $addExperience->isValid()) {
            $experience = $addExperience->getData();
            $experience->setUser($user);
            $this->entityManager->persist($experience);
            $this->entityManager->flush();

            return $this->redirectToRoute('control_profil', [
                'id' => $user->getId(),
                'user' => $user,
                'add_competence' => $addCompetence->createView(),
                'add_experience' => $addExperience->createView(),
                'control_profil' => $controlProfil->createView()
            ]);
        }

        if($addCompetence->isSubmitted() && $addCompetence->isValid()) {
            $competence = $addCompetence->getData();
            $competence->setUser($user);
            $this->entityManager->persist($competence);
            $this->entityManager->flush();

            return $this->redirectToRoute('control_profil', [
                'id' => $user->getId(),
                'user' => $user,
                'add_competence' => $addCompetence->createView(),
                'add_experience' => $addExperience->createView(),
                'control_profil' => $controlProfil->createView()
            ]);
        }

        if($controlProfil->isSubmitted() && $controlProfil->isValid()) {
            $user->setDisponibilite($controlProfil->get('disponibilite')->getData());
            $user->setVisibilite($controlProfil->get('visibilite')->getData());

            if($controlProfil->get('poste')->getData()) {
                $user->setRoles(["ROLE_COLLABORATEUR"]);
            } else {
                $user->setRoles(["ROLE_USER"]);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return $this->redirectToRoute('control_profil', [
                'id' => $user->getId(),
                'user' => $user,
                'add_competence' => $addCompetence->createView(),
                'add_experience' => $addExperience->createView(),
                'control_profil' => $controlProfil->createView()
            ]);
        }


        return $this->render('home/control_profil.html.twig', [
            'user' => $user,
            'add_competence' => $addCompetence->createView(),
            'add_experience' => $addExperience->createView(),
            'control_profil' => $controlProfil->createView()
        ]);
    }

    /**
     * @IsGranted("ROLE_COMMERCIAL")
     * @Route("/home/competence/update_competence/{id}", name="update_profil_competence")
     */
    public function updateCompetence(UserCompetence $competence, HttpFoundationRequest $request): Response
    {
        $updateCompetence = $this->createForm(UserCompetenceType::class, $competence);
        $updateCompetence->handleRequest($request);

        if($updateCompetence->isSubmitted() && $updateCompetence->isValid()) {
            $competence = $updateCompetence->getData();
            $this->entityManager->persist($competence);
            $this->entityManager->flush();
            $user = $competence->getUser();
            return $this->redirectToRoute('control_profil', [
                'id' => $user->getId()
            ]);
        }

        return $this->render('home/update_competence.html.twig', [
            'update_competence' => $updateCompetence->createView()
        ]);
    }

    /**
     * @IsGranted("ROLE_COMMERCIAL")
     * @Route("/home/competence/delete_competence/{id}", name="delete_profil_competence")
     */
    public function deleteCompetence(UserCompetence $competence): Response
    {
        $user = $competence->getUser();
        $this->entityManager->remove($competence);
        $this->entityManager->flush();
        return $this->redirectToRoute('control_profil', [
            'id' => $user->getId()
        ]);
    }

    /**
     * @IsGranted("ROLE_COMMERCIAL")
     * @Route("/home/update_experience/{id}", name="update_profil_experience")
     */
    public function updateExperience(Experience $experience, HttpFoundationRequest $request): Response
    {
        $user = $experience->getUser();
        $updateExperience = $this->createForm(InnerExperienceType::class, $experience);
        $updateExperience->handleRequest($request);

        if($updateExperience->isSubmitted() && $updateExperience->isValid()) {
            $experience = $updateExperience->getData();
            $this->entityManager->persist($experience);
            $this->entityManager->flush();
            return $this->redirectToRoute('control_profil', [
                'id' => $user->getId()
            ]);
        }

        return $this->render('home/update_experience.html.twig', [
            'update_experience' => $updateExperience->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_COMMERCIAL")
     * @Route("/home/delete_experience/{id}", name="delete_profil_experience")
     */
    public function deleteExperience(Experience $experience): Response
    {
        $user = $experience->getUser();
        $this->entityManager->remove($experience);
        $this->entityManager->flush();
        return $this->redirectToRoute('control_profil', [
            'id' => $user->getId()
        ]);
    }

    /**
     * @IsGranted("ROLE_COMMERCIAL")
     * @Route("home/profil_resume/{id}", name="resume")
     */
    public function resume(User $user): Response
    {
        return $this->render('home/resume.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("home/add_profil", name="add_profil_admin")
     */
    public function addProfilAdmin(HttpFoundationRequest $request, UserPasswordHasherInterface $passwordEncoder): Response
    {
        $user = new User();
        $addUser = $this->createForm(RegistrationType::class, $user);
        $addUser->handleRequest($request);

        if($addUser->isSubmitted() && $addUser->isValid()) {
            $user = $addUser->getData();
            $role = $_POST['qualite'];
            $user->setRoles([$role]);
            $user->setPassword($passwordEncoder->hashPassword($user, $user->getPassword()));

            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return $this->redirectToRoute('home');
        }

        return $this->render('home/add_profil.html.twig', [
            'add_profil' => $addUser->createView()
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("home/delete_profil/{id}", name="delete_profil_admin")
     */
    public function deleteProfilAdmin(User $user): Response
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('home');
    }
}
