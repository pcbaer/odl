<?php
declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200402170116 extends AbstractMigration {

	/**
	 * @return string
	 */
	public function getDescription(): string {
		return 'Create the station table.';
	}

	/**
	 * @param Schema $schema
	 * @throws DBALException
	 */
	public function up(Schema $schema): void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql("
			CREATE TABLE station (
				id SMALLINT AUTO_INCREMENT NOT NULL,
				odl_id CHAR(9) NOT NULL,
				zip CHAR(5) NOT NULL,
				city VARCHAR(255) NOT NULL,
				kid SMALLINT NOT NULL,
				created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				altitude SMALLINT NOT NULL,
				latitude FLOAT NOT NULL,
				longitude FLOAT NOT NULL,
				status SMALLINT NOT NULL,
				last FLOAT NOT NULL,
				PRIMARY KEY (id),
				UNIQUE KEY UQ_station_odl_id (odl_id),
				KEY IX_station_zip (zip),
				KEY IX_station_city (city)
			) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB"
		);
	}

	/**
	 * @param Schema $schema
	 * @throws DBALException
	 */
	public function down(Schema $schema): void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql('DROP TABLE station');
	}
}
