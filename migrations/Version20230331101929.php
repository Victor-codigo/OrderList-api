<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230331101929 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added table Notifications';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE Notifications (id CHAR(36) NOT NULL, user_id CHAR(36) NOT NULL, type VARCHAR(50) NOT NULL, data JSON DEFAULT NULL, viewed TINYINT(1) NOT NULL, created_on DATETIME NOT NULL, UNIQUE INDEX u_notification_id (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE Notifications');
    }
}
