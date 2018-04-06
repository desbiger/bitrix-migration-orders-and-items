<?php


namespace BitrixMigration\Import;


use BitrixMigration\JsonReader;

class ImportDelivery {

    use JsonReader;
    public $excluidedFields = ['ID' => ''];
    public $deliverys;
    public $newIDs;

    public static function init()
    {
        return new self();
    }

    public function __construct()
    {
        \CModule::IncludeModule('sale');
        $this->deliverys = $this->read('delivery');
    }

    public function import()
    {
        foreach ($this->deliverys as $delivery) {
            $delivery = $this->clearFields($delivery);

            $this->newIDs[$delivery['ID']] = $this->createIfNotExists($delivery);
        }
        return $this;
    }

    private function createIfNotExists($delivery)
    {
        if (!$id = $this->isExists($delivery))
            return $this->createDelivery($delivery);

        return $id;
    }

    /**
     * @param $delivery
     *
     * @return mixed
     */
    private function isExists($delivery)
    {
        return \CSaleDelivery::GetList([], ['NAME' => $delivery['NAME']])->Fetch()['ID'];
    }

    /**
     * @param $delivery
     *
     * @return mixed
     */
    private function createDelivery($delivery)
    {
        return \CSaleDelivery::add($delivery);
    }

    /**
     * @param $delivery
     *
     * @return array
     */
    private function clearFields($delivery)
    {
        return array_diff_key($delivery, $this->excluidedFields);
    }
}