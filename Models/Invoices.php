<?php
namespace Models;

use \Core\Model;

require_once('./lib/simple_html_dom.php');

class Invoices extends Model {

    public function getAll() {
        $array = array();

        $sql = "SELECT * 
                FROM tb_invoice
                ORDER BY emitted_at DESC;";
        $sql = $this->db->query($sql);
        $sql->execute();

        if($sql->rowCount() > 0) {
            $array = $sql->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $array;
    }

    public function getById($id) {
        $array = array();

        $sql = "SELECT *
                FROM tb_invoice
                WHERE id_invoice = :id ;";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':id', $id);
        $sql->execute();

        if($sql->rowCount() > 0) {
            $array = $sql->fetch(\PDO::FETCH_ASSOC);
            $array['store'] = (new Stores())->getById($array['fk_id_store']);
            $array['products'] = (new Products())->getByNoteId($id);
        }
        
        return $array;
    }

    public function getByStoreId($id) {
        $array = array();

        $sql = "SELECT *
                FROM tb_invoice
                WHERE fk_id_store = :id
                ORDER BY emitted_at DESC;";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':id', $id);
        $sql->execute();

        if($sql->rowCount() > 0) {
            $array = $sql->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $array;
    }

    public function create_invoice($link, $invoice) {
        $response = array();
        $response["error"] = "";
        $response["warning"] = array();
        $response["idInvoice"] = "";

        $webScraperData = array();
        
        // verifica se foi enviado um link
        if (isset($link)) {
            // verificar se uma nota existe, por um link
            // se existir, pegar o id da nota
            $idInvoice = (new Invoices())->getIfExists(
                $link
            );

            // se já existir nota fiscal cadastrada, retornar erro
            if ($idInvoice) {
                $response["error"] = "Nota fiscal já existe";
                return $response;
            }

            $link = str_replace("consulta", "qrcode", $link);
            $webScraperData = (new Webscrepper())->screpper($link);
            $webScraperData["input_type"] = "qrcode";
        } else 
        // verifica se foi enviado uma nota em formato de json
        if (isset($invoice)){
            // verificar se uma nota existe, por um link
            // se existir, pegar o id da nota
            $idInvoice = (new Invoices())->getIfExists(
                $invoice["link"]
            );

            // se existir nota fiscal, retornar erro
            if ($idInvoice) {
                $response["error"] = "Nota fiscal já existe";
                return $response;
            }
            
            $webScraperData = $invoice;
            $webScraperData["input_type"] = "json";
            $webScraperData["isValid"] = true;
        }

        // caso tenho retornado false, o link é inválido
        if ($webScraperData['isValid'] === false) {
            $response["error"] = "Link inválido - Chave salva para cadastro manual!";
            $key = explode("p=", $link);
            $key = substr($key[1], 0, 44);
            (new Keys())->create($key, $link);

            return $response;
        }

        // verificar se store existe, pelo nome, ou pelo CNPJ ou endereço
        // se existir, pegar o id do store
        $idStore = (new Stores())->getIfExists(
            $webScraperData['store']['name'], 
            $webScraperData['store']['cnpj'], 
            $webScraperData['store']['address']
        );

        // se store não existir, criar store
        if (!$idStore) {
            $idStore = (new Stores())->create(
                $webScraperData['store']['name'], 
                $webScraperData['store']['cnpj'], 
                $webScraperData['store']['address']
            );
        }

        // se não existir nota fiscal, criar nota
        if (!$idInvoice) {
            $idInvoice = (new Invoices())->create(
                $webScraperData['link'], 
                $webScraperData['emitted_at'], 
                $webScraperData['value'], 
                $webScraperData["input_type"],
                $comments = 'Comentários....',
                $fk_id_store = $idStore
            );
        }

        //foreach nos produtos
        foreach ($webScraperData['products'] as $product) {
            // verificar se produto existe, pelo código
            // se existir, pegar o id do produto
            $idProduct = (new Products())->getIfExists($product['store_code']);

            // se não existir, criar produto
            if (!$idProduct) {
                $idProduct = (new Products())->create(
                    $product['name'],
                    $product['store_code'],
                    $product['unity']
                );
            } else {
                $response["warning"][] = "Produto com id {$idProduct} já existe";
            }
            
            
            // Verificar se o valor é da mesma nota
            $idProductValue = (new ProductValues())->getIfExists(
                $webScraperData['emitted_at'],
                $idProduct
            );
            

            // criar novo valor
            if (!$idProductValue) {
                $idProductValue = (new ProductValues())->create(
                    $product['price'],
                    $webScraperData['emitted_at'],
                    $idProduct
                );
            }
            

            // criar a conexão de produto e nota
            $this->connectProductWithNote(
                $idInvoice,
                $idProduct,
                $idProductValue,
                $product['amount'],
                $product['amountValue']
            );
            
        }

        if($webScraperData["input_type"] == "json") {
            (new Keys())->delete($webScraperData['key']);
        }

        $response["idInvoice"] = $idInvoice;
        return $response;
    }
    


    public function getIfExists($link) {
        $array = array('id_invoice' => null);

        $sql = "SELECT id_invoice
                FROM tb_invoice
                WHERE link = :link ;";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':link', $link);
        $sql->execute();

        if($sql->rowCount() > 0) {
            $array = $sql->fetch(\PDO::FETCH_ASSOC);
        }

        return $array['id_invoice'];
    }

    //INSERE UMA NOVA NOTA FISCAL
    public function create(
        $link, 
        $emittedAt, 
        $value, 
        $input_type,
        $comments,
        $fk_id_store
    ) {
        $sql = "INSERT INTO tb_invoice
                (
                    created_at, 
                    link, 
                    emitted_at, 
                    value, 
                    input_type, 
                    comments, 
                    fk_id_store
                )
                VALUES
                (
                    current_timestamp(), 
                    :link, 
                    :emitted_at, 
                    :value, 
                    :input_type, 
                    :comments,
                    :fk_id_store
                );";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':link', $link);
        $sql->bindValue(':emitted_at', $emittedAt);
        $sql->bindValue(':value', $value);
        $sql->bindValue(':input_type', $input_type);
        $sql->bindValue(':comments', $comments);
        $sql->bindValue(':fk_id_store', $fk_id_store);
        $sql->execute();

        return $this->db->lastInsertId();
    }


    //INSERE UMA NOVA ASSOCIAÇÃO DE PRODUTOS COM A NOTA FISCAL
    public function connectProductWithNote(
        $fk_id_invoice, 
        $fk_id_product,
        $fk_id_product_value,
        $amount,
        $amount_value
    ) {
        $sql = "INSERT INTO tb_invoice_has_product
                (
                    fk_id_invoice, 
                    fk_id_product, 
                    fk_id_product_value, 
                    amount, 
                    amount_value
                )
                VALUES
                (
                    :fk_id_invoice,
                    :fk_id_product,
                    :fk_id_product_value,
                    :amount,
                    :amount_value
                );";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':fk_id_invoice', $fk_id_invoice);
        $sql->bindValue(':fk_id_product', $fk_id_product);
        $sql->bindValue(':fk_id_product_value', $fk_id_product_value);
        $sql->bindValue(':amount', $amount);
        $sql->bindValue(':amount_value', $amount_value);
        $sql->execute();

        return $this->db->lastInsertId();
    }

    //VERIFICA SE EXISTE UMA ASSOCIAÇÃO DE PRODUTOS COM A NOTA FISCAL
    public function getIfExistsConnection($fk_id_invoice, $fk_id_product) {
        $array = array('id_invoice_has_product' => null);

        $sql = "SELECT id_invoice_has_product
                FROM tb_invoice_has_product
                WHERE fk_id_invoice = :fk_id_invoice 
                AND fk_id_product = :fk_id_product ;";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':fk_id_invoice', $fk_id_invoice);
        $sql->bindValue(':fk_id_product', $fk_id_product);
        $sql->execute();

        if($sql->rowCount() > 0) {
            $array = $sql->fetch(\PDO::FETCH_ASSOC);
        }

        return $array['id_invoice_has_product'];
    }
}