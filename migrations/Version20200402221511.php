<?php
declare(strict_types = 1);
namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200402221511 extends AbstractMigration
{
	public function getDescription(): string {
		return 'Create the measurement table.';
	}

	public function up(Schema $schema): void {
		$this->addSql("
			CREATE TABLE measurement (
				station_id SMALLINT NOT NULL,
				time DATETIME NOT NULL,
				dosage float NOT NULL DEFAULT 0.0,
				rain float NOT NULL DEFAULT 0.0,
				abnormality float NOT NULL DEFAULT 0.0,
				PRIMARY KEY (station_id, time),
				FOREIGN KEY (station_id) REFERENCES station (id) ON DELETE CASCADE ON UPDATE CASCADE,
				INDEX IX_measurement_time (time)
			) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB"
		);
	}

	public function down(Schema $schema): void {
		$this->addSql('DROP TABLE measurement');
	}
}
