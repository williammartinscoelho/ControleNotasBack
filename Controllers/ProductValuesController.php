<?php
namespace Controllers;

use \Core\Controller;
use \Models\ProductValues;

class ProductValuesController extends Controller {

    public function index($id = null) {
        $array = array('error'=>'');
        $method = $this->getMethod();
		$data = $this->getRequestData();

        // Busca o valor de um produto especificado pelo id
        if($method == 'GET' && !empty($id)) {
            $productValues = new ProductValues();
            $array['productValues'] = $productValues->getById($id);

            $this->returnJson($array);
        }


        // Busca todos os valores de produtos
        if ($method == 'GET' && empty($id)) {
            $productValues = new ProductValues();
            $array['productValues'] = $productValues->getAll();

            $this->returnJson($array);
        }
    }

    public function product($id) {
        $array = array('error'=>'');
        $method = $this->getMethod();
		$data = $this->getRequestData();

        // Busca o(s) valor(res) de um produto especificado pelo id do produto
        if($method == 'GET' && !empty($id)) {
            $productValues = new ProductValues();
            $array['productValues'] = $productValues->getByProductId($id);

            $this->returnJson($array);
        }

    }
}