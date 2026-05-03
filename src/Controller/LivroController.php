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

        if ($request->isMethod('POST')) {
            $this->sanitizePriceInput($request);
        }

        $form = $this->createForm(LivroType::class, $livro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {

                $entityManager->persist($livro);
                $entityManager->flush();

                $this->addFlash('success', 'Livro cadastrado com sucesso!');
                return $this->redirectToRoute('app_livro_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Ocorreu um erro ao cadastrar o livro: ' . $e->getMessage());
            }
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
        if ($request->isMethod('POST')) {
            $this->sanitizePriceInput($request);
        }

        $form = $this->createForm(LivroType::class, $livro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();

                $this->addFlash('success', 'Livro atualizado com sucesso!');
                return $this->redirectToRoute('app_livro_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Ocorreu um erro ao atualizar o livro: ' . $e->getMessage());
                return $this->redirectToRoute('app_livro_edit', ['id' => $livro->getId()], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('livro/edit.html.twig', [
            'livro' => $livro,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_livro_delete', methods: ['POST'])]
    public function delete(Request $request, Livro $livro, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $livro->getId(), $request->getPayload()->getString('_token'))) {
            try {

                $entityManager->remove($livro);
                $entityManager->flush();
                $this->addFlash('success', 'Livro excluído com sucesso!');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Ocorreu um erro ao excluir o livro: ' . $e->getMessage());
                return $this->redirectToRoute('app_livro_show', ['id' => $livro->getId()], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->redirectToRoute('app_livro_index', [], Response::HTTP_SEE_OTHER);
    }

    private function sanitizePriceInput(Request $request): void
    {
        $livroData = $request->request->all('livro');
        if (isset($livroData['valor'])) {
            $livroData['valor'] = str_replace(',', '.', $livroData['valor']);
            $request->request->set('livro', $livroData);
        }
    }
}
