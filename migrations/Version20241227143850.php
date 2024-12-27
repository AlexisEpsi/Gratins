<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241227143850 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commentaire (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id_id INTEGER DEFAULT NULL, recette_id_id INTEGER DEFAULT NULL, texte CLOB NOT NULL, date DATE NOT NULL, CONSTRAINT FK_67F068BC9D86650F FOREIGN KEY (user_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_67F068BC83B016C1 FOREIGN KEY (recette_id_id) REFERENCES recette (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_67F068BC9D86650F ON commentaire (user_id_id)');
        $this->addSql('CREATE INDEX IDX_67F068BC83B016C1 ON commentaire (recette_id_id)');
        $this->addSql('CREATE TABLE favoris (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id_id INTEGER DEFAULT NULL, recette_id_id INTEGER DEFAULT NULL, CONSTRAINT FK_8933C4329D86650F FOREIGN KEY (user_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_8933C43283B016C1 FOREIGN KEY (recette_id_id) REFERENCES recette (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_8933C4329D86650F ON favoris (user_id_id)');
        $this->addSql('CREATE INDEX IDX_8933C43283B016C1 ON favoris (recette_id_id)');
        $this->addSql('CREATE TABLE ingredient (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id_id INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, CONSTRAINT FK_6BAF78709D86650F FOREIGN KEY (user_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_6BAF78709D86650F ON ingredient (user_id_id)');
        $this->addSql('CREATE TABLE recette (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id_id INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, description CLOB NOT NULL, duree INTEGER NOT NULL, personnes INTEGER NOT NULL, image VARCHAR(255) NOT NULL, CONSTRAINT FK_49BB63909D86650F FOREIGN KEY (user_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_49BB63909D86650F ON recette (user_id_id)');
        $this->addSql('CREATE TABLE recette_ingredient (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, recette_id_id INTEGER DEFAULT NULL, ingredient_id_id INTEGER DEFAULT NULL, quantite INTEGER NOT NULL, CONSTRAINT FK_17C041A983B016C1 FOREIGN KEY (recette_id_id) REFERENCES recette (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_17C041A96676F996 FOREIGN KEY (ingredient_id_id) REFERENCES ingredient (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_17C041A983B016C1 ON recette_ingredient (recette_id_id)');
        $this->addSql('CREATE INDEX IDX_17C041A96676F996 ON recette_ingredient (ingredient_id_id)');
        $this->addSql('CREATE TABLE "user" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME ON "user" (username)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE commentaire');
        $this->addSql('DROP TABLE favoris');
        $this->addSql('DROP TABLE ingredient');
        $this->addSql('DROP TABLE recette');
        $this->addSql('DROP TABLE recette_ingredient');
        $this->addSql('DROP TABLE "user"');
    }
}
