<?php
declare(strict_types = 1);
namespace App\Migration\planned;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211217071658 extends AbstractMigration
{
	public function getDescription(): string {
		return 'Add unique index for new station ID 2.';
	}

	public function up(Schema $schema): void {
		$this->addSql("ALTER TABLE station ADD UNIQUE KEY UQ_station_odl_id_2 (odl_id_2)");
	}

	public function down(Schema $schema): void {
		$this->addSql("ALTER TABLE station DROP KEY UQ_station_odl_id_2");
	}
}
