<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Post;
use App\Repository\CategorieRepository;
use App\Repository\PostRepository;
use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use phpDocumentor\Reflection\Type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;

#[Route('/api')]
class PostController extends AbstractController
{
    //Nomme la route et indique la fonction à utiliser en fonction de la méthode HTTP
    /**
     * @param PostRepository $repository
     * @param SerializerInterface $serializer
     * @return Response
     */
    #[Route('/posts', name: 'api_post_index', methods: ['GET'])]
    #[OA\Tag(name: "Posts")]
    #[OA\Get(
        path: "/api/posts",
        description: "Permet de récupérer la liste des posts",
        summary: "Lister les posts",
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID du post à rechercher',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer'
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des posts au format JSON",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        ref: new Model(type: Post::class, groups: ['list_posts'])
                    )
                )
            )
        ]
    )]
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
        $postsSerialized = $serializer->serialize($posts, 'json', ['groups' => 'list_posts']);

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
    #[OA\Tag(name: "Posts")]
    #[OA\Get(
        path: "/api/posts/{id}",
        description: "Permet de récupérer un post par son id",
        summary: "Récupérer un post",
        responses: [
            new OA\Response(
                response: 200,
                description: "Détails du post au format JSON",
                content: new OA\JsonContent(
                    ref: new Model(type: Post::class, groups: ['show_post'])
                )
            )
        ]
    )]
    public function show(PostRepository      $repository,
                         SerializerInterface $serializer,
                         int                 $id): Response
    {
        //Récupère tous les posts dans la base de données
        $post = $repository->find($id);
        $postSerialized = $serializer->serialize($post, 'json', ['groups' => 'show_post']);
        return new Response($postSerialized, 200, [
            'content-type' => 'application/json'
        ]);
    }

    #[Route('/posts', name: 'api_post_create', methods: ['POST'])]
    public function create(Request $request, SerializerInterface $serializer, CategorieRepository $catRepo, EntityManagerInterface $entityManager): Response
    {
        $body =  $request->getContent();
        //Décode le JSON en tableau PHP
        $post = $serializer->deserialize($body,Post::class, 'json');
        $json = json_decode($body);
        $post->setCreatedAt(new DateTime());

        if(isset($json->idCategorie)){
            $post->setCategorie($catRepo->find($json->idCategorie));
        }

        $post->setCreateur($this->getUser());

        $entityManager->persist($post);
        $entityManager->flush();
        return new Response($serializer->serialize($post, 'json', ['groups' => 'show_posts']), 201, [
            'content-type' => 'application/json'
        ]);
    }

    #[Route('/posts/{id}', name: 'api_post_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(PostRepository $repository, EntityManagerInterface $entityManager, int $id): Response
    {
        $post = $repository->find($id);
        $entityManager->remove($post);
        $entityManager->flush();
        return new Response(null, Response::HTTP_NO_CONTENT, );
    }

    #[Route('/posts/{id}', name: 'api_post_change', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function update(PostRepository $repository, EntityManagerInterface $entityManager, int $id, Request $request, SerializerInterface $serializer): Response
    {
        $post = $repository->find($id);
        $body =  $request->getContent();
        //Fusionne les données du JSON dans l'objet $post
        $serializer->deserialize($body,Post::class, 'json', ['object_to_populate' => $post]);
        $entityManager->flush();
        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @throws \Exception
     */
    #[Route('/posts/publies-apres', name: 'api_posts_date_after', methods: ['GET'])]
    public function publish_after(PostRepository $repository, Request $request, SerializerInterface $serializer): Response{
        $date = new DateTime($request->get('date'));
        $posts = $repository->findPostsAfter($date);
        $postsJSON = $serializer->serialize($posts, 'json', ['groups' => 'list_posts']);

        return new Response($postsJSON, Response::HTTP_OK, [
            'content-type' => 'application/json'
        ]);

    }
}
