<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CommentsListController extends AbstractController
{
    #[Route('/', name: 'homePage', methods: 'GET')]
    public function index(HttpClientInterface $client): Response
    {
        try {
            $response = $client->request(
                'GET',
                'http://localhost:8000/api/comments'
            );
        } catch (TransportExceptionInterface $e) {
            throw new ServiceUnavailableHttpException();
        }

        $comments = json_decode($response->getContent(), true);

        return $this->render('last_comments.html.twig', ['comments' => $comments]);
    }
}