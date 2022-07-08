<?php
declare(strict_types = 1);
namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220708202919 extends AbstractMigration
{
    public function getDescription(): string {
		return 'Migrate migrations to new namespace.';
    }

    public function up(Schema $schema): void {
		$this->addSql('UPDATE migration SET version = REPLACE(version, "App\\\\Migration", "DoctrineMigrations")');
    }

    public function down(Schema $schema): void {
		$this->addSql('UPDATE migration SET version = REPLACE(version, "DoctrineMigrations", "App\\\\Migration")');
    }
}
