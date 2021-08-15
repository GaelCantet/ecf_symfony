<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class CategorieController extends AbstractController
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/categorie", name="categorie")
     */
    public function index(Request $request): Response
    {
        $categorie = new Categorie();
        $addCategorie = $this->createForm(CategorieType::class, $categorie);
        $addCategorie->handleRequest($request);

        if($addCategorie->isSubmitted() && $addCategorie->isValid()) {
            $categorie = $addCategorie->getData();
            $this->entityManager->persist($categorie);
            $this->entityManager->flush();
            return $this->redirectToRoute('categorie');
        }

        $categories = $this->entityManager->getRepository(Categorie::class)->findAll();
        return $this->render('categorie/index.html.twig', [
            'categories' => $categories,
            'add_categorie' => $addCategorie->createView()
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/categorie/update_categorie/{id}", name="update_admin_categorie")
     */
    public function updateCategorie(Categorie $categorie, Request $request): Response
    {
        $updateCategorie = $this->createForm(CategorieType::class, $categorie);
        $updateCategorie->handleRequest($request);

        if($updateCategorie->isSubmitted() && $updateCategorie->isValid()) {
            $categorie = $updateCategorie->getData();
            $this->entityManager->persist($categorie);
            $this->entityManager->flush();
            return $this->redirectToRoute('categorie');
        }

        return $this->render('categorie/update_categorie.html.twig', [
            'id' => $categorie,
            'update_categorie' => $updateCategorie->createView()
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/categorie/delete_categorie/{id}", name="delete_admin_categorie")
     */
    public function deleteCategorie(Categorie $categorie): Response
    {
        $this->entityManager->remove($categorie);
        $this->entityManager->flush();
        return $this->redirectToRoute('categorie');
    }
}
