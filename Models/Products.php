<?php
namespace Models;

use \Core\Model;
use \Models\ProductValues;

class Products extends Model {

    /*SELECIONA TODOS OS PRODUTOS*/
    public function getAll() {
        $array = array();

        $sql = "SELECT * 
                FROM tb_product";
        $sql = $this->db->query($sql);
        $sql->execute();

        if($sql->rowCount() > 0) {
            $array = $sql->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $array;
    }

    /*SELECIONA UM PRODUTO POR ID*/
    public function getById($id) {
        $array = array();

        $sql = "SELECT * 
                FROM tb_product 
                WHERE id_product = :id ;";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':id', $id);
        $sql->execute();

        if($sql->rowCount() > 0) {
            $array = $sql->fetch(\PDO::FETCH_ASSOC);
            $array['values'] = (new ProductValues())->getByProductId($id);
        }

        return $array;
    }

    public function getByIdWithValues($id) {
        $array = array();

        $sql = "SELECT * 
                FROM tb_product 
                WHERE active = 1 AND id_product = :id ;";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':id', $id);
        $sql->execute();

        if($sql->rowCount() > 0) {
            $array = $sql->fetch(\PDO::FETCH_ASSOC);
            $array['productValues'] = (new ProductValues())->getById($id);
        }

        return $array;

    }

    /*SELECIONA TODOS OS PRODUTOS DE UMA NOTA ESPECIFICADA PELO ID*/
    public function getByNoteId($idNote) {
        $array = array();

        $sql = "SELECT tb_product.*, tb_invoice_has_product.amount, tb_invoice_has_product.amount_value, tb_product_value.value
                FROM tb_product
                INNER JOIN tb_invoice_has_product ON tb_invoice_has_product.fk_id_product = tb_product.id_product
                INNER JOIN tb_invoice ON tb_invoice.id_invoice = tb_invoice_has_product.fk_id_invoice
                INNER JOIN tb_product_value ON tb_product_value.id_product_value = tb_invoice_has_product.fk_id_product_value
                WHERE tb_invoice.id_invoice = :id ;";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':id', $idNote);
        $sql->execute();

        if($sql->rowCount() > 0) {
            $array = $sql->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $array;
    }

    public function getIfExists($store_code) {
        $array = array('id_product' => null);

        $sql = "SELECT id_product
                FROM tb_product
                WHERE store_code = :store_code ;";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':store_code', $store_code);
        $sql->execute();

        if($sql->rowCount() > 0) {
            $array = $sql->fetch(\PDO::FETCH_ASSOC);
        }

        return $array['id_product'];
    }

    public function create($name, $store_code, $unity) {
        $sql = "INSERT INTO tb_product
                (name, created_at, store_code, unity)
                VALUES
                (:name, current_timestamp(), :store_code, :unity );";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':name', $name);
        $sql->bindValue(':store_code', $store_code);
        $sql->bindValue(':unity', $unity);
        $sql->execute();

        return $this->db->lastInsertId();
    }

    
}