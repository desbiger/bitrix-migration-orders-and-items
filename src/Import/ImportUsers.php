<?php

namespace BitrixMigration\Import;

class ImportUsers {

    public $list;
    public $ids;

    /**
     * @param $users
     *
     * @return ImportUsers
     */
    static function init($users)
    {
        return new self($users);
    }

    /**
     * ImportUsers constructor.
     *
     * @param $list
     */
    public function __construct($list)
    {
        $this->list = $list;
    }

    /**
     * @return $this
     */
    public function import()
    {
        $userObject = new \CUser();
        foreach ($this->list as $user) {
            $newUser = $userObject->Add($user);
            if (!$newUser) {
                $filter = ['EMAIL' => $user['EMAIL']];
                $order = ['sort'=>'asc'];
                $tmp = 'sort';
                $newUser = \CUser::GetList($order,$tmp, $filter)->Fetch()['ID'];
            }
            $this->ids[$user['ID']] = $newUser;
        }

        return $this;

    }


}