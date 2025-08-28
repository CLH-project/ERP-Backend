<?php

namespace App\Models;

use CodeIgniter\Model;

class ProdutoModel extends Model
{
    protected $table            = 'produtos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['nome','marca','valor_unico','estoque','categoria'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'valor_unico' => 'float',
        'estoque' => 'integer'
    ];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'nome'=> 'required|min_length[3]',
        'marca'=> 'required',
        'valor_unico' => 'required|decimal',
        'estoque'=>'required|integer',
        'categorias'=>'required'
    ];
    protected $validationMessages   = [
        'nome' => [
        'required'     => 'O campo Nome é obrigatório.',
        'min_length'   => 'O Nome deve ter pelo menos 3 caracteres.'
    ],
    'marca' => [
        'required'     => 'Informe a marca do produto.'
    ],
    'valor_unico' => [
        'required'     => 'O valor do produto é obrigatório.',
        'decimal'      => 'O valor deve estar no formato decimal, como 99.99.'
    ],
    'estoque' => [
        'required'     => 'Informe a quantidade em estoque.',
        'integer'      => 'O estoque deve ser um número inteiro.'
    ],
    'categorias' => [
        'required'     => 'Selecione uma categoria para o produto.'
    ]

    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
