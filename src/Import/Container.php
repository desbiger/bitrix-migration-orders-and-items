<?php
namespace BitrixMigration\Import;

class Container {

    public $newIblock;

    public $sectionImportResult;

    /**
     * @var \BitrixMigration\Import\ImportProducts
     */
    public $ProductsImportResult;

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
    }
}
