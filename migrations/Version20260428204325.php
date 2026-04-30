<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260428204325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE VIEW vw_relatorio_livros AS
            SELECT 
                a.nome AS autor_nome,
                l.titulo AS livro_titulo,
                l.editora AS livro_editora,
                l.edicao AS livro_edicao,
                l.ano_publicacao AS livro_ano,
                l.valor AS livro_valor,
                GROUP_CONCAT(assunto.descricao SEPARATOR ', ') AS assuntos
            FROM autor a
            JOIN livro_autor la ON a.id = la.autor_id
            JOIN livro l ON l.id = la.livro_id
            LEFT JOIN livro_assunto lass ON l.id = lass.livro_id
            LEFT JOIN assunto assunto ON assunto.id = lass.assunto_id
            GROUP BY a.id, l.id
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP VIEW IF EXISTS vw_relatorio_livros');
    }
}
