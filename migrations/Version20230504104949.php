<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230504104949 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates tables Orders, Products, Products_shops and Shops';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE Orders (id CHAR(36) NOT NULL, product_id CHAR(36) NOT NULL, user_id CHAR(36) NOT NULL, group_id CHAR(36) NOT NULL, amount DECIMAL(10,3) DEFAULT NULL, description VARCHAR(500) DEFAULT NULL, created_on DATETIME NOT NULL, bought_on DATETIME DEFAULT NULL, date_to_buy DATETIME DEFAULT NULL, INDEX IDX_user_id (user_id), INDEX IDX_group_id (group_id), INDEX IDX_product_id (product_id), UNIQUE INDEX u_order_id (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Products (id CHAR(36) NOT NULL, group_id CHAR(36) NOT NULL, name VARCHAR(50) NOT NULL, description VARCHAR(500) DEFAULT NULL, image VARCHAR(256) DEFAULT NULL, created_on DATETIME NOT NULL, INDEX IDX_group_id (group_id), INDEX IDX_product_name (name), UNIQUE INDEX u_products_id (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Products_Shops (id INT AUTO_INCREMENT NOT NULL, product_id CHAR(36) NOT NULL, shop_id CHAR(36) NOT NULL, price DECIMAL(10,2) DEFAULT NULL, INDEX IDX_product_id (product_id), INDEX IDX_shop_id (shop_id), UNIQUE INDEX u_id (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Shops (id CHAR(36) NOT NULL, group_id CHAR(36) NOT NULL, name VARCHAR(50) NOT NULL, image VARCHAR(250) DEFAULT NULL, description VARCHAR(500) DEFAULT NULL, created_on DATETIME NOT NULL, INDEX idx_shop_group_id (group_id), INDEX idx_shop_name (name), UNIQUE INDEX u_shops_id (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Orders ADD CONSTRAINT FK_E283F8D84584665A FOREIGN KEY (product_id) REFERENCES Products (id)');
        $this->addSql('ALTER TABLE Products_Shops ADD CONSTRAINT FK_CF86CE8F4584665A FOREIGN KEY (product_id) REFERENCES Products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Products_Shops ADD CONSTRAINT FK_CF86CE8F4D16C4DD FOREIGN KEY (shop_id) REFERENCES Shops (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Orders DROP FOREIGN KEY FK_E283F8D84584665A');
        $this->addSql('ALTER TABLE Products_Shops DROP FOREIGN KEY FK_CF86CE8F4584665A');
        $this->addSql('ALTER TABLE Products_Shops DROP FOREIGN KEY FK_CF86CE8F4D16C4DD');
        $this->addSql('DROP TABLE Orders');
        $this->addSql('DROP TABLE Products');
        $this->addSql('DROP TABLE Products_Shops');
        $this->addSql('DROP TABLE Shops');
    }
}
