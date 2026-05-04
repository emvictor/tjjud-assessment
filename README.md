# Biblioteca TJRJ - Avaliação Técnica

Sistema de gerenciamento de acervo de livros, autores e assuntos, com geração de relatórios consolidados financeiros. Projeto desenvolvido como parte da avaliação técnica (`tjjud_assessment`).

---

## Tecnologias e Arquitetura

- **Backend:** PHP 8.x, Symfony 6.4+ (Webapp)
- **Banco de Dados:** MySQL 8 (via Docker), Doctrine ORM
- **Frontend:** Twig, UX Twig Component, Bootstrap 5
- **Gerenciamento de Assets:** AssetMapper (dispensando uso de bundlers como Webpack/Vite)
- **Qualidade de Código:** Prettier (com plugin Twig)
- **Testes:** PHPUnit

## Pré-requisitos

Certifique-se de ter as seguintes ferramentas instaladas em seu ambiente de desenvolvimento:

- [PHP 8.x](https://www.php.net/) (extensões requeridas: `cli`, `pdo_mysql`, `xml`, `mbstring`, `curl`, `zip`, `intl`)
- [Composer](https://getcomposer.org/)
- [Symfony CLI](https://symfony.com/download)
- [Docker e Docker Compose](https://www.docker.com/) (Para provisionamento rápido do banco de dados)
- [Node.js e NPM](https://nodejs.org/) (Exclusivo para as dependências de formatação do Prettier)

## Instalação e Configuração

**1. Clone o repositório e acesse o diretório:**

```bash
git clone [https://github.com/emvictor/tjjud-assessment.git](https://github.com/emvictor/tjjud-assessment.git)
cd tjjud-assessment
```

**2. Instale as dependências (Backend e Frontend):**

```bash
composer install
php bin/console importmap:install
npm install
```

**3. Configure as credenciais do banco de dados:**
Crie um arquivo .env.local na raiz do projeto para isolar suas credenciais. Insira o seguinte conteúdo:

```
DATABASE_PASSWORD="sua-senha"
DATABASE_URL="mysql://root:${DATABASE_PASSWORD}@127.0.0.1:3306/tjjud_assessment?serverVersion=8.0&charset=utf8mb4"
```

**4. Suba a infraestrutura do Banco de Dados (Docker):**

```bash
docker compose --env-file .env.local up -d
```

**5. Construa a estrutura de tabelas e views:**

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

## Executando a Aplicação

Inicie o servidor de desenvolvimento embutido do Symfony:

```bash
symfony server:start -d
```

Acesse a aplicação no seu navegador padrão: http://localhost:8000

## Executando os Testes Funcionais

O projeto conta com uma suíte de testes (PHPUnit). O Symfony isola o ambiente de testes por segurança, portanto, é necessário apontar para o contêiner Docker através de um arquivo específico.

1. Configure o banco de dados de teste:

```bash
cp .env.local .env.test.local

php bin/console doctrine:database:drop --force --env=test --if-exists
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test --no-interaction
```

2. Executa os testes

```bash 
php bin/phpunit
```
