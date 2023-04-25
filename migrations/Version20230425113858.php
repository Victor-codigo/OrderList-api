<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230425113858 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Generates tables Orders, Products, Shops and Products_shops';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE Orders (id CHAR(36) NOT NULL, product_id CHAR(36) NOT NULL, user_id CHAR(36) NOT NULL, group_id CHAR(36) NOT NULL, amount DECIMAL(10,3) DEFAULT NULL, description VARCHAR(500) DEFAULT NULL, created_on DATETIME NOT NULL, bought_on DATETIME DEFAULT NULL, date_to_buy DATETIME DEFAULT NULL, INDEX IDX_user_id (user_id), INDEX IDX_group_id (group_id), INDEX IDX_product_id (product_id), UNIQUE INDEX u_order_id (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Products (id CHAR(36) NOT NULL, name VARCHAR(50) NOT NULL, price DECIMAL(10,2) NOT NULL, description VARCHAR(500) DEFAULT NULL, image VARCHAR(256) DEFAULT NULL, created_on DATETIME NOT NULL, UNIQUE INDEX UNIQ_4ACC380C5E237E06 (name), UNIQUE INDEX UNIQ_4ACC380CCAC822D9 (price), INDEX IDX_product_name (name), UNIQUE INDEX u_products_id (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Products_shops (product_id CHAR(36) NOT NULL, shop_id CHAR(36) NOT NULL, INDEX IDX_E47E18B4584665A (product_id), INDEX IDX_E47E18B4D16C4DD (shop_id), PRIMARY KEY(product_id, shop_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Shops (id CHAR(36) NOT NULL, group_id CHAR(36) NOT NULL, name VARCHAR(50) NOT NULL, description VARCHAR(500) DEFAULT NULL, created_on DATETIME NOT NULL, INDEX idx_shop_id (id), INDEX idx_shop_group_id (group_id), UNIQUE INDEX u_shops_id (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Orders ADD CONSTRAINT FK_E283F8D84584665A FOREIGN KEY (product_id) REFERENCES Products (id)');
        $this->addSql('ALTER TABLE Products_shops ADD CONSTRAINT FK_E47E18B4584665A FOREIGN KEY (product_id) REFERENCES Products (id)');
        $this->addSql('ALTER TABLE Products_shops ADD CONSTRAINT FK_E47E18B4D16C4DD FOREIGN KEY (shop_id) REFERENCES Shops (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Orders DROP FOREIGN KEY FK_E283F8D84584665A');
        $this->addSql('ALTER TABLE Products_shops DROP FOREIGN KEY FK_E47E18B4584665A');
        $this->addSql('ALTER TABLE Products_shops DROP FOREIGN KEY FK_E47E18B4D16C4DD');
        $this->addSql('DROP TABLE Orders');
        $this->addSql('DROP TABLE Products');
        $this->addSql('DROP TABLE Products_shops');
        $this->addSql('DROP TABLE Shops');
    }
}
