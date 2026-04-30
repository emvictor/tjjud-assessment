<?php

namespace App\Tests\Controller;

use App\Entity\Livro;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LivroControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    /** @var EntityRepository<Livro> */
    private EntityRepository $livroRepository;
    private string $path = '/livro/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->livroRepository = $this->manager->getRepository(Livro::class);

        foreach ($this->livroRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Livro index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'livro[titulo]' => 'Testing',
            'livro[editora]' => 'Testing',
            'livro[edicao]' => 'Testing',
            'livro[anoPublicacao]' => 'Testing',
            'livro[valor]' => 'Testing',
            'livro[autores]' => 'Testing',
            'livro[assuntos]' => 'Testing',
        ]);

        self::assertResponseRedirects('/livro');

        self::assertSame(1, $this->livroRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new Livro();
        $fixture->setTitulo('My Title');
        $fixture->setEditora('My Title');
        $fixture->setEdicao('My Title');
        $fixture->setAnoPublicacao('My Title');
        $fixture->setValor('My Title');
        $fixture->setAutores('My Title');
        $fixture->setAssuntos('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Livro');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new Livro();
        $fixture->setTitulo('Value');
        $fixture->setEditora('Value');
        $fixture->setEdicao('Value');
        $fixture->setAnoPublicacao('Value');
        $fixture->setValor('Value');
        $fixture->setAutores('Value');
        $fixture->setAssuntos('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'livro[titulo]' => 'Something New',
            'livro[editora]' => 'Something New',
            'livro[edicao]' => 'Something New',
            'livro[anoPublicacao]' => 'Something New',
            'livro[valor]' => 'Something New',
            'livro[autores]' => 'Something New',
            'livro[assuntos]' => 'Something New',
        ]);

        self::assertResponseRedirects('/livro');

        $fixture = $this->livroRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getTitulo());
        self::assertSame('Something New', $fixture[0]->getEditora());
        self::assertSame('Something New', $fixture[0]->getEdicao());
        self::assertSame('Something New', $fixture[0]->getAnoPublicacao());
        self::assertSame('Something New', $fixture[0]->getValor());
        self::assertSame('Something New', $fixture[0]->getAutores());
        self::assertSame('Something New', $fixture[0]->getAssuntos());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new Livro();
        $fixture->setTitulo('Value');
        $fixture->setEditora('Value');
        $fixture->setEdicao('Value');
        $fixture->setAnoPublicacao('Value');
        $fixture->setValor('Value');
        $fixture->setAutores('Value');
        $fixture->setAssuntos('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/livro');
        self::assertSame(0, $this->livroRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
