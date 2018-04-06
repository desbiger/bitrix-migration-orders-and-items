# Скрипт переноса данных битрикс (еще в отладке большая половина функционала не отлажена)

## Предназначен для переноса торгового каталога вместе с заказами и пользователями.

### Пример формирования списка товаров для инфоблока ID = 6

```php
<?

use BitrixMigration\Import;
use BitrixMigration\Import\Container;
use BitrixMigration\Import\ImportIblock;
use BitrixMigration\Import\ImportOrders;
use BitrixMigration\Import\ImportPrices;
use BitrixMigration\Import\ImportProducts;
use BitrixMigration\Import\ImportSections;

define('DOCROOT', __DIR__ . '/../');
$_SERVER['DOCUMENT_ROOT'] = DOCROOT;
require(DOCROOT . 'bitrix/modules/main/include/prolog_before.php');

\CModule::IncludeModule('iblock');


while (ob_get_level()) {
    ob_end_flush();
}
Container::instance()->setImportPath(__DIR__);

Import::init()
    ->register(new ImportIblock('iblock'))
    ->register(new ImportSections())
    ->register(new ImportProducts())
    ->register(new ImportPrices())
    ->register(new ImportOrders())
    ->import();

```