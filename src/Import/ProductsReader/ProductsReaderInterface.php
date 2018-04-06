<?php

namespace BitrixMigration\Import\ProductsReader;

interface ProductsReaderInterface {

    /**
     * ProductsReaderInterface constructor.
     *
     * @param $filesPath
     */
    public function __construct($filesPath,$import_path);

    /**
     * @return array|bool
     */
    public function getNextElement();

}