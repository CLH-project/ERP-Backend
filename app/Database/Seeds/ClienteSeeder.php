<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ClienteSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nome'     => 'Carlos Henrique dos Santos',
                'cpf'      => '06659337709',
                'telefone' => '11987654321',
            ],
            [
                'nome'     => 'Celso Antonio dos Santos',
                'cpf'      => '03079504763',
                'telefone' => '11912345678',
            ],
        ];

        $this->db->table('clientes')->insertBatch($data);
    }
}
