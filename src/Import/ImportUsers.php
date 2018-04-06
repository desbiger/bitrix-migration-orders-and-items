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

        foreach ($this->list as $user) {
            return $this->createUserIfNotExists($user);
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