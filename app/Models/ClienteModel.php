<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table            = 'clientes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true; 
    protected $protectFields    = true;
    protected $allowedFields    = ['nome', 'cpf', 'telefone'];

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
        'nome'      => 'required|min_length[5]|max_length[100]',
        'cpf'       => 'required|valid_cpf|is_unique[clientes.cpf,id,{id}]',
        'telefone'  => 'required|is_unique[clientes.telefone,id,{id}]',
    ];
    protected $validationMessages   = [
        'nome'      => [
            'required'    => 'O nome é obrigatório.',
            'min_length'  => 'O nome deve ter pelo menos 5 caracteres.',
            'max_length'  => 'O nome não pode exceder 100 caracteres.',
        ],
        'cpf'       => [
            'required'      => 'O CPF é obrigatório.',
            'valid_cpf'     => 'O CPF informado é inválido.',
            'is_unique'     => 'Já existe um cliente cadastrado com este CPF.',
        ],
        'telefone'  => [
            'required'    => 'O telefone é obrigatório.',
            'is_unique'   => 'Já existe um cliente cadastrado com este telefone.',
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
