<?php

namespace BitrixMigration\Import\ProductsReader;


class Prices extends FilesReader {
    public $folder = '/prices/';
    protected $containerIDsFieldName = 'newPriceIDs';
}