<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api')]
class PostController extends AbstractController
{
    //Nomme la route et indique la fonction à utiliser en fonction de la méthode HTTP
    #[Route('/posts', name: 'api_post_index', methods: ['GET'])]
    public function index(PostRepository      $repository,
                          SerializerInterface $serializer): Response
    {
        //Récupère tous les posts dans la base de données
        $posts = $repository->findAll();

        //Normalisation des posts
        //$postsNormalized = $normalizer->normalize($posts);

        //Encode les posts en JSON
        //$json = json_encode($postsNormalized);

        //Serialise les posts
        $postsSerialized = $serializer->serialize($posts, 'json');

//        //Crée une réponse HTTP
//        $response = new Response();
//        $response->setStatusCode(Response::HTTP_OK);
//        $response->headers->set('content-type', 'application/json');
//        $response->setContent($postsSerialized);

        return new Response($postsSerialized, 200, [
            'content-type' => 'application/json'
        ]);


//        dd($this->json($posts)->getContent());
    }

    #[Route('/posts/{id}', name: 'api_post_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(PostRepository      $repository,
                         SerializerInterface $serializer,
                         int                 $id): Response
    {
        //Récupère tous les posts dans la base de données
        $post = $repository->find($id);
        $postSerialized = $serializer->serialize($post, 'json');
        return new Response($postSerialized, 200, [
            'content-type' => 'application/json'
        ]);
    }

    #[Route('/posts', name: 'api_post_create', methods: ['POST'])]
    public function create(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager): Response
    {
        $body =  $request->getContent();
        //Décode le JSON en tableau PHP
        $post = $serializer->deserialize($body,Post::class, 'json');
        $post->setCreatedAt(new \DateTime());
        $entityManager->persist($post);
        $entityManager->flush();
        return new Response($serializer->serialize($post, 'json'), 201, [
            'content-type' => 'application/json'
        ]);
    }

    #[Route('/posts/{id}', name: 'api_post_show', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(PostRepository $repository, EntityManagerInterface $entityManager, int $id): Response
    {
        $post = $repository->find($id);
        $entityManager->remove($post);
        $entityManager->flush();
        return new Response(null, Response::HTTP_NO_CONTENT, );
    }

    #[Route('/posts/{id}', name: 'api_post_show', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function update(PostRepository $repository, EntityManagerInterface $entityManager, int $id, Request $request, SerializerInterface $serializer): Response
    {
        $post = $repository->find($id);
        $body =  $request->getContent();
        //Fusionne les données du JSON dans l'objet $post
        $serializer->deserialize($body,Post::class, 'json', ['object_to_populate' => $post]);
        $entityManager->flush();
        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
