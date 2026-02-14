<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use App\Form\CommentType;
use App\Repository\ConferenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CommentController extends AbstractController
{
     private CommentRepository $commentRepository;
     private ConferenceRepository $conferenceRepository;

     public function __construct(CommentRepository $commentRepo, ConferenceRepository $conferenceRepo) {
	     $this->commentRepository = $commentRepo;
	     $this->conferenceRepository = $conferenceRepo;
	}

    #[Route('/admin/conference/{id}/comment', name: 'admin.comment.indexby')]
    public function indexby(int $id): Response
    {
	$conference = $this->conferenceRepo->findById($id);
	$comments = $this->commentRepo->findByConferenceId($id);
        
	return $this->render('comment/indexby.html.twig', [
	    'conference' => $conference,
            'comments' => $comments,
        ]);
    }

     #[Route('/admin/comment/{id}', name: 'admin.comment.show')]
     public function index(int $id): Response
     {
        $comment = $this->commentRepo->findById($id);

	return $this->render('comment/index.html.twig', [
            'comment' => $comment,
        ]);
     }

     #[Route('/admin/comment/comment/create', name: 'admin.comment.create')]
     public function create(Request $request, EntityManager $em, SluggerInterface $slugger): Response
     {
	 $comment = new Comment('name');
	 $form = $this->createForm(CommentType::class, $comment);
	 $form->handleRequest($request);
         
	 if ($form->isSubmitted() && $form->isValid()) {
        //recuperer le chemin de l'image, creer le schematic et l'associer au comment
        $imagePath = $form->getData()->getSchematic();  // attention it is a text type
        if ($imagePath) {
            $imgoriginalFilename = pathinfo($imagePath, PATHINFO_FILENAME);
	        // this is needed to safely include the file name as part of the URL
	        $imgsafeFilename = $slugger->slug($imgoriginalFilename);
            $imgnewFilename = $imgsafeFilename.'-'.uniqid().guessExtension($imagePath);
        }
         $schematic = new Schematic();
         $schematic->setImagePath($imgnewFilename);
         $comment->setSchematic($schematic);
         // FRED: TBC after this 09/01/2016 migration
         $em->persist($comment);
	     $em->flush();
	     $this->addFlash('success', 'le commentaire a bien ete cree');
	     return $this->redirectToRoute('admin.conference.indexby');
	 }
	 return $this->render('admin/comment/create.html.twig', [
		 'form' => $form
	 ]);
     }

     #[Route('/admin/comment/{id}/delete', name: 'admin.comment.delete', methods: ['DELETE'])]
        public function remove(Comment $comment, EntityManagerInterface $em)
        {
           $em->remove($comment);
           $em->flush();
           $this->addFlash('success', 'Le commentaire a bien ete supprime');
           return $this->redirectToRoute('admin.comment.indexby', ['id' => $comment->getConference()->getId()]);
        }
}
