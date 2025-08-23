<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\FornecedorModel;
use App\Models\FornecedorContatoModel;

class FornecedorController extends BaseController
{
    public function create()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $modelFornecedor = new FornecedorModel();
        $modelContato = new FornecedorContatoModel();

        $data = $this->request->getJSON(true);

        $fornecedorData = [
            'nome' => $data['nome'] ?? null,
            'cnpj' => $data['cnpj'] ?? null,
        ];

        $contatoData = [
            'email'    => $data['contato']['email'] ?? null,
            'telefone' => $data['contato']['telefone'] ?? null,
        ];

        if (!$modelFornecedor->insert($fornecedorData)) {
            $db->transRollback();
            return $this->response->setJSON([
                'error' => 'Erro ao salvar fornecedor.',
                'validation_errors' => $modelFornecedor->errors()
            ]);
        }

        $contatoData['fornecedor_id'] = $modelFornecedor->getInsertID();

        if (!$modelContato->insert($contatoData)) {
            $db->transRollback();
            return $this->response->setJSON([
                'error' => 'Erro ao salvar contato do fornecedor.',
                'validation_errors' => $modelContato->errors()
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'error' => 'Erro na transação. Nenhum dado foi salvo.'
            ]);
        }

        return $this->response->setJSON([
            'message' => 'Fornecedor e contato salvos com sucesso!',
            'fornecedor_id' => $contatoData['fornecedor_id']
        ]);
    }
}