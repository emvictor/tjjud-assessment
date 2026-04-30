<?php

namespace App\Controller;

use App\Entity\Livro;
use App\Form\LivroType;
use App\Repository\LivroRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/livro')]
final class LivroController extends AbstractController
{
    #[Route(name: 'app_livro_index', methods: ['GET'])]
    public function index(LivroRepository $livroRepository): Response
    {
        return $this->render('livro/index.html.twig', [
            'livros' => $livroRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_livro_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $livro = new Livro();
        $form = $this->createForm(LivroType::class, $livro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($livro);
            $entityManager->flush();

            return $this->redirectToRoute('app_livro_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('livro/new.html.twig', [
            'livro' => $livro,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_livro_show', methods: ['GET'])]
    public function show(Livro $livro): Response
    {
        return $this->render('livro/show.html.twig', [
            'livro' => $livro,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_livro_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Livro $livro, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LivroType::class, $livro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_livro_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('livro/edit.html.twig', [
            'livro' => $livro,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_livro_delete', methods: ['POST'])]
    public function delete(Request $request, Livro $livro, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$livro->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($livro);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_livro_index', [], Response::HTTP_SEE_OTHER);
    }
}
