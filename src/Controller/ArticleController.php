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
            // On récupère les infos envoyées en header
            $headers = $r->headers->all();
            // Si la clé 'token' existe et qu'elle n'est pas vide dans le header
            if(isset($headers['token']) && !empty($headers['token'])){
                $jwt = current($headers['token']); // Récupère la cellule 0 avec current()
                $key = $this->getParameter('jwt_secret');
    
                // On essaie de décoder le jwt
                try{
                    $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
                }
                // Si la signature n'est pas vérifiée ou que la date d'expiration est passée, il entrera dans le catch
                catch(\Exception $e){
                    return new JsonResponse($e->getMessage(), 403);
                }
    
                // On regarde si la clé 'roles' existe et si l'utilisateur possède le bon rôle
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
                    
                    $errors = $v->validate($article); // Vérifie que l'objet soit conforme avec les validations (assert)
                    if(count($errors) > 0){
                        // S'il y a au moins une erreur
                        $e_list = [];
                        foreach($errors as $e){ // On parcours toutes les erreurs
                            $e_list[] = $e->getMessage(); // On ajoute leur message dans le tableau de messages
                        }
    
                        return new JsonResponse($e_list, 400); // On retourne le tableau de messages
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
    
            // On récupère les infos envoyées en header
            $headers = $r->headers->all();
            // Si la clé 'token' existe et qu'elle n'est pas vide dans le header
            if(isset($headers['token']) && !empty($headers['token'])){
                $jwt = current($headers['token']); // Récupère la cellule 0 avec current()
                $key = $this->getParameter('jwt_secret');
    
                // On essaie de décoder le jwt
                try{
                    $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
                }
                // Si la signature n'est pas vérifiée ou que la date d'expiration est passée, il entrera dans le catch
                catch(\Exception $e){
                    return new JsonResponse($e->getMessage(), 403);
                }
    
                // On regarde si la clé 'roles' existe et si l'utilisateur possède le bon rôle
                if($decoded->roles != null  && in_array('ROLE_ADMIN', $decoded->roles)){
    
            if($article === null){
                return new JsonResponse('Catégorie introuvable', 404); // Retourne un status 404 car le 204 ne retourne pas de message
            }
    
            $params = 0;
            // On regarde si l'attribut name reçu n'est pas null
            if($r->get('title') != null || $r->get('content') != null  ){
                $params++;
                // On attribue à la category le nouveau name
                $article->setTitle($r->get('title'));
                $article->setContent($r->get('content'));
                $article->setCategory($r->get('category'));
                $article->setStatus($r->get('status'));
            }
    
            if($params > 0){
                $errors = $v->validate($article); // Vérifie que l'objet soit conforme avec les validations (assert)
                if(count($errors) > 0){
                    // S'il y a au moins une erreur
                    $e_list = [];
                    foreach($errors as $e){ // On parcours toutes les erreurs
                        $e_list[] = $e->getMessage(); // On ajoute leur message dans le tableau de messages
                    }
    
                    return new JsonResponse($e_list, 400); // On retourne le tableau de messages
                }
    
                // Si tout va bien, on sauvegarde
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
    
                    // On récupère les infos envoyées en header
                    $headers = $r->headers->all();
                    // Si la clé 'token' existe et qu'elle n'est pas vide dans le header
                    if(isset($headers['token']) && !empty($headers['token'])){
                        $jwt = current($headers['token']); // Récupère la cellule 0 avec current()
                        $key = $this->getParameter('jwt_secret');
            
                        // On essaie de décoder le jwt
                        try{
                            $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
                        }
                        // Si la signature n'est pas vérifiée ou que la date d'expiration est passée, il entrera dans le catch
                        catch(\Exception $e){
                            return new JsonResponse($e->getMessage(), 403);
                        }
            
                        // On regarde si la clé 'roles' existe et si l'utilisateur possède le bon rôle
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
