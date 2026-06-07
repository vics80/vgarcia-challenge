<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260607180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add maximum product stock quantity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE products ADD max_stock_quantity INT NOT NULL DEFAULT 10');
        $this->addSql("UPDATE products SET max_stock_quantity = CASE selector WHEN 'WATER' THEN 20 WHEN 'JUICE' THEN 15 WHEN 'SODA' THEN 10 ELSE 10 END");
        $this->addSql('ALTER TABLE products ALTER max_stock_quantity DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE products DROP max_stock_quantity');
    }
}
