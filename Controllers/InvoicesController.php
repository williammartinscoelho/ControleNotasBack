<?php
namespace Controllers;

use \Core\Controller;
use \Models\Invoices;

class InvoicesController extends Controller {

    public function index($id = null) {
        $array = array('error'=>'');
        $method = $this->getMethod();
        $data = $this->getRequestData();

        // Busca uma note especificada pelo id
        if($method == 'GET' && isset($id)) {
            $invoices = new Invoices();
            $array['invoice'] = $invoices->getById($id);
            
            $this->returnJson($array);
        }

        // Busca todas as notas
        if ($method == 'GET' && empty($id)) {
            $invoices = new Invoices();
            $array['invoices'] = $invoices->getAll();

            $this->returnJson($array);
        }
        
        // Insere nota(s)
        if ($method == 'POST' && empty($id)) {

            // if (empty($data["link"])){
            //     $array["error"] = array("message" => "Propriedade link não pode ser vazia");
            //     $this->returnJson($array);
            // }
            
            // LINK preenchido e LINK é array
            if (isset($data["link"]) && is_array($data["link"])) {
                $invoices = new Invoices();
                foreach ($data["link"] as $link) {
                    $response = $invoices->create_invoice($link, null);
                }
            }

            // LINK preenchido e LINK é string
            if (isset($data["link"]) && is_string($data["link"])) {
                $invoices = new Invoices();
                $response = $invoices->create_invoice($data["link"], null);
            }

            // NOTA preenchida
            if (isset($data["invoice"])) {
                $data["invoice"] = (array) $data["invoice"];
                $data["invoice"]["store"] = (array) $data["invoice"]["store"];
                
                foreach ($data["invoice"]["products"] as $product => $value) {
                    $data["invoice"]["products"][$product] = (array) $value;
                }

                $invoices = new Invoices();
                $response = $invoices->create_invoice(null, $data["invoice"]);
            }

            
            if (isset($response)) {
                $this->returnJson($response);
            } else {
                $array["error"] = "Sem dados para inserir!";
                $this->returnJson($array);
            }
            
            
        }

    }
}