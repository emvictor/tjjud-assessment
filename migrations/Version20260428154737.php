<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260428154737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE assunto (id INT AUTO_INCREMENT NOT NULL, descricao VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE autor (id INT AUTO_INCREMENT NOT NULL, nome VARCHAR(40) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE livro (id INT AUTO_INCREMENT NOT NULL, titulo VARCHAR(40) NOT NULL, editora VARCHAR(40) NOT NULL, edicao INT NOT NULL, ano_publicacao VARCHAR(4) NOT NULL, valor NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE livro_autor (livro_id INT NOT NULL, autor_id INT NOT NULL, INDEX IDX_67499925864C5AF (livro_id), INDEX IDX_674999214D45BBE (autor_id), PRIMARY KEY(livro_id, autor_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE livro_assunto (livro_id INT NOT NULL, assunto_id INT NOT NULL, INDEX IDX_53C2C52A5864C5AF (livro_id), INDEX IDX_53C2C52A4CE74285 (assunto_id), PRIMARY KEY(livro_id, assunto_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE livro_autor ADD CONSTRAINT FK_67499925864C5AF FOREIGN KEY (livro_id) REFERENCES livro (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE livro_autor ADD CONSTRAINT FK_674999214D45BBE FOREIGN KEY (autor_id) REFERENCES autor (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE livro_assunto ADD CONSTRAINT FK_53C2C52A5864C5AF FOREIGN KEY (livro_id) REFERENCES livro (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE livro_assunto ADD CONSTRAINT FK_53C2C52A4CE74285 FOREIGN KEY (assunto_id) REFERENCES assunto (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE livro_autor DROP FOREIGN KEY FK_67499925864C5AF');
        $this->addSql('ALTER TABLE livro_autor DROP FOREIGN KEY FK_674999214D45BBE');
        $this->addSql('ALTER TABLE livro_assunto DROP FOREIGN KEY FK_53C2C52A5864C5AF');
        $this->addSql('ALTER TABLE livro_assunto DROP FOREIGN KEY FK_53C2C52A4CE74285');
        $this->addSql('DROP TABLE assunto');
        $this->addSql('DROP TABLE autor');
        $this->addSql('DROP TABLE livro');
        $this->addSql('DROP TABLE livro_autor');
        $this->addSql('DROP TABLE livro_assunto');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
