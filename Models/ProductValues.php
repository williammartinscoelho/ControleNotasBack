<?php
namespace Models;

use \Core\Model;

class ProductValues extends Model {

    /*SELECIONA TODOS OS VALORES*/
    public function getAll() {
        $array = array();

        $sql = "SELECT * 
                FROM tb_product_value;";
        $sql = $this->db->query($sql);
        $sql->execute();

        if($sql->rowCount() > 0) {
            $array = $sql->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $array;
    }


    /*SELECIONA O VALOR POR ID*/
    public function getById($id) {
        $array = array();

        $sql = "SELECT * 
                FROM tb_product_value 
                WHERE id_product_value = :id ;";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':id', $id);
        $sql->execute();

        if($sql->rowCount() > 0) {
            $array = $sql->fetch(\PDO::FETCH_ASSOC);
        }

        return $array;
    }

    /*SELECIONA O VALOR POR ID DO PRODUTO*/
    public function getByProductId($id) {
        $array = array();

        $sql = "SELECT * 
                FROM tb_product_value 
                WHERE fk_id_product = :id
                ORDER BY emitted_at ASC;";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':id', $id);
        $sql->execute();

        if($sql->rowCount() > 0) {
            $array = $sql->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $array;
    }

    /*VERIFICAR SE O VALOR INSERIDO É O MESMO JÁ INSERIDO*/
    public function getIfExists($emitted_at, $fk_id_product) {
        $array = array('id_product_value' => null);

        $sql = "SELECT id_product_value
                FROM tb_product_value
                WHERE emitted_at = :emitted_at 
                AND fk_id_product = :fk_id_product;";
        $sql = $this->db->prepare($sql);
        // $sql->bindValue(':value', $value);
        $sql->bindValue(':emitted_at', $emitted_at);
        $sql->bindValue(':fk_id_product', $fk_id_product);
        $sql->execute();

        if($sql->rowCount() > 0) {
            $array = $sql->fetch(\PDO::FETCH_ASSOC);
        }

        return $array['id_product_value'];
    }

    /*CRIA UM NOVO HISTÓRICO DE VALOR*/
    public function create($value, $emitted_at, $fk_id_product) {
        $sql = "INSERT INTO tb_product_value
                (value, created_at, emitted_at, fk_id_product)
                VALUES
                (:value, current_timestamp(), :emitted_at, :fk_id_product) ;";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':value', $value);
        $sql->bindValue(':emitted_at', $emitted_at);
        $sql->bindValue(':fk_id_product', $fk_id_product);
        $sql->execute();

        return $this->db->lastInsertId();
    }


}