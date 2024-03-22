<?php
namespace Controllers;

use \Core\Controller;
use \Models\Stores;
use \Models\Invoices;

class StoresController extends Controller {

    public function index($id = null) {
        $array = array('error'=>'');
        $method = $this->getMethod();
        $data = $this->getRequestData();

        // Busca uma loja especificada pelo id
        if($method == 'GET' && !empty($id)) {
            $stores = new Stores();
            $array['store'] = $stores->getById($id);

            $this->returnJson($array);
        }

        // Busca todas as lojas
        if ($method == 'GET' && empty($id)) {
            $stores = new Stores();
            $array['stores'] = $stores->getAll();

            $this->returnJson($array);
        } 
    }

    public function invoices($id) {
        // id = id da loja
        $array = array('error'=>'');
        $method = $this->getMethod();
        $data = $this->getRequestData();

        // Busca uma loja especificada pelo id
        if($method == 'GET' && !empty($id)) {
            $stores = new Stores();
            $array['store'] = $stores->getById($id);
            $invoices = new Invoices();
            $array['store']['invoices'] = $invoices->getByStoreId($id);

            $this->returnJson($array);
        }
    }
}