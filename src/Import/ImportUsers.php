<?php

namespace BitrixMigration\Import;

use BitrixMigration\CLI;

class ImportUsers {

    public $list;
    public $ids;
    public $newIDs;

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
        $i = 0;
        $count = count($this->list);
        foreach ($this->list as $user) {
            $this->newIDs[$user['ID']] = $this->createUserIfNotExists($user);
            CLI::show_status($i++,$count,30,' | import users');
        }

        return $this;

    }

    /**
     * @param $user
     *
     * @return mixed
     */
    private function createUserIfNotExists($user)
    {
        if (!$id = $this->userExists($user)) {

            return $this->createUser($user);
        }

        return $id;
    }

    /**
     * @param $user
     *
     * @return mixed
     */
    private function createUser($user)
    {
        $userObject = new \CUser();

        return $userObject->add($user);
    }

    /**
     * @param $user
     *
     * @return mixed
     */
    private function userExists($user)
    {
        return \CUser::GetList(($by="personal_country"), ($order="desc"), ['EMAIL' => $user['EMAIL']])->Fetch()['ID'];
    }


}