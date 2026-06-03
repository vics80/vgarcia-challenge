<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260603174750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create initial vending machine tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE orders (id BINARY(16) NOT NULL, vending_machine_id BINARY(16) NOT NULL, product_selector VARCHAR(20) NOT NULL, total_amount_cents INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX idx_orders_vending_machine_id (vending_machine_id), INDEX idx_orders_product_selector (product_selector), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE products (id BINARY(16) NOT NULL, name VARCHAR(120) NOT NULL, selector VARCHAR(20) NOT NULL, price_cents INT NOT NULL, stock_quantity INT NOT NULL, UNIQUE INDEX uniq_products_selector (selector), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE vending_machines (id BINARY(16) NOT NULL, inserted_money JSON NOT NULL, available_change JSON NOT NULL, product_inventory JSON NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE orders');
        $this->addSql('DROP TABLE products');
        $this->addSql('DROP TABLE vending_machines');
    }
}
