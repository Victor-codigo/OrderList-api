<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230123110524 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Tables Groups, Users_Group';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `Groups` (id CHAR(36) NOT NULL, name VARCHAR(50) NOT NULL, description VARCHAR(500) DEFAULT NULL, type VARCHAR(50) NOT NULL, image VARCHAR(256), created_on DATETIME NOT NULL, UNIQUE INDEX UNIQ_F7C13C465E237E06 (name), UNIQUE INDEX u_groups_id (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Users_Group (id INT AUTO_INCREMENT NOT NULL, group_id CHAR(36) NOT NULL, user_id CHAR(36) NOT NULL, roles JSON NOT NULL, INDEX IDX_CDEA405FFE54D947 (group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Users_Group ADD CONSTRAINT FK_CDEA405FFE54D947 FOREIGN KEY (group_id) REFERENCES `Groups` (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Users_Group DROP FOREIGN KEY FK_CDEA405FFE54D947');
        $this->addSql('DROP TABLE `Groups`');
        $this->addSql('DROP TABLE Users_Group');
    }
}
