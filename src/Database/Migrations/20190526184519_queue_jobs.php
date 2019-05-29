<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_queue_jobs extends Migration
{
	public function up()
	{

        $this->forge->addField([
			'id' => [
				'type' => 'int',
				'constraint' => 11,
				'auto_increment' => true,
			],
			'name' => [
				'type' => 'varchar',
				'constraint' => 255,
				'null' => false,
			],
			'method_name' => [
				'type' => 'varchar',
				'constraint' => 255,
				'null' => true,
				'default' => null
			],
			'data' => [
				'type' => 'text',
				'null' => true,
			],
			'priority'		=> [
				'type' => 'tinyint',
				'constraint' => 4,
				'null' => false,
			],
			'unique_id'		=> [
				'type' => 'varchar',
				'constraint' => 32,
				'null' => true,
				'default' => null
			],
			'created_at'	=> [
				'type' => 'datetime',
				'null' => false
			],
			'is_taken'		=> [
				'type' => 'tinyint',
				'constraint' => 1,
				'null' => false,
				'default' => 0
			],
			'error'			=> [
				'type' => 'tinyint',
				'constraint' => 1,
				'null' => true,
				'default' => null
			],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('queue_jobs', true);
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('queue_jobs', true);
	}
}
