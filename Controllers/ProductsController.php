<?php
namespace Controllers;

use \Core\Controller;
use \Models\Products;

class ProductsController extends Controller {

    public function index($id = null) {
        $array = array('error'=>'');
        $method = $this->getMethod();
		$data = $this->getRequestData();
        
        // Busca um produto especificado pelo id
        if($method == 'GET' && !empty($id)) {
            $products = new Products();
            $array['product'] = $products->getById($id);

            $this->returnJson($array);
        }
        
        // Busca todos os produtos
        if ($method == 'GET' && empty($id)) {
            $products = new Products();
            $array['products'] = $products->getAll();

            $this->returnJson($array);
        }

        if ($method == 'POST' && empty($id)) {
            
            $this->returnJson($array);
        }
    }


    public function values($id) {
        $array = array('error'=>'');
        $method = $this->getMethod();
		$data = $this->getRequestData();
        
        // Busca um produto especificado pelo id com o historico de valores
        if ($method == 'GET' && !empty($id)) {
            $products = new Products();
            $array['product'] = $products->getByIdWithValues($id);
            $this->returnJson($array);
        }
    }
}