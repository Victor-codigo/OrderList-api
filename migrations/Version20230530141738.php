<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230530141738 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tables ListOrders, Orders, Products, Products_shops, Shops, Orders,';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE List_Orders (id CHAR(36) NOT NULL, user_id CHAR(36) NOT NULL, group_id CHAR(36) NOT NULL, name VARCHAR(50) NOT NULL, description VARCHAR(500) DEFAULT NULL, date_to_buy DATETIME DEFAULT NULL, created_on DATETIME NOT NULL, INDEX IDX_user_id (user_id), UNIQUE INDEX u_list_order_name (name), UNIQUE INDEX u_list_order_id (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Orders (id CHAR(36) NOT NULL, group_id CHAR(36) NOT NULL, list_orders_id CHAR(36) NOT NULL, product_id CHAR(36) NOT NULL, shop_id CHAR(36), user_id CHAR(36) NOT NULL,  description VARCHAR(500) DEFAULT NULL, amount DECIMAL(10,3) DEFAULT NULL, bought TINYINT(1) NOT NULL, created_on DATETIME NOT NULL, INDEX IDX_user_id (user_id), INDEX IDX_group_id (group_id), INDEX IDX_list_orders_id (list_orders_id), INDEX IDX_product_id (product_id), INDEX IDX_shop_id (shop_id), UNIQUE INDEX u_order_id (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Products (id CHAR(36) NOT NULL, group_id CHAR(36) NOT NULL, name VARCHAR(50) NOT NULL, description VARCHAR(500) DEFAULT NULL, image VARCHAR(256) DEFAULT NULL, created_on DATETIME NOT NULL, INDEX IDX_group_id (group_id), INDEX IDX_product_name (name), UNIQUE INDEX u_products_id (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Products_Shops (id INT AUTO_INCREMENT NOT NULL, product_id CHAR(36) NOT NULL, shop_id CHAR(36) NOT NULL, price DECIMAL(10,2) DEFAULT NULL, unit CHAR(5) NOT NULL, INDEX IDX_product_id (product_id), INDEX IDX_shop_id (shop_id), UNIQUE INDEX u_id (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Shops (id CHAR(36) NOT NULL, group_id CHAR(36) NOT NULL, name VARCHAR(50) NOT NULL, address VARCHAR(100) DEFAULT NULL, image VARCHAR(250) DEFAULT NULL, description VARCHAR(500) DEFAULT NULL, created_on DATETIME NOT NULL, INDEX idx_shop_group_id (group_id), INDEX idx_shop_name (name), UNIQUE INDEX u_shops_id (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Orders ADD CONSTRAINT FK_list_orders FOREIGN KEY (list_orders_id) REFERENCES List_Orders (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Orders ADD CONSTRAINT FK_E283F8D84584665A FOREIGN KEY (product_id) REFERENCES Products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Orders ADD CONSTRAINT FK_E283F8D84D16C4DD FOREIGN KEY (shop_id) REFERENCES Shops (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE Products_Shops ADD CONSTRAINT FK_CF86CE8F4584665A FOREIGN KEY (product_id) REFERENCES Products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Products_Shops ADD CONSTRAINT FK_CF86CE8F4D16C4DD FOREIGN KEY (shop_id) REFERENCES Shops (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Orders DROP FOREIGN KEY FK_E283F8D84584665A');
        $this->addSql('ALTER TABLE Orders DROP FOREIGN KEY FK_E283F8D84D16C4DD');
        $this->addSql('ALTER TABLE Products_Shops DROP FOREIGN KEY FK_CF86CE8F4584665A');
        $this->addSql('ALTER TABLE Products_Shops DROP FOREIGN KEY FK_CF86CE8F4D16C4DD');
        $this->addSql('DROP TABLE List_Orders');
        $this->addSql('DROP TABLE Orders');
        $this->addSql('DROP TABLE Products');
        $this->addSql('DROP TABLE Products_Shops');
        $this->addSql('DROP TABLE Shops');
    }
}
