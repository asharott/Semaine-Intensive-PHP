<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
     */
    public function index(ArticleRepository $repo)
    {
        $articles = $repo->findAll();

        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'articles' => $articles
        ]);
    }

    /** 
     * @Route("/", name="home")
    */
    public function home() {
        return $this->render('blog/home.html.twig');
    }

    /**
     * @route("/blog/new", name="blog_create")
     * @route("/blog/{id}/edit" , name="blog_edit")
     */
    public function form(Article $article = null, Request $request, ObjectManager $manager, UserInterface $user) {
       
        if(!$article) {

            $article = new Article(); 
       }
        
    $form = $this->createForm(ArticleType::class, $article);
          
    $form->handleRequest($request);
        
    if($form->isSubmitted() && $form->isValid()) {
        if(!$article->getId()) {
            $article->setCreatedAt(new \DateTime());
            $article->setIdAuthor($user->getId());
        }
            
        $manager->persist($article);
        $manager->flush();
        return $this->redirectToRoute('blog_edit', ['id' => $article->getId()]);
        return $this->redirectToRoute('blog_show', ['id' => $article->getId()]); 
    }

        return $this->render('blog/create.html.twig', [
            'formArticle' => $form->createView(),
            'editMode' => $article->getId() !== null
        ]);
    }

    /**
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show(Article $article, Request $request, ObjectManager $manager,UserInterface $user) {
        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $comment->setCreatedAt(new \DateTime())
                    ->setArticle($article);

            $manager->persist($comment);
            $manager->flush();

            return $this->redirectToRoute('blog_show', ['id' => $article->getId()]);
        }

        return $this->render('blog/show.html.twig', [
            'article' => $article,
            'commentForm' => $form->createView(),
            'idRelation' => $article->getIdAuthor() == $user->getId()
        ]);
    }
}
