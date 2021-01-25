# CSV Reader

Библиотека позволяет **считывать построчно CSV-файлы в кодировках `UTF-8`
и `CP1251` (`Windows-1251`)**.

## Требования

Требуется `PHP >=7.2` и расширения `ext-iconv` и `ext-fileinfo`.

*При желании можно переписать код под php5.6 или ниже. Однако автор считает,
что уже не стоит поддерживать настолько устаревшие версии PHP. В будущем
требования к версии PHP будут только повышаться.*

## Как установить библиотеку

Выполните в терминале команду:
```
composer require nieroo/csv-reader
```

Или добавьте в composer.json:
```json
{
    "require": {
        "nieroo/csv-reader": ">=1.0"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/Nieroo/csv-reader"
        }
    ]
}
```
После чего выполните в терминале команду:
```
composer update
```

## Как пользоваться

```php
<?php

// Подключаем автозагрузчик composer
require_once __DIR__ . '/vendor/autoload.php';

use Nieroo\CSVReader\CSVReader;

// Указываем путь до CSV-файла
$filename = __DIR__ . '/путь/до/файла.csv';

$reader = new CSVReader();

// Загружаем CSV-файл
if ($reader->load($filename)) {
    // Включаем игнорирование пустых строк
    // (по умолчанию пустые строки не игнорируются)
    $reader->ignoreWhiteLines(true);

    // Зачитываем построчно CSV-файл (массив значений в строке)
    while ($row = $reader->getNextRow()) {
        print_r($row);
    }
// Обрабатываем ошибку в случае неудачной загрузки CSV-файла
} else {
    print_r($reader->getLoadError());
}
```

## Примечания

1. CSV-файл открывается в режиме r+ (для чтения с записи).
2. Возможные ошибки при загрузке файла:
  - `FILE_NOT_LOADED` — файл не найден или не может быть открыт в режиме r+;
  - `INCORRECT_FILE_ENCODING` — файл в кодировке, отличном от `UTF-8` и `CP1251`.
