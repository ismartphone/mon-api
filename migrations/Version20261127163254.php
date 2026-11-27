<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261127163254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE actor (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_447556F95E237E06 ON actor (name)');
        $this->addSql('CREATE TABLE category (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(100) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_64C19C15E237E06 ON category (name)');
        $this->addSql('CREATE TABLE movie (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, release_year INTEGER NOT NULL, director VARCHAR(255) DEFAULT NULL, runtime INTEGER DEFAULT NULL, poster_url VARCHAR(500) DEFAULT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('CREATE INDEX idx_movie_title ON movie (title)');
        $this->addSql('CREATE INDEX idx_movie_release_year ON movie (release_year)');
        $this->addSql('CREATE TABLE movie_category (movie_id INTEGER NOT NULL, category_id INTEGER NOT NULL, PRIMARY KEY (movie_id, category_id), CONSTRAINT FK_DABA824C8F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_DABA824C12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_DABA824C8F93B6FC ON movie_category (movie_id)');
        $this->addSql('CREATE INDEX IDX_DABA824C12469DE2 ON movie_category (category_id)');
        $this->addSql('CREATE TABLE movie_actor (movie_id INTEGER NOT NULL, actor_id INTEGER NOT NULL, PRIMARY KEY (movie_id, actor_id), CONSTRAINT FK_3A374C658F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3A374C6510DAF24A FOREIGN KEY (actor_id) REFERENCES actor (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_3A374C658F93B6FC ON movie_actor (movie_id)');
        $this->addSql('CREATE INDEX IDX_3A374C6510DAF24A ON movie_actor (actor_id)');
        $this->addSql('CREATE TABLE rating (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, score SMALLINT NOT NULL, comment CLOB DEFAULT NULL, rated_at DATETIME NOT NULL, user_id INTEGER NOT NULL, movie_id INTEGER NOT NULL, CONSTRAINT FK_D8892622A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D88926228F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX idx_rating_rated_at ON rating (rated_at)');
        $this->addSql('CREATE UNIQUE INDEX uniq_rating_user_movie ON rating (user_id, movie_id)');
        $this->addSql('CREATE INDEX IDX_D8892622A76ED395 ON rating (user_id)');
        $this->addSql('CREATE INDEX IDX_D88926228F93B6FC ON rating (movie_id)');
        $this->addSql('CREATE TABLE "user" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, username VARCHAR(50) NOT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON "user" (username)');
        $this->addSql('CREATE TABLE user_follow (follower_id INTEGER NOT NULL, followed_id INTEGER NOT NULL, PRIMARY KEY (follower_id, followed_id), CONSTRAINT FK_D665F4DAC24F853 FOREIGN KEY (follower_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D665F4DD956F010 FOREIGN KEY (followed_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_D665F4DAC24F853 ON user_follow (follower_id)');
        $this->addSql('CREATE INDEX IDX_D665F4DD956F010 ON user_follow (followed_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE actor');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE movie');
        $this->addSql('DROP TABLE movie_category');
        $this->addSql('DROP TABLE movie_actor');
        $this->addSql('DROP TABLE rating');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE user_follow');
    }
}
