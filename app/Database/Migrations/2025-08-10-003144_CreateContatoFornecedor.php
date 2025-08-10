<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContatoFornecedor extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'fornecedor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'telefone' => [
                'type'       => 'VARCHAR',
                'constraint' => 15,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('fornecedor_id', 'fornecedores', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('contato_fornecedores');
    }

    public function down()
    {
        $this->forge->dropTable('contato_fornecedores');
    }
}
