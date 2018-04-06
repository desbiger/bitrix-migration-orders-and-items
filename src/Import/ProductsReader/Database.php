<?php
namespace BitrixMigration\Import\ProductsReader;

use BitrixMigration\Import\ProductsReader\ProductsReaderInterface;

class Database implements ProductsReaderInterface {

    /**
     * ProductsReaderInterface constructor.
     *
     * @param $filesPath
     */
    public function __construct($filesPath, $import_path)
    {
        parent::__construct($filesPath, $import_path);
    }

    /**
     * @return array|bool
     */
    public function getNextElement()
    {
        // TODO: Implement getNextElement() method.
    }
}