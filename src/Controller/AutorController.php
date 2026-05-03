<?php

namespace App\Controller;

use App\Entity\Autor;
use App\Form\AutorType;
use App\Repository\AutorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/autor')]
final class AutorController extends AbstractController
{
    #[Route(name: 'app_autor_index', methods: ['GET'])]
    public function index(AutorRepository $autorRepository): Response
    {
        return $this->render('autor/index.html.twig', [
            'autors' => $autorRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_autor_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $autor = new Autor();
        $form = $this->createForm(AutorType::class, $autor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($autor);
            $entityManager->flush();

            return $this->redirectToRoute('app_autor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('autor/new.html.twig', [
            'autor' => $autor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_autor_show', methods: ['GET'])]
    public function show(Autor $autor): Response
    {
        return $this->render('autor/show.html.twig', [
            'autor' => $autor,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_autor_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Autor $autor, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AutorType::class, $autor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_autor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('autor/edit.html.twig', [
            'autor' => $autor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_autor_delete', methods: ['POST'])]
    public function delete(Request $request, Autor $autor, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $autor->getId(), $request->getPayload()->getString('_token'))) {
        
        if (!$autor->getLivros()->isEmpty()) {
            $this->addFlash('danger', 'Operação negada: Este autor possui ' . $autor->getLivros()->count() . ' livro(s) associado(s) e não pode ser excluído.');
            
            return $this->redirectToRoute('app_autor_show', ['id' => $autor->getId()]);
        }

        try {
            $entityManager->remove($autor);
            $entityManager->flush();
            $this->addFlash('success', 'Autor removido com sucesso!');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erro técnico ao excluir: ' . $e->getMessage());
            return $this->redirectToRoute('app_autor_show', ['id' => $autor->getId()], Response::HTTP_SEE_OTHER);
        }
    }

    return $this->redirectToRoute('app_autor_index');
    }
}
