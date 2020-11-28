<?php
declare(strict_types = 1);

namespace App\Migrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200410093129 extends AbstractMigration {

	/**
	 * @return string
	 */
	public function getDescription(): string {
		return 'Create the gammascout table.';
	}

	/**
	 * @param Schema $schema
	 * @throws DBALException
	 */
	public function up(Schema $schema): void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql("
			CREATE TABLE gammascout (
				time DATETIME NOT NULL,
				dosage float NOT NULL DEFAULT 0.0,
				PRIMARY KEY (time)
			) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB"
		);
	}

	/**
	 * @param Schema $schema
	 * @throws DBALException
	 */
	public function down(Schema $schema): void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql('DROP TABLE gammascout');
	}
}
