<?php

namespace app\helpers;

/**
 * MyHelper - класс-помощник.
 */
class MyHelper
{
    /**
     * Архивация zip.
     */
    public static function makeZip($source, $destination)
    {
        // Проверка загрузки расширения zip для php и источника для архивации:
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }
        // Создание архива:
        $zip = new \ZipArchive();
        if (!$zip->open($destination, \ZIPARCHIVE::CREATE)) {
            return false;
        }
        // Подготовка пути (замена разделителей на DS):
        $source = str_replace('\\', DS, realpath($source));
        $source = str_replace('/', DS, $source);
        // Обработка директорий:
        if (is_dir($source) === true) {
            // Создание рекурсивного итератора:
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($source),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            // Проход по каждому элементу рекурсии:
            foreach ($files as $file) {
                // Подготовка пути (замена разделителей на DS):
                $file = str_replace('\\', DS, $file);
                $file = str_replace('/', DS, $file);
                // Пропускаем итерацию в частных случаях:
                if ($file == '.' || $file == '..' || empty($file) || $file == DS) {
                    continue;
                }
                // Пропускаем папки с "." и "..":
                if (in_array(
                    substr($file, strrpos($file, DS) + 1),
                    array('.', '..')
                )) {
                    continue;
                }
                // Определение абсолютного пути:
                $file = realpath($file);
                // Подготовка пути (замена разделителей на DS):
                $file = str_replace('\\', DS, $file);
                $file = str_replace('/', DS, $file);
                // Если это директория:
                if (is_dir($file) === true) {
                    $d = str_replace($source . DS, '', $file);
                    if (empty($d)) {
                        continue;
                    }
                    // Добавление новой директории в архив:
                    $zip->addEmptyDir($d);
                // Если это файл:
                } elseif (is_file($file) === true) {
                    // Добавление файла в архив:
                    $zip->addFromString(
                        str_replace($source . DS, '', $file),
                        file_get_contents($file)
                    );
                }
            }
        // Если это файл:
        } elseif (is_file($source) === true) {
            // Добавление файла в архив:
            $zip->addFromString(basename($source), file_get_contents($source));
        }
        // Закрытие архива:
        return $zip->close();
    }

    /**
     * Скачивание zip архива.
     */
    public static function downloadZip($file)
    {
        // Определение базового имени файла:
        $fileName = basename($file);
        // Установка заголовка:
        header("Content-Type: application/zip");
        header("Content-Disposition: attachment; filename=" . $fileName);
        // Чтение файла и передача в буфер вывода:
        readfile($file);
        // Удаление файла архива:
        if (is_file($file)) {
            unlink($file);
        }
        // Завершение выполнения скрипта:
        exit;
    }
}
