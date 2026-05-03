<?php

namespace App\Tests\Controller;

use App\Entity\Autor;
use App\Entity\Livro;
use App\Entity\Assunto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RelatorioControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();

        $this->manager->createQuery('DELETE FROM App\Entity\Livro')->execute();
        $this->manager->createQuery('DELETE FROM App\Entity\Autor')->execute();
        $this->manager->createQuery('DELETE FROM App\Entity\Assunto')->execute();
        $this->manager->clear();
    }

    public function testRelatorioGroupingLogic(): void
    {
        $assunto = new Assunto();
        $assunto->setDescricao('Geral');
        $this->manager->persist($assunto);

        $autorA = new Autor();
        $autorA->setNome('Autor A');
        $this->manager->persist($autorA);

        for ($i = 1; $i <= 2; $i++) {
            $livro = new Livro();
            $livro->setTitulo("Livro $i")
                ->setEditora('Editora Teste')
                ->setEdicao(1)
                ->setAnoPublicacao('2026')
                ->setValor('50.00');
            $livro->addAutore($autorA)->addAssunto($assunto);
            $this->manager->persist($livro);
        }

        $autorB = new Autor();
        $autorB->setNome('Autor B');
        $this->manager->persist($autorB);

        $livro3 = new Livro();
        $livro3->setTitulo('Livro 3')
            ->setEditora('Editora Teste')
            ->setEdicao(1)
            ->setAnoPublicacao('2026')
            ->setValor('50.00');
        $livro3->addAutore($autorB)->addAssunto($assunto);
        $this->manager->persist($livro3);

        $this->manager->flush();

        $crawler = $this->client->request('GET', '/relatorio');

        self::assertResponseIsSuccessful();

        $jsonPayload = $crawler->filter('#test-relatorio-data')->attr('data-payload');
        $data = json_decode($jsonPayload, true);

        self::assertCount(2, $data, 'The report should group by exactly 2 authors.');
        self::assertArrayHasKey('Autor A', $data);
        self::assertArrayHasKey('Autor B', $data);

        self::assertCount(2, $data['Autor A'], 'Autor A should have 2 book entries.');
        self::assertCount(1, $data['Autor B'], 'Autor B should have 1 book entry.');

        $firstBook = $data['Autor A'][0];
        self::assertArrayHasKey('livro_titulo', $firstBook);
        self::assertArrayHasKey('livro_valor', $firstBook);
        self::assertArrayHasKey('assuntos', $firstBook);
    }
}