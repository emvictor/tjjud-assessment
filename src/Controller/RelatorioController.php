<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RelatorioController extends AbstractController
{
    #[Route('/relatorio', name: 'app_relatorio')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $conn = $entityManager->getConnection();

        $sql = 'SELECT * FROM vw_relatorio_livros ORDER BY autor_nome ASC, livro_titulo ASC';
        $resultSet = $conn->executeQuery($sql);
        $dadosBrutos = $resultSet->fetchAllAssociative();

        $relatorioAgrupado = [];
        foreach ($dadosBrutos as $linha) {
            $nomeAutor = $linha['autor_nome'];
            if (!isset($relatorioAgrupado[$nomeAutor])) {
                $relatorioAgrupado[$nomeAutor] = [];
            }
            $relatorioAgrupado[$nomeAutor][] = $linha;
        }

        return $this->render('relatorio/index.html.twig', [
            'relatorio' => $relatorioAgrupado,
        ]);
    }
}
