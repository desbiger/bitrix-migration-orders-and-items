<?php

namespace BitrixMigration\Import\ProductsReader;


class Products extends FilesReader {
    public $folder = '/products/';

    public function setLoadedIDS($list)
    {
        $this->loadedIDs = $list;
    }


}