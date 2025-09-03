<?php

namespace App\Models;
use CodeIgniter\Model;
class UsuarioModel extends Model
{
    protected $table            = 'usuarios';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['nome','cpf','senha','cargo'];

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
        'nome'  => 'required|min_length[3]|max_length[100]',
        'cpf'   => 'required|valid_cpf|is_unique[usuarios.cpf,id,{id}]',
        'senha' => 'required|min_length[6]',
        'cargo' => 'required|in_list[gerente,caixa]'
    ];
    protected $validationMessages   = [
        'nome' => [
        'required'    => 'O campo nome é obrigatório.',
        'min_length'  => 'O nome deve ter pelo menos 3 caracteres.',
        'max_length'  => 'O nome não pode exceder 100 caracteres.'
    ],
    'cpf' => [
        'required'    => 'O CPF é obrigatório.',
        'valid_cpf'   => 'Informe um CPF válido.',
        'is_unique'   => 'Este CPF já está cadastrado para outro usuário.'
    ],
    'senha' => [
        'required'   => 'A senha é obrigatória.',
        'min_length' => 'A senha deve ter pelo menos 6 caracteres.'
    ],
    'cargo' => [
        'required' => 'O cargo é obrigatório.',
        'in_list'  => 'O cargo deve ser um dos seguintes:gerente ou caixa.'
    ]

    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['hashPassword'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = ['hashPassword'];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected function hashPassword(array $data){
        if (isset($data['data']['senha'])) {
        $data['data']['senha'] = password_hash($data['data']['senha'], PASSWORD_DEFAULT);
    }
    return $data;
    }
}
