<?php

namespace App\Controller\Back;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ArticleController extends AbstractController
{
    #[Route('/api/article', name: 'createArticle', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $article = $serializer->deserialize($request->getContent(), Article::class, 'json');
        $em->persist($article);
        $em->flush();

        $jsonArticle = $serializer->serialize($article, 'json');

        return new JsonResponse($jsonArticle, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/article/{id}', name: 'getArticle', methods: ['GET'])]
    public function get(Article $article, SerializerInterface $serializer): JsonResponse
    {
        $jsonArticle = $serializer->serialize($article, 'json', ['groups' => 'article']);

        return new JsonResponse($jsonArticle, Response::HTTP_OK, [], true);
    }
}
