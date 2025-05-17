<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/books')]
class BookController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'app_book_index', methods: ['GET'])]
    public function index(BookRepository $bookRepository): JsonResponse
    {
        $books = $bookRepository->findAll();
        $data = $this->serializer->serialize($books, 'json', ['groups' => 'book:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'app_book_show', methods: ['GET'])]
    public function show(Book $book): JsonResponse
    {
        $data = $this->serializer->serialize($book, 'json', ['groups' => 'book:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'app_book_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $book = $this->serializer->deserialize($request->getContent(), Book::class, 'json');
        
        $errors = $this->validator->validate($book);
        if (count($errors) > 0) {
            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        $data = $this->serializer->serialize($book, 'json', ['groups' => 'book:read']);
        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'app_book_update', methods: ['PUT'])]
    public function update(Request $request, Book $book): JsonResponse
    {
        $this->serializer->deserialize($request->getContent(), Book::class, 'json', ['object_to_populate' => $book]);
        
        $errors = $this->validator->validate($book);
        if (count($errors) > 0) {
            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        $data = $this->serializer->serialize($book, 'json', ['groups' => 'book:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'app_book_delete', methods: ['DELETE'])]
    public function delete(Book $book): JsonResponse
    {
        $this->entityManager->remove($book);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
} 