<?php
namespace App\Controllers;
use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class ClienteController extends ResourceController
{
    use ResponseTrait;
    protected $model;

    public function __construct()
    {
        $this->model = model('App\Models\ClienteModel');
    }

    public function index()
    {
        $clientes = $this->model->findAll();
        return $this->respond($clientes, 200);
    }

     public function create(){
        $data = $this->request->getJSON(true);
        if ($this->model->save($data)) {
            return $this->respondCreated(['message' => 'Cadastro realizado com sucesso']);
        } else {
            return $this->failValidationErrors($this->model->errors());
        }
    }
    public function show($id = null)
    {
        $cliente = $this->model->find($id);
        if ($cliente) {
            return $this->respond($cliente, 200);
        } else {
            return $this->failNotFound('Cliente not found');
        }
    }
    public function delete($id = null)
    {
       $clienteEncontrado = $this->model->find($id);
        if (!$clienteEncontrado) {
            return $this->failNotFound('Cliente not found');
        }
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Cliente deleted successfully']);
        } else {
            return $this->failServerError('Failed to delete cliente');
        }
    }
}
