<?php
namespace Models;
require_once('./lib/simple_html_dom.php');

use \Core\Model;


class Webscrepper extends Model {

    public function get_fiscal_note_emitted_at($html) {
        $emitted_at = explode('Emissão: ', $html)[1];
        $emitted_at = substr($emitted_at, 9, 19);
        $date = explode(' ', $emitted_at)[0];
        $time = explode(' ', $emitted_at)[1];

        $date = explode('/', $date);
        $date = array_reverse($date);
        $date = implode('-', $date);

        $emitted_at = $date . ' ' . $time;

        return $emitted_at;
    }

    public function get_fiscal_note_value($html) {

            $domNodeList = $html->find('.totalNumb.txtMax');

            // se não encontrar o valor, retornar false
            if(empty($domNodeList)) return false;

            $value = trim($domNodeList[0]->innertext);
            $value = str_replace(',', '.', $value);

            return $value;

    }

    public function get_store_name($html) {
        $domNodeList = $html->find('#u20');
        $name = trim($domNodeList[0]->innertext);

        return $name;
    }

    public function get_store_cnpj($html) {
        $domNodeList = $html->find('.text');
        $cnpj = trim($domNodeList[0]->innertext);
        $cnpj = str_replace("CNPJ:", "", $cnpj);
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        $cnpj = trim($cnpj);

        return $cnpj;
    }

    public function get_store_address($html) {
        $domNodeList = $html->find('.text');
        $address = trim($domNodeList[1]->innertext);

        return $address;
    }

    public function get_products($html) {
        $domNodeList = $html->find('#tabResult tr');

        $products = array();

        foreach ($domNodeList as $domNode) {
            $product = array();
            
            $name= $domNode->getElementsByTagName('span')[0]->innertext;
            $product['name'] = trim($name);

            $store_code = $domNode->getElementsByTagName('span')[1]->innertext;
            $store_code = preg_replace('/[^0-9]/', '', $store_code);
            $product['store_code'] = $store_code;

            $amount = $domNode->getElementsByTagName('span')[2]->innertext;
            $amount = preg_replace('/[^0-9,]/','', $amount); 
            $amount = str_replace(',', '.', $amount);
            $product['amount'] = $amount;

            $unity = $domNode->getElementsByTagName('span')[3]->innertext;
            $unity = str_replace('<strong>UN: </strong>','', $unity);
            $product['unity'] = trim($unity);

            $price = $domNode->getElementsByTagName('span')[4]->innertext;
            $price = preg_replace('/[^0-9,]/','', $price); 
            $price = str_replace(',', '.', $price);
            $product['price'] = $price;
            
            $amountValue = $domNode->getElementsByTagName('span')[5]->innertext;
            $amountValue = preg_replace('/[^0-9,]/','', $amountValue); 
            $amountValue = str_replace(',', '.', $amountValue);
            $product['amountValue'] = $amountValue;

            $products[] = $product;
        }

        return $products;
    }   


    
    public function screpper($link) {
            $data = array();
            $html = file_get_html($link);

            $data['value'] = $this->get_fiscal_note_value($html);
            // verifica se encontrou o valor
            // caso não tenha encontrado, o link é invalido
            // retornar false
            if ($data['value'] == false) {
                $data['isValid'] = false;
                return $data;
            }

            $data['emitted_at'] = $this->get_fiscal_note_emitted_at($html);
            $data['store']['name'] = $this->get_store_name($html);
            $data['store']['cnpj'] = $this->get_store_cnpj($html);
            $data['store']['address'] = $this->get_store_address($html);
            $data['products'] = $this->get_products($html);
            $data['link'] = $link;
            $data['isValid'] = true;
            
            return $data;
    }
}


