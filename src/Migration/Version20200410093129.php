<?php
declare(strict_types = 1);
namespace App\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200410093129 extends AbstractMigration
{
	public function getDescription(): string {
		return 'Create the gammascout table.';
	}

	public function up(Schema $schema): void {
		$this->addSql("
			CREATE TABLE gammascout (
				time DATETIME NOT NULL,
				dosage float NOT NULL DEFAULT 0.0,
				PRIMARY KEY (time)
			) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB"
		);
	}

	public function down(Schema $schema): void {
		$this->addSql('DROP TABLE gammascout');
	}
}
