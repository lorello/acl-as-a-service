<?php

namespace App\Services;

class ListsService extends BaseService
{

    public function getAll()
    {
        return $this->db->fetchAll("SELECT * FROM lists");
    }

    function save($list)
    {
        $this->db->insert("lists", $list);
        return $this->db->lastInsertId();
    }

    function update($id, $list)
    {
        return $this->db->update('lists', $list, ['id' => $id]);
    }

    function delete($id)
    {
        return $this->db->delete("lists", array("id" => $id));
    }

}
