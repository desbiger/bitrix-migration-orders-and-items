<?php
namespace BitrixMigration;

class ImportUsers {

    /**
     * @return array
     */
    public function getAll()
    {
        $users = [];
        $list = \CUser::getList();
        while($user = $list->fetch()){
            $users[] = $user;
        }
        return $users;
    }

}