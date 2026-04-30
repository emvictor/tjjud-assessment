<?php

namespace App\Tests\Controller;

use App\Entity\Autor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AutorControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    /** @var EntityRepository<Autor> */
    private EntityRepository $autorRepository;
    private string $path = '/autor/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->autorRepository = $this->manager->getRepository(Autor::class);

        foreach ($this->autorRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Autor index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'autor[nome]' => 'Testing',
            'autor[livros]' => 'Testing',
        ]);

        self::assertResponseRedirects('/autor');

        self::assertSame(1, $this->autorRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new Autor();
        $fixture->setNome('My Title');
        $fixture->setLivros('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Autor');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new Autor();
        $fixture->setNome('Value');
        $fixture->setLivros('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'autor[nome]' => 'Something New',
            'autor[livros]' => 'Something New',
        ]);

        self::assertResponseRedirects('/autor');

        $fixture = $this->autorRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getNome());
        self::assertSame('Something New', $fixture[0]->getLivros());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new Autor();
        $fixture->setNome('Value');
        $fixture->setLivros('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/autor');
        self::assertSame(0, $this->autorRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
