<?php

namespace BitrixMigration\Import;

class Container {

    use SavedContainer;

    public $newIblock;

    public $newPersonsTypeIDS;
    public $newOrderPropsIDS;
    public $newPaySystemIDS;
    public $newDeliveryIDs;
    public $newProductsIDs;
    public $newPriceTypesIDs;
    public $newPriceIDs;


    public $sectionImportResult;

    /**
     * @var \BitrixMigration\Import\ImportProducts
     */
    public $ProductsImportResult;

    public $usersImportResult;

    /**
     * @var Container
     */
    protected static $instance;
    public $import_path;

    static function init()
    {
        return new self();
    }

    public function __construct()
    {

    }

    /**
     *
     */
    public function trySaveContainer()
    {
        if (method_exists($this, 'saveContainer')) {
            $this->saveContainer();
        }
    }


    /**
     * @return Container
     */
    static function instance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param mixed $newIblock
     */
    public function setNewIblock($newIblock)
    {
        $this->newIblock = $newIblock;
        $this->trySaveContainer();
    }

    /**
     * @return \BitrixMigration\Import\ImportIblock
     */
    public function getNewIblock()
    {
        return $this->newIblock;
    }

    /**
     * @return mixed
     */
    public function getSectionImportResult()
    {
        return $this->sectionImportResult;
    }

    /**
     * @param mixed $sectionImportResult
     */
    public function setSectionImportResult($sectionImportResult)
    {
        $this->sectionImportResult = $sectionImportResult;
        $this->trySaveContainer();
    }

    /**
     * @return \BitrixMigration\Import\ImportProducts
     */
    public function getProductsImportResult()
    {
        return $this->ProductsImportResult;
    }

    /**
     * @param mixed $ProductsImportResult
     */
    public function setProductsImportResult($ProductsImportResult)
    {
        $this->ProductsImportResult = $ProductsImportResult;
        $this->trySaveContainer();
    }

    /**
     * @return mixed
     */
    public function getImportPath()
    {
        return $this->import_path;
    }

    /**
     * @param mixed $import_path
     */
    public function setImportPath($import_path)
    {
        $this->import_path = $import_path;
        $this->tryLoadContainer();
    }

    private function tryLoadContainer()
    {
        if (method_exists($this, 'load_container')) {
            $this->load_container($this->import_path);
        }
    }

    /**
     * @return mixed
     */
    public function getUsersImportResult()
    {
        return $this->usersImportResult ?: [];
    }

    /**
     * @param mixed $usersImportResult
     */
    public function setUsersImportResult($usersImportResult)
    {
        $this->usersImportResult = $usersImportResult;
        $this->trySaveContainer();
    }

    /**
     * @param mixed $newPersonsTypeIDS
     */
    public function setNewPersonsTypeIDS($newPersonsTypeIDS)
    {
        $this->newPersonsTypeIDS = $newPersonsTypeIDS;
        $this->trySaveContainer();
    }

    /**
     * @param mixed $newOrderPropsIDS
     */
    public function setNewOrderPropsIDS($newOrderPropsIDS)
    {
        $this->newOrderPropsIDS = $newOrderPropsIDS;
        $this->trySaveContainer();
    }

    /**
     * @param mixed $newPaySystemIDS
     */
    public function setNewPaySystemIDS($newPaySystemIDS)
    {
        $this->newPaySystemIDS = $newPaySystemIDS;
        $this->trySaveContainer();
    }

    /**
     * @param mixed $newDeliveryIDs
     */
    public function setNewDeliveryIDs($newDeliveryIDs)
    {
        $this->newDeliveryIDs = $newDeliveryIDs;
        $this->trySaveContainer();
    }

    /**
     * @return mixed
     */
    public function getNewProductsIDs()
    {
        return $this->newProductsIDs;
    }

    /**
     * @param mixed $newProductsIDs
     */
    public function setNewProductsIDs($newProductsIDs)
    {
        $this->newProductsIDs = $newProductsIDs;
        $this->trySaveContainer();
    }

    /**
     * @param $id
     * @param $newID
     */
    public function addNewProductID($id, $newID)
    {
        $this->newProductsIDs[$id] = $newID;
        $this->trySaveContainer();
    }

    /**
     * @param mixed $newPriceIDs
     */
    public function addNewPriceID($oldID, $newID)
    {
        $this->newPriceIDs[$oldID] = $newID;
        $this->trySaveContainer();
    }

}
