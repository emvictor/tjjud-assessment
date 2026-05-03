<?php

namespace App\Tests\Controller;

use App\Entity\Autor;
use App\Entity\Livro;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;

final class AutorControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $autorRepository;
    private string $path = '/autor/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->autorRepository = $this->manager->getRepository(Autor::class);

        $this->manager->createQuery('DELETE FROM App\Entity\Livro')->execute();
        $this->manager->createQuery('DELETE FROM App\Entity\Autor')->execute();
        $this->manager->clear();
    }

    private function generateCsrfToken(string $tokenId): string
    {
        $session = static::getContainer()->get('session.factory')->createSession();
        $session->start();

        $request = new Request();
        $request->setSession($session);
        static::getContainer()->get('request_stack')->push($request);

        $token = static::getContainer()
            ->get('security.csrf.token_manager')
            ->getToken($tokenId)
            ->getValue();

        $session->save();
        static::getContainer()->get('request_stack')->pop();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);

        return $token;
    }


    public function testNew(): void
    {
        $csrfToken = $this->generateCsrfToken('autor');

        $this->client->request('POST', sprintf('%snew', $this->path), [
            'autor' => [
                'nome' => 'Clarice Lispector',
                '_token' => $csrfToken,
            ],
        ]);

        self::assertResponseRedirects('/autor');
        self::assertSame(1, $this->autorRepository->count([]));

        $autor = $this->autorRepository->findOneBy(['nome' => 'Clarice Lispector']);
        self::assertNotNull($autor);
    }

    public function testEdit(): void
    {
        $fixture = new Autor();
        $fixture->setNome('Escritor Errado');
        $this->manager->persist($fixture);
        $this->manager->flush();

        $csrfToken = $this->generateCsrfToken('autor');

        $this->client->request('POST', sprintf('%s%s/edit', $this->path, $fixture->getId()), [
            'autor' => [
                'nome' => 'Escritor Corrigido',
                '_token' => $csrfToken,
            ],
        ]);

        self::assertResponseRedirects('/autor');

        $this->manager->refresh($fixture);
        self::assertSame('Escritor Corrigido', $fixture->getNome());
    }

    public function testRemove(): void
    {
        $fixture = new Autor();
        $fixture->setNome('Autor Temporario');
        $this->manager->persist($fixture);
        $this->manager->flush();

        $id = $fixture->getId();
        $csrfToken = $this->generateCsrfToken('delete' . $id);

        $this->client->request('POST', sprintf('%s%s', $this->path, $id), [
            '_token' => $csrfToken,
        ]);

        self::assertResponseRedirects('/autor');
        self::assertSame(0, $this->autorRepository->count([]));
    }

    public function testCannotRemoveAutorWithLivros(): void
    {
        $autor = new Autor();
        $autor->setNome('Machado de Assis');

        $livro = new Livro();
        $livro->setTitulo('Memorias Postumas');
        $livro->setEditora('Editora');
        $livro->setEdicao(1);
        $livro->setAnoPublicacao('1881');
        $livro->setValor('50.00');
        $autor->addLivro($livro);

        $this->manager->persist($autor);
        $this->manager->persist($livro);
        $this->manager->flush();

        $id = $autor->getId();
        $csrfToken = $this->generateCsrfToken('delete' . $id);

        $this->client->request('POST', sprintf('%s%s', $this->path, $id), [
            '_token' => $csrfToken,
        ]);

        self::assertResponseRedirects(sprintf('/autor/%s', $id));

        self::assertSame(1, $this->autorRepository->count(['id' => $id]));

        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-danger', 'Não é possível excluir um autor que possui livros associados.');
    }
}