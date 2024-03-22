<?php
namespace Models;

use \Core\Model;

class Keys extends Model {

    public function getALl() {
        $array = array();

        $sql = "SELECT * 
                FROM tb_key;";
        $sql = $this->db->query($sql);
        $sql->execute();

        if($sql->rowCount() > 0) {
            $array = $sql->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $array;
    }

    public function create($key, $link) {
        $sql = "INSERT INTO tb_key
                (`key`, `link`, created_at)
                VALUES
                (:key, :link, current_timestamp() );";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':key', $key);
        $sql->bindValue(':link', $link);
        $sql->execute();

        return $this->db->lastInsertId();
    }

    public function delete($key) {
        echo('//////////////');
        
        echo('//////////////');

        $sql = "DELETE FROM tb_key WHERE tb_key.key = :key ;";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':key', $key);
        $sql->execute();

        return '';
    }
}