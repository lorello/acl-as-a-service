<?php

namespace App\Services;

class ListsService extends BaseService
{
    public function getAll()
    {
        return $this->db->fetchAll('SELECT * FROM lists');
    }

    public function save($list)
    {
        $this->db->insert('lists', $list);

        return $this->db->lastInsertId();
    }

    public function update($id, $list)
    {
        return $this->db->update('lists', $list, ['id' => $id]);
    }

    public function delete($id)
    {
        return $this->db->delete('lists', ['id' => $id]);
    }
}
