<?php

declare(strict_types=1);

namespace Nieroo\CSVReader;

use Exception;

/**
 * Класс разбирает построчно csv-файлы в кодировках CP1251 и UTF-8
 *
 * Пример использования:
 *
 * ```php
 * <?php
 *
 * use Nieroo\CSVReader\CSVReader;
 *
 * $filename = '/path/to/file.csv';
 *
 * $reader = new CSVReader();
 *
 * if ($reader->load($filename)) {
 *     $reader->ignoreWhiteLines(true);
 *
 *     while ($row = $reader->getNextRow()) {
 *         print_r($row);
 *     }
 * } else {
 *     print_r($reader->getLoadError());
 * }
 *
 * ```
 *
 * @package Nieroo\CSVReader
 */
class CSVReader
{
    /**
     * @var resource Указатель на csv-файл
     */
    private $file;

    /**
     * @var string Кодировка файла
     */
    private $fileEncoding;

    /**
     * @var array Массив с разрешенными кодировками файла
     */
    private $fileEncodings = [
        'utf-8',
        'iso-8859-1',
    ];

    /**
     * @var bool Пропускать или нет пустые строки
     */
    private $ignoreWhiteLines = false;

    /**
     * @var Exception Ошибка при загрузке файла
     *
     * @see load()
     */
    private $loadError;

    /**
     * Загружает файл. Файл открывается с правами на чтение и запись (r+).
     * В случае возникновения ошибки объект ошибки будет доступен
     * через вызов метода getLoadError() объекта
     *
     * @param string $filename Имя (путь) csv-файла
     *
     * @see getLoadError()
     *
     * @return bool Возвращает true, если файл загружен, иначе — false
     */
    public function load(string $filename) : bool
    {
        $this->loadError = null;

        try {
            $this->file = fopen($filename, 'r+');

            if (!$this->file) {
                throw new Exception('FILE_NOT_LOADED');
            }

            $this->fileEncoding = finfo_file(
                finfo_open(FILEINFO_MIME_ENCODING),
                $filename
            );

            if (!in_array($this->fileEncoding, $this->fileEncodings)) {
                throw new Exception('INCORRECT_FILE_ENCODING');
            }
        } catch (Exception $e) {
            $this->loadError = $e;

            $this->file = null;
            $this->fileEncoding = null;

            return false;
        }

        return true;
    }

    /**
     * Возвращает объект ошибки, если во время загрузки файла возникла ошибка.
     * В случае успешной загрузки csv-файла возвращает null
     *
     * @see load()
     *
     * @return Exception|null Ошибка загрузки файла
     */
    public function getLoadError()
    {
        return $this->loadError;
    }

    /**
     * Устанавливает опцию пропуска пустых строк.
     *
     * @param bool $ignoreWL
     */
    public function ignoreWhiteLines(bool $ignoreWL)
    {
        $this->ignoreWhiteLines = $ignoreWL;
    }

    /**
     * Возвращает следующую строку csv-файла (пустые строки пропускаются).
     * Массив содержит данные в кодировке UTF-8
     *
     * @return array|null Массив данных или null в случае конца файла
     */
    public function getNextRow() : ?array
    {
        $data = null;

        while ($rowData = fgetcsv($this->file)) {
            if ($this->ignoreWhiteLines
                && count($rowData) === 1
                && !$rowData[0]
            ) {
                continue;
            }

            $data = array_map(function($val) {
                return $this->convertToUTF8($val);
            }, $rowData);

            break;
        }

        return $data;
    }

    /**
     * Конвертирует строку в UTF-8
     *
     * @param string|null $inStr Входная строка
     *
     * @return string Выходная строка в кодировке UTF-8
     */
    private function convertToUTF8(?string $inStr) : string
    {
        $outStr = strval($inStr);

        if ($this->fileEncoding === 'iso-8859-1') {
            $outStr = iconv('CP1251', 'UTF-8', $outStr);
        }

        return $outStr;
    }
}
