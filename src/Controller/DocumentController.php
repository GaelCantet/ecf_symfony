<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\User;
use App\Form\DocumentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;

class DocumentController extends AbstractController
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/document/{id}", name="document")
     */
    public function index(User $user): Response
    {
        return $this->render('document/index.html.twig', [
            'documents' => $user->getDocuments(),
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/document/add_document/{id}", name="add_document")
     */
    public function addDocument(User $user, Request $request, SluggerInterface $slugger): Response
    {
        $document = new Document();
        $addDocument = $this->createForm(DocumentType::class, $document);
        $addDocument->handleRequest($request);

        if($addDocument->isSubmitted() && $addDocument->isValid()) {
            $file = $addDocument->get('document')->getData();
            if($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('documents_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    return $this->redirectToRoute('document', [
                        'id' => $user->getId(),
                        'error_msg' => 'Une erreur c\'est produite: ' . $e
                    ]);
                }

                $document->setNom($newFilename);
                $document->setLabel($addDocument->get('label')->getData());
                $document->setUser($user);

                $this->entityManager->persist($document);
                $this->entityManager->flush();
                return $this->redirectToRoute('document', [
                    'id' => $user->getId(),
                ]);
            }

            return $this->redirectToRoute('document', [
                'documents' => $user->getDocuments(),
                'id' => $user->getId()
            ]);
        }

        return $this->render('document/add_document.html.twig', [
            'add_document' => $addDocument->createView()
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("document/delete_document/{id}", name="delete_document")
     */
    public function deleteDocument(Document $document): Response
    {
        $user = $document->getUser();
        $fileSystem = new Filesystem();
        $fileSystem->remove([$this->getParameter('documents_directory') . '/' . $document->getNom()]);
        $this->entityManager->remove($document);
        $this->entityManager->flush();

        return $this->redirectToRoute('document', [
            'id' => $user->getId()
        ]);
    }

}
