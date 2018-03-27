<?php


namespace BitrixMigration\Export;


use BitrixMigration\BitrixMigrationHelper;

class ExportPaySystems {
    use BitrixMigrationHelper;


    public function getAll()
    {
        return $this->FetchAll(\CSalePaySystem::GetList([], ['LID' => 's1']), function ($paySystem) {
            $paySystem['ACTION'] = \CSalePaySystemAction::GetByID($paySystem['ID']);
            $paySystem['ACTION']['LOGOTIP'] = $paySystem['ACTION']['LOGOTIP'] ? \CFIle::GetPath($paySystem['ACTION']['LOGOTIP']) : '';

            return $paySystem;

        });
    }

}