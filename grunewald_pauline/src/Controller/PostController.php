<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PostRepository;
use App\Entity\Post;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Like;
use App\Repository\LikeRepository;


class PostController extends AbstractController
{
    private $PostRepository;

    public function __construct(PostRepository $PostRepository)
    {
        // $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        // if($this->getUser() == 'null') {
        //     return $this->redirectToRoute('app_login');
        // }
        // else{
            
        // }

        $this->PostRepository = $PostRepository;

        
    }

    #[Route('/post', name: 'app_post')]
    public function index(PostRepository $PostRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $PostRepository->findAll(),
        ]);
    }

    #[Route('/post/{id}', name: 'detail_post')]
    public function show(Post $post, $id, Request $request, EntityManagerInterface $entityManager, CommentRepository $commentsRepository): Response
    {
        $comment = new Comment();
        $form = $this->CreateForm(CommentType::class, $comment);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setAuthor($this->getUser())
                ->setPost($post);

            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirectToRoute('detail_post', [
                    'id' => $id
                ]
            );
        }
        return $this->render('post/post.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
            'comment' => $comment
        ]);
    }

    #[Route('/post/{id}/{liked}', name: 'like_post')]
    public function liked($id, $liked, Post $post, EntityManagerInterface $entityManager, CommentRepository $commentsRepository, LikeRepository $likeRepository) 
    {
    $postEntity = $this->PostRepository->find($id);
    
    // Récupérer l'utilisateur actuel
    $user = $this->getUser();

    // // Rechercher le like spécifique de l'utilisateur sur ce commentaire
    // $existingLike = $likeRepository->findOneBy([
    //     'user_id' => $user,
    //     'postid' => $id,
    // ]);

    // Si l'utilisateur a déjà aimé ce commentaire, supprimer le like
    // if ($existingLike) {
    //     return $this->redirectToRoute('detail_post', [
    //     'id' => $id,
    // ]);
    // } 
    // else {
        // Si l'utilisateur n'a pas encore aimé ce commentaire, créer un nouveau like
        $like = new Like();
        $like->setUser($user)
            ->setPost($post)
            ->setType($liked);

        $entityManager->persist($like);
        $entityManager->flush();
    // }

    return $this->redirectToRoute('detail_post', [
        'id' => $id,
        'liked' => $liked,
    ]);
    }
}
