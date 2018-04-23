<?php

namespace BitrixMigration\Import;

use BitrixMigration\BitrixMigrationHelper;
use BitrixMigration\CLI;
use BitrixMigration\Import\Contracts\Importer;
use BitrixMigration\Import\ProductsReader\Products;
use BitrixMigration\JsonReader;

class ImportProducts implements Importer {

    use BitrixMigrationHelper, JsonReader, MigrationFilesUploadHelper;
    public $iblockElement;
    public $exportProducts;
    public $readedChunks;
    public $OldFilesArray;
    public $newIblockID;
    /**
     * @var ImportIblock
     */
    private $newIblock;
    private $import_path;

    public $newIds;


    public function __construct()
    {
        $this->iblockElement = new \CIBlockElement();
    }

    /**
     * @param $productXMLID
     * @param $price
     *
     * @return mixed
     */
    public function getProductPriceID($productXMLID, $price)
    {
        $t = $this->iblockElement->GetList([], ['XML_ID' => $productXMLID]);
        $product = $t->Fetch();
        $prices = $this->exportProducts->CatalogPrices->getPrices($product['ID'], ['PRICE' => $price]);

        return count($prices) ? $price[0]['ID'] : null;
    }

    /**
     *
     */
    private function importCatalog()
    {

        $reader = new Products();

        echo "\n";
        while (list($element, $count, $counter, $file, $key) = $reader->getNextElement()) {

            CLI::show_status($counter, $count, 30, ' | file: ' . $file);
            $newID = $this->createElementIfNotExist($element);
            Container::instance()->addNewProductID($element['ID'], $newID);
        }

    }


    /**
     * @param $Element
     *
     * @return mixed
     */
    private function createElementIfNotExist($Element)
    {
        $element = new \CIBlockElement();
        if ($id = $this->exists($Element)) {
            return $id;
        }

        $id = $element->add($Element);
        if (!$id) {
            echo $element->LAST_ERROR;
            dd($Element);
        }

        return $id;

    }

    /**
     * @param $Element
     *
     * @return mixed
     */
    private function exists($Element)
    {
        return \CIBlockElement::GetList([], [
            'XML_ID' => $Element['XML_ID'],
            'NAME'   => $Element['NAME']
        ])->Fetch()['ID'];
    }



    public function before()
    {
        $container = Container::instance();
        $this->import_path = $container->getImportPath();
        $this->newIblock = $container->getNewIblock();
        $this->newIblockID = $this->newIblock->newIblockID;
        $this->loadFiles();
    }

    public function after()
    {
        $this->allFilesArray = [];
    }


    public function execute()
    {
        $this->before();
        $this->importCatalog();
        $this->after();
    }

    /**
     * @return string
     */
    public function getImportName()
    {
        return 'Import IBlock Elements';
    }

    public function setSiteID($id)
    {
        // TODO: Implement setSiteID() method.
    }
}