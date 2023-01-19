<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class ArticleController extends AbstractController
{
    #[Route('/page{articleId}', name: 'articlePage', methods: 'GET')]
    public function index(HttpClientInterface $client, int $articleId): Response
    {
        $orderedComments = [];

        try {
            $response = $client->request(
                'GET',
                'http://localhost:8000/api/article/' . $articleId,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => '*/*',
                        'Accept-Encoding' => 'gzip, deflate, br',
                        'Connection' => 'keep-alive',
                    ]
                ]
            );
        } catch (TransportExceptionInterface $e) {
            throw new ServiceUnavailableHttpException();
        }

        $article = json_decode($response->getContent(), true);

        foreach ($article['comments'] as $comment) {
            if (null === $comment['parentComment']) {
                $orderedComments[$comment['id']] = $comment;
                $orderedComments[$comment['id']]['replies'] = [];

                continue;
            }

            $orderedComments[$comment['parentComment']['id']]['replies'][] = $comment;
        }

        return $this->render('article.html.twig', [
            'article' => $article,
            'comments' => $orderedComments
        ]);
    }
}