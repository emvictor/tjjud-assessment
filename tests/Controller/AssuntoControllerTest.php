<?php

namespace App\Tests\Controller;

use App\Entity\Assunto;
use App\Entity\Livro;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;

final class AssuntoControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $assuntoRepository;
    private string $path = '/assunto/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->assuntoRepository = $this->manager->getRepository(Assunto::class);

        $this->manager->createQuery('DELETE FROM App\Entity\Livro')->execute();
        $this->manager->createQuery('DELETE FROM App\Entity\Assunto')->execute();
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
        $csrfToken = $this->generateCsrfToken('assunto');

        $this->client->request('POST', sprintf('%snew', $this->path), [
            'assunto' => [
                'descricao' => 'Ficção Científica',
                '_token' => $csrfToken,
            ],
        ]);

        self::assertResponseRedirects('/assunto');
        self::assertSame(1, $this->assuntoRepository->count([]));
    }

    public function testEdit(): void
    {
        $fixture = new Assunto();
        $fixture->setDescricao('Antigo');
        $this->manager->persist($fixture);
        $this->manager->flush();

        $csrfToken = $this->generateCsrfToken('assunto');

        $this->client->request('POST', sprintf('%s%s/edit', $this->path, $fixture->getId()), [
            'assunto' => [
                'descricao' => 'Novo',
                '_token' => $csrfToken,
            ],
        ]);

        self::assertResponseRedirects('/assunto');
        $this->manager->refresh($fixture);
        self::assertSame('Novo', $fixture->getDescricao());
    }

    public function testRemove(): void
    {
        $fixture = new Assunto();
        $fixture->setDescricao('Para Deletar');
        $this->manager->persist($fixture);
        $this->manager->flush();

        $id = $fixture->getId();
        $csrfToken = $this->generateCsrfToken('delete' . $id);

        $this->client->request('POST', sprintf('%s%s', $this->path, $id), [
            '_token' => $csrfToken,
        ]);

        self::assertResponseRedirects('/assunto');
        self::assertSame(0, $this->assuntoRepository->count([]));
    }

    public function testCannotRemoveAssuntoWithLivros(): void
    {
        $assunto = new Assunto();
        $assunto->setDescricao('Tecnologia');

        $livro = new Livro();
        $livro->setTitulo('Clean Code');
        $livro->setEditora('Prentice Hall');
        $livro->setEdicao(1);
        $livro->setAnoPublicacao('2008');
        $livro->setValor('120.00');

        $assunto->addLivro($livro);
        $this->manager->persist($assunto);
        $this->manager->persist($livro);
        $this->manager->flush();

        $id = $assunto->getId();
        $csrfToken = $this->generateCsrfToken('delete' . $id);

        $this->client->request('POST', sprintf('%s%s', $this->path, $id), [
            '_token' => $csrfToken,
        ]);

        self::assertResponseRedirects(sprintf('/assunto/%s', $id));
        self::assertSame(1, $this->assuntoRepository->count(['id' => $id]));
    }
}