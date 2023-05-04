<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    #[Route('/article', name: 'app_article', methods:['GET'])]
    public function index(): Response
    {
        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }

    #[Route('/article/{id}', name: 'app_article', methods:['GET'])]
    public function getArticle($id, EntityManagerInterface $em): Response
    {
        $article = $em->getRepository(Article::class)->findById($id);
        $comments = $em->getRepository(Comment::class)->findByArticle($id);
        return new JsonResponse([$article, $comments], 200);
    }
}
