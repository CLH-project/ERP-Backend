<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemVendaModel extends Model
{
    protected $table            = 'itemvenda';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['venda_id','produto_id','quantidade','preco_unitario'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'venda_id' => 'required|integer',
        'produto_id' => 'required|integer',
        'quantidade' => 'required|integer',
        'preco_unitario' => 'required|decimal',
    ];
    protected $validationMessages   = [
        'venda_id' => [
            'required' => 'O campo venda_id é obrigatório.',
            'integer'  => 'O campo venda_id deve ser um número inteiro.',
        ],
        'produto_id' => [
            'required' => 'O campo produto_id é obrigatório.',
            'integer'  => 'O campo produto_id deve ser um número inteiro.',
        ],
        'quantidade' => [
            'required' => 'O campo quantidade é obrigatório.',
            'integer'  => 'O campo quantidade deve ser um número inteiro.',
        ],
        'preco_unitario' => [
            'required' => 'O campo preco_unitario é obrigatório.',
            'decimal'  => 'O campo preco_unitario deve ser um número decimal.',
        ],
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
