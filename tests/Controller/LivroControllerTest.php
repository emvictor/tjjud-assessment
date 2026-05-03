<?php

namespace App\Tests\Controller;

use App\Entity\Autor;
use App\Entity\Assunto;
use App\Entity\Livro;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use \Symfony\Bundle\TwigBundle\DataCollector\TwigDataCollector;

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

        $this->manager->createQuery('DELETE FROM App\Entity\Livro')->execute();
        $this->manager->createQuery('DELETE FROM App\Entity\Autor')->execute();
        $this->manager->createQuery('DELETE FROM App\Entity\Assunto')->execute();
        $this->manager->clear();
    }
    public function testNew(): void
    {
        $this->client->catchExceptions(false);

        // 1. Create Prerequisite Data
        $autor = new Autor();
        $autor->setNome('Machado de Assis');
        $assunto = new Assunto();
        $assunto->setDescricao('Romance');

        $this->manager->persist($autor);
        $this->manager->persist($assunto);
        $this->manager->flush();

        // 2. Setup the session and CSRF token manually
        $session = static::getContainer()->get('session.factory')->createSession();
        $session->start();

        $request = new \Symfony\Component\HttpFoundation\Request();
        $request->setSession($session);
        static::getContainer()->get('request_stack')->push($request);

        // In Symfony Forms, the token ID is typically the form name (usually the lowercase class name)
        $csrfToken = static::getContainer()
            ->get('security.csrf.token_manager')
            ->getToken('livro')
            ->getValue();

        $session->save();
        static::getContainer()->get('request_stack')->pop();

        // Sync the client's cookies with our manual session
        $cookie = new \Symfony\Component\BrowserKit\Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);

        // 3. Perform a raw POST request (Purely Functional)
        // We pass the data exactly as the Form component expects it
        $this->client->request('POST', sprintf('%snew', $this->path), [
            'livro' => [
                'titulo' => 'Dom Casmurro',
                'editora' => 'Garnier',
                'edicao' => 1,
                'anoPublicacao' => '1899',
                'valor' => '55.00',
                'autores' => [$autor->getId()],
                'assuntos' => [$assunto->getId()],
                '_token' => $csrfToken, // Include the token inside the form array
            ],
        ]);

        // 4. Assertions
        self::assertResponseRedirects('/livro');
        self::assertSame(1, $this->livroRepository->count([]));
        $createdLivro = $this->livroRepository->findOneBy(['titulo' => 'Dom Casmurro']);

        self::assertNotNull($createdLivro);

        // Verify Autores
        self::assertCount(1, $createdLivro->getAutores());
        self::assertEquals('Machado de Assis', $createdLivro->getAutores()[0]->getNome());

        // Verify Assuntos (The missing piece!)
        self::assertCount(1, $createdLivro->getAssuntos());
        self::assertEquals('Romance', $createdLivro->getAssuntos()[0]->getDescricao());
    }

    public function testShow(): void
    {
        $fixture = new Livro();
        $fixture->setTitulo('O Alienista');
        $fixture->setEditora('Garnier');
        $fixture->setEdicao(1);
        $fixture->setAnoPublicacao('1882');
        $fixture->setValor('40.00');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertSelectorTextContains('body', 'O Alienista');
    }
    public function testRemove(): void
    {
        $fixture = new Livro();
        $fixture->setTitulo('Livro para Excluir');
        $fixture->setEditora('Teste');
        $fixture->setEdicao(1);
        $fixture->setAnoPublicacao('2024');
        $fixture->setValor('10.00');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $id = $fixture->getId();

        $session = static::getContainer()->get('session.factory')->createSession();
        $session->start();

        $request = new \Symfony\Component\HttpFoundation\Request();
        $request->setSession($session);
        static::getContainer()->get('request_stack')->push($request);

        $csrfToken = static::getContainer()
            ->get('security.csrf.token_manager')
            ->getToken('delete' . $id)
            ->getValue();

        $session->save();

        static::getContainer()->get('request_stack')->pop();

        $cookie = new \Symfony\Component\BrowserKit\Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);

        $this->client->request('POST', sprintf('%s%s', $this->path, $id), [
            '_token' => $csrfToken,
        ]);

        self::assertResponseRedirects('/livro');

        self::assertSame(0, $this->livroRepository->count([]));
    }

    public function testCannotCreateDuplicateLivro(): void
    {
        $this->client->catchExceptions(false);

        $livro1 = new Livro();
        $livro1->setTitulo('Livro Duplicado');
        $livro1->setEditora('Editora X');
        $livro1->setEdicao(1);
        $livro1->setAnoPublicacao('2024');
        $livro1->setValor('100.00');

        $this->manager->persist($livro1);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%snew', $this->path));

        $this->client->submitForm('Salvar', [
            'livro[titulo]' => 'Livro Duplicado',
            'livro[editora]' => 'Editora X',
            'livro[edicao]' => 1,
            'livro[anoPublicacao]' => '2025',
            'livro[valor]' => '150.00',
        ]);


        self::assertResponseStatusCodeSame(422);

        self::assertSame(1, $this->livroRepository->count([]));
    }
}