<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240628074204 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the user Guest, to try the application';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL

            INSERT INTO Profile
                (
                    id,
                    image
                )
            VALUES
                (
                    'a1c35f1d-b4e3-4d3a-9719-a9509806ba47',
                    null
                );

        SQL);

        $this->addSql(<<<SQL

            INSERT INTO Users
                (
                    id,
                    email,
                    password,
                    name,
                    roles,
                    created_on
                )
            VALUES
                (
                    'a1c35f1d-b4e3-4d3a-9719-a9509806ba47',
                    'guest@email.com',
                    '$2y$13$96m4PJR4W32B6CFFPmXYE.LQpb3fRl0ng/zm59Iz1PJ1vV83iBzUG',
                    'Guest',
                    '["ROLE_USER"]',
                    '2024-06-27 15:29:19'
                );

        SQL);

        $this->addSql(<<<SQL

            INSERT INTO `Groups`
                (
                    id,
                    name,
                    description,
                    type,
                    image,
                    created_on
                )
            VALUES
                (
                    '69e2e3ad-6f2a-4706-97c8-2fcc70bb398b',
                    'Guest885358',
                    null,
                    'TYPE_USER',
                    null,
                    '2024-06-27 15:32:49'
                );

        SQL);

        $this->addSql(<<<SQL

            INSERT INTO Users_Group
                (
                    id,
                    group_id,
                    user_id,
                    roles
                )
            VALUES
                (
                    1,
                    '69e2e3ad-6f2a-4706-97c8-2fcc70bb398b',
                    'a1c35f1d-b4e3-4d3a-9719-a9509806ba47',
                    '["GROUP_ROLE_ADMIN"]'
                );

        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<SQL

            DELETE FROM Users
            WHERE id = 'a1c35f1d-b4e3-4d3a-9719-a9509806ba47';

        SQL);

        $this->addSql(<<<SQL

            DELETE FROM Profile
            WHERE id = 'a1c35f1d-b4e3-4d3a-9719-a9509806ba47';

        SQL);

        $this->addSql(<<<SQL

            DELETE FROM `Groups`
            WHERE id = '69e2e3ad-6f2a-4706-97c8-2fcc70bb398b';

        SQL);

        $this->addSql(<<<SQL

            DELETE FROM Users_Group
            WHERE id = 1;

        SQL);
    }
}
