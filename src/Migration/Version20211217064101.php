<?php
declare(strict_types = 1);
namespace App\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211217064101 extends AbstractMigration
{
	public function getDescription(): string {
		return 'Add new station data in version 2.';
	}

	public function up(Schema $schema): void {
		$this->addSql("
			ALTER TABLE station
				ADD COLUMN odl_id_2 CHAR(7) NOT NULL DEFAULT '' AFTER odl_id,
				ADD COLUMN status_text VARCHAR(255) NOT NULL DEFAULT '' AFTER `status`
		");
		$this->addSql("
			ALTER TABLE station
				ADD COLUMN last_timestamp DATETIME DEFAULT NULL AFTER status_text,
			    ADD KEY IX_last_timestamp (`last_timestamp`)
		");
		$this->addSql("
			ALTER TABLE station
				CHANGE COLUMN last `last_value` FLOAT NOT NULL DEFAULT 0.0
		");
		$this->addSql("
			ALTER TABLE station
				ADD COLUMN unit CHAR(8) NOT NULL DEFAULT '',
				ADD COLUMN duration CHAR(10) NOT NULL DEFAULT '',
				ADD COLUMN is_validated TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
				ADD COLUMN nuclide VARCHAR(255) NOT NULL DEFAULT '' 
		");
	}

	public function down(Schema $schema): void {
		$this->addSql("
			ALTER TABLE station
				CHANGE COLUMN `last_value` last FLOAT NOT NULL
		");
		$this->addSql("
			ALTER TABLE station
			    DROP KEY IX_last_timestamp,
				DROP COLUMN odl_id_2,
				DROP COLUMN status_text,
				DROP COLUMN last_timestamp,
				DROP COLUMN unit,
				DROP COLUMN duration,
				DROP COLUMN is_validated,
				DROP COLUMN nuclide
		");
	}
}
