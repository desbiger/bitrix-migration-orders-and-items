<?php


namespace BitrixMigration\Export;


class ExportUsers {

    /**
     * ExportUsers constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return array
     */
    public function getAllUsers()
    {
        $res = [];
        $list = \CUser::getList();
        while ($user = $list->Fetch()) {
            $res[] = $user;
        }

        return $res;
    }

}