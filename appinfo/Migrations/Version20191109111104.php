<?php
namespace OCA\brute_force_protection\Migrations;

use Doctrine\DBAL\Schema\Schema;
use OCP\Migration\ISchemaMigration;

class Version20191109111104 implements ISchemaMigration {

	/** @var  string */
	private $prefix;

	public function changeSchema(Schema $schema, array $options) {
		$this->prefix = $options['tablePrefix'];
		if (!$schema->hasTable("{$this->prefix}bfp_link_accesses")) {
			$table = $schema->createTable("{$this->prefix}bfp_link_accesses");
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'unsigned' => true,
				'notnull' => true,
				'length' => 11,
			]);
			$table->addColumn('ip', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('link_token', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('attempted_at', 'integer', [
				'notnull' => true,
			]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['ip'], 'bfp_link_accesses_ip');
			$table->addIndex(['attempted_at'], 'bfp_link_accesses_at');
		}
	}
}
