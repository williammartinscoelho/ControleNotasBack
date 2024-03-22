<?php
namespace Models;

use \Core\Model;

class Stores extends Model {

    

    public function getAll() {
        $array = array();

        $sql = "SELECT * 
                FROM tb_store 
                WHERE active = 1;";
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
                FROM tb_store
                WHERE active = 1 AND id_store = :id ;";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':id', $id);
        $sql->execute();

        if($sql->rowCount() > 0) {
            $array = $sql->fetch(\PDO::FETCH_ASSOC);
        }

        return $array;
    }

    public function getIfExists($name, $cnpj, $address) {
        $array = array('id_store' => null);

        $sql = "SELECT id_store
                FROM tb_store
                WHERE name = :name OR CNPJ = :cnpj OR address = :address ;";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':name', $name);
        $sql->bindValue(':cnpj', $cnpj);
        $sql->bindValue(':address', $address);
        $sql->execute();

        if($sql->rowCount() > 0) {
            $array = $sql->fetch(\PDO::FETCH_ASSOC);
        }

        return $array['id_store'];
    }


    public function create($name, $cnpj, $address) {
        $sql = "INSERT INTO tb_store
            (name, CNPJ, address, created_at)
            VALUES
            (:name , :cnpj , :address , current_timestamp());";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':name', $name);
        $sql->bindValue(':cnpj', $cnpj);
        $sql->bindValue(':address', $address);
        $sql->execute();

        return $this->db->lastInsertId();
    }
    
}