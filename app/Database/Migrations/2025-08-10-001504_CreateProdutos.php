<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProdutos extends Migration
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
            'nome' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'unique'     => true,
            ],
            'marca' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'valor_unico' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'estoque' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'categoria' => [
                'type'       => 'ENUM',
                'constraint' => ['Alcolico', 'Não Alcolico'],
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
        $this->forge->createTable('produtos');
    }

    public function down()
    {
         $this->forge->dropTable('produtos');
    }
}
