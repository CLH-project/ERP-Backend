<?php

namespace App\Models;

use CodeIgniter\Model;

class VendaModel extends Model
{
    protected $table            = 'vendas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['usuario_id','cliente_id','total_venda','created_at','updated_at'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'usuario_id'   => 'required|integer',
        'cliente_id'   => 'required|integer',
        'total_venda'  => 'required|numeric|greater_than_equal_to[0]',
    ];
    protected $validationMessages   = [
        'usuario_id' => [
            'required' => 'O campo usuario_id é obrigatório.',
            'integer'  => 'O campo usuario_id deve ser um número inteiro.',
        ],
        'cliente_id' => [
            'required' => 'O campo cliente_id é obrigatório.',
            'integer'  => 'O campo cliente_id deve ser um número inteiro.',
        ],
        'total_venda' => [
            'required'               => 'O campo total_venda é obrigatório.',
            'numeric'                => 'O campo total_venda deve ser um número.',
            'greater_than_equal_to'  => 'O campo total_venda deve ser maior ou igual a zero.',
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
