<?php

namespace App\Controller\Back;

use App\Entity\Comment;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CommentController extends AbstractController
{
    #[Route('/api/comment', name: 'createComment', methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        CommentRepository $commentRepository,
        ArticleRepository $articleRepository,
        UserRepository $userRepository
    ): JsonResponse {
        $requestContent = $request->toArray();

        $comment = new Comment();
        $comment->setContent($requestContent['content']);

        $article = $articleRepository->find($requestContent['article_id'] ?? -1);

        if (null === $article) {
            throw new InvalidArgumentException("Invalid article_id");
        }

        $user = $userRepository->find($requestContent['author_id'] ?? -1);

        if (null === $user) {
            throw new InvalidArgumentException("Invalid author_id");
        }

        if (null !== $parentCommentId = $requestContent['parent_comment_id'] ?? null) {
            $parentComment = $commentRepository->find($parentCommentId);

            if (null === $parentComment || null !== $parentComment->getParentComment()) {
                throw new InvalidArgumentException("Invalid parent_comment_id");
            }

            $comment->setParentComment($parentComment);
        }

        $comment->setArticle($article);
        $comment->setAuthor($user);
        $commentRepository->save($comment);

        $jsonComment = $serializer->serialize($comment, 'json', ['groups' => 'comment']);

        return new JsonResponse($jsonComment, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/comments', name: 'getCommentList', methods: ['GET'])]
    public function list(CommentRepository $commentRepository, SerializerInterface $serializer): JsonResponse
    {
        $comments = $commentRepository->findAll();

        $jsonComment = $serializer->serialize($comments, 'json', ['groups' => 'comment']);

        return new JsonResponse($jsonComment, Response::HTTP_OK, [], true);
    }
}
