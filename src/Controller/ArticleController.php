<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\HttpFoundation\Request;

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

        //Permet la création d’une nouvelle catégorie
        #[Route('/article', name:'article_add', methods:['POST'])]
        public function add(Request $r, EntityManagerInterface $em, ValidatorInterface $v) : Response
        {
            
            $headers = $r->headers->all();
            
            if(isset($headers['token']) && !empty($headers['token'])){
                $jwt = current($headers['token']);
                $key = $this->getParameter('jwt_secret');
                try{
                    $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
                }
                catch(\Exception $e){
                    return new JsonResponse($e->getMessage(), 403);
                }
    
                if($decoded->roles != null  && in_array('ROLE_ADMIN', $decoded->roles)){
                    $author = $em->getRepository(User::class)->findById($r->get('author'));
                    $category = $em->getRepository(Category::class)->findById($r->get('category'));
                    $article = new Article();
                    $article->setTitle($r->get('title'));
                    $article->setContent($r->get('content'));
                    $article->setAuthor($author);
                    $article->setCategory($category);
                    $article->setStatus($r->get('status'));
                    $article->setCreatedAt(new \DateTimeImmutable());
                    
                    $errors = $v->validate($article); 
                    if(count($errors) > 0){
                        $e_list = [];
                        foreach($errors as $e){ 
                            $e_list[] = $e->getMessage(); 
                        }
    
                        return new JsonResponse($e_list, 400); 
                    }
    
                    $em->persist($article);
                    $em->flush();
    
                    return new JsonResponse('success', 201);
    
                }
            }
    
            return new JsonResponse('Access denied', 403);
        }

        // Permet de modifier une catégorie
        #[Route('/article/{id}', name:'article_update', methods:['PATCH'])]
        public function update(Article $article = null, Request $r, ValidatorInterface $v, EntityManagerInterface $em) : Response
        {
    
           
            $headers = $r->headers->all();
            if(isset($headers['token']) && !empty($headers['token'])){
                $jwt = current($headers['token']); 
                $key = $this->getParameter('jwt_secret');
    
                try{
                    $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
                }
                catch(\Exception $e){
                    return new JsonResponse($e->getMessage(), 403);
                }
    
                if($decoded->roles != null  && in_array('ROLE_ADMIN', $decoded->roles)){
    
            if($article === null){
                return new JsonResponse('Catégorie introuvable', 404); 
            }
    
            $params = 0;
            if($r->get('title') != null || $r->get('content') != null  ){
                $params++;
                $article->setTitle($r->get('title'));
                $article->setContent($r->get('content'));
                $article->setCategory($r->get('category'));
                $article->setStatus($r->get('status'));
            }
    
            if($params > 0){
                $errors = $v->validate($article); 
                if(count($errors) > 0){
                    $e_list = [];
                    foreach($errors as $e){ 
                        $e_list[] = $e->getMessage(); 
                    }
    
                    return new JsonResponse($e_list, 400);
                }
    
                $em->persist($article);
                $em->flush();
            }else{
                return new JsonResponse('Empty', 200);
            }
    
            return new JsonResponse('Modification réussi', 200);
        }
    }
    
    return new JsonResponse('Access denied', 403);
        }
    
        //Permet de supprimer un article
        #[Route('/article/{id}', name:'article_delete', methods:['DELETE'])]
        public function delete(Article $article = null, Request $r, EntityManagerInterface $em): Response
        {
    
                    $headers = $r->headers->all();

                    if(isset($headers['token']) && !empty($headers['token'])){
                        $jwt = current($headers['token']); 
                        $key = $this->getParameter('jwt_secret');
            
                        try{
                            $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
                        }
                        catch(\Exception $e){
                            return new JsonResponse($e->getMessage(), 403);
                        }
            
                        if($decoded->roles != null  && in_array('ROLE_ADMIN', $decoded->roles)){
            if($article == null){
                return new JsonResponse('Article introuvable', 404);
            }else{
                $em->remove($article);
                $em->flush();
                
                return new JsonResponse('Article supprimée', 204);
            }
        }
    }
        return new JsonResponse('Access denied', 403);
    
        }
}
