<?php

namespace BitrixMigration\Import\Contracts;


interface Importer {
    public function execute();

    /**
     * @return string
     */
    public function getImportName();

    public function before();

    public function after();
}