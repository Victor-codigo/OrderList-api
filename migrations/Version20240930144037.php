<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240930144037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds table Share';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE Share (id CHAR(36) NOT NULL, list_orders_id CHAR(36) NOT NULL, group_id CHAR(36) NOT NULL, user_id CHAR(36) NOT NULL, expire DATETIME NOT NULL, INDEX IDX_list_orders (list_orders_id), INDEX IDX_user_id (user_id), UNIQUE INDEX u_share_id (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE Share');
    }
}
