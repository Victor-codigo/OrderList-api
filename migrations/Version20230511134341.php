<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230511134341 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates tables, List_Orders, ListOrders_Orders and Orders';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ListOrders_Orders (id CHAR(36) NOT NULL, order_id CHAR(36) NOT NULL, list_order_id CHAR(36) NOT NULL, bought TINYINT(1) NOT NULL, INDEX IDX_order_id (order_id), INDEX IDX_list_order_id (list_order_id), UNIQUE INDEX u_list_order_id (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE List_Orders (id CHAR(36) NOT NULL, user_id CHAR(36) NOT NULL, name CHAR(36) NOT NULL, description VARCHAR(500) DEFAULT NULL, date_to_buy DATETIME DEFAULT NULL, created_on DATETIME NOT NULL, INDEX IDX_user_id (user_id), INDEX IDX_name (name), UNIQUE INDEX u_list_order_id (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Orders (id CHAR(36) NOT NULL, product_id CHAR(36) NOT NULL, shop_id CHAR(36) NOT NULL, user_id CHAR(36) NOT NULL, group_id CHAR(36) NOT NULL, description VARCHAR(500) DEFAULT NULL, amount DECIMAL(10,3) DEFAULT NULL, created_on DATETIME NOT NULL, INDEX IDX_user_id (user_id), INDEX IDX_group_id (group_id), INDEX IDX_product_id (product_id), INDEX IDX_shop_id (shop_id), UNIQUE INDEX u_order_id (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ListOrders_Orders ADD CONSTRAINT FK_FE703FB4F3F25E58 FOREIGN KEY (list_order_id) REFERENCES List_Orders (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ListOrders_Orders ADD CONSTRAINT FK_FE703FB48D9F6D38 FOREIGN KEY (order_id) REFERENCES Orders (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Orders ADD CONSTRAINT FK_E283F8D84584665A FOREIGN KEY (product_id) REFERENCES Products (id)');
        $this->addSql('ALTER TABLE Orders ADD CONSTRAINT FK_E283F8D84D16C4DD FOREIGN KEY (shop_id) REFERENCES Shops (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ListOrders_Orders DROP FOREIGN KEY FK_FE703FB4F3F25E58');
        $this->addSql('ALTER TABLE ListOrders_Orders DROP FOREIGN KEY FK_FE703FB48D9F6D38');
        $this->addSql('ALTER TABLE Orders DROP FOREIGN KEY FK_E283F8D84584665A');
        $this->addSql('ALTER TABLE Orders DROP FOREIGN KEY FK_E283F8D84D16C4DD');
        $this->addSql('DROP TABLE ListOrders_Orders');
        $this->addSql('DROP TABLE List_Orders');
        $this->addSql('DROP TABLE Orders');
        $this->addSql('ALTER TABLE Shops CHANGE id id CHAR(36) NOT NULL, CHANGE group_id group_id CHAR(36) NOT NULL');
    }
}
