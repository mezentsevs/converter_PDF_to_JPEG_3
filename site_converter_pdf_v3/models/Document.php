<?php

namespace app\models;

use Yii;
use app\models\Image;
use app\helpers\MyHelper;

/**
 * This is the model class for table "documents".
 *
 * @property integer $id
 * @property string $filename
 * @property string $type
 * @property integer $size
 */
class Document extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'documents';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['filename', 'type', 'size'], 'required'],
            [['size'], 'integer'],
            [['filename'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'filename' => 'Filename',
            'type' => 'Type',
            'size' => 'Size',
        ];
    }

    /**
     * Определение количества страниц.
     */
    public function getPagesNumber()
    {
        // Открытие файла на чтение:
        $f = fopen(
            Yii::getAlias('@documentsFullPath').'/'.$this->filename,
            "r"
        );
        // Поиск регулярными выражениями количества страниц:
        $count = 0;
        while (!feof($f)) {
            // Построчное чтение файла:
            $line = fgets($f, 255);
            // Поиск сочетаний "Count число" в строке:
            if (preg_match('/\/Count [0-9]+/', $line, $matches)) {
                // Поиск значений "число" в результате предыдущего поиска:
                preg_match('/[0-9]+/', $matches[0], $matches2);
                // Запись количества страниц, если значение больше текущего:
                if ($count<$matches2[0]) {
                    $count=$matches2[0];
                }
            }
        }
        // Закрытие файла:
        fclose($f);
        // Возвращение количества страниц:
        return $count;
    }

    /**
     * Конвертирование документа.
     */
    public function convert()
    {
        // Определение путей:
        $pdfFilePath = Yii::getAlias('@documentsFullPath').
                        '/'.$this->filename;
        $pdfFilePathInfo = pathinfo($this->filename);
        $imgFolderPath = Yii::getAlias('@slidersFullPath').
                            '/'.$pdfFilePathInfo["filename"].
                            '/'.\Yii::$app->params['imagesDir'];
        // Создание директории:
        if (mkdir($imgFolderPath, 0777, true)) {
            // Успех cоздания директории:
            // Попытка обработки документа PDF:
            try {
                // Создание нового пустого объекта Imagick:
                $imagick = new \Imagick();
                // Установка кол-ва точек на дюйм (300,300 = 300dpi):
                $imagick->setResolution(300, 300);
                // Чтение документа PDF из файла:
                $imagick->readImage($pdfFilePath);
                // Конвертирование страниц документа PDF в изображения JPEG:
                $i=0;
                foreach ($imagick as $pageImage) {
                    $i++;
                    // Добавление первого "0" для чисел меньше 10:
                    if ($i<10) {
                        $i = "0".$i;
                    }
                    // Установка палитры:
                    $pageImage->setImageColorspace(255);
                    // Установка компрессора JPEG:
                    $pageImage->setCompression(\Imagick::COMPRESSION_JPEG);
                    // Установка качества сжатия
                    // (1 = высокое сжатие .. 100 = низкое сжатие):
                    $pageImage->setCompressionQuality(80);
                    // Установка формата изображения:
                    $pageImage->setImageFormat(
                        \Yii::$app->params['imageExtension']
                    );
                    // Изменение альбомной ориентации страницы в портретную:
                    if ($pageImage->getImageWidth() >
                        $pageImage->getImageHeight()
                    ) {
                        // Поворот изображения против часовой стрелки:
                        $pageImage->rotateImage("#000", -90);
                    }
                    // Определение пути к файлу изображения JPEG:
                    $imgFileName = $pdfFilePathInfo["filename"].
                        "_".$i.".".\Yii::$app->params['imageExtension'];
                    $imgFilePath = $imgFolderPath.'/'.$imgFileName;
                    $imgFilePathInfo = pathinfo($imgFilePath);
                    // Запись страницы документа PDF
                    // в файл изображения JPEG:
                    $pageImage->writeImage($imgFilePath);
                    // Создание объекта image,
                    // инициализация и сохранение в базе данных:
                    $image = new Image();
                    $image->document_id = $this->id;
                    $image->document_filename = $this->filename;
                    $image->filename = $imgFilePathInfo["basename"];
                    $image->type = $imgFilePathInfo["extension"];
                    $image->size = filesize($imgFilePath);
                    $image->save();
                }
                // Освобождение памяти и уничтожение объекта Imagick:
                $imagick->clear();
                $imagick->destroy();
                // Запись файла index.html для слайдера:
                $this->writeSlidersIndexHtml();
                return true;
            } catch (ImagickException $e) {
                // Обработка исключения - вывод сообщения:
                echo $e->getMessage();
                return false;
            }
        } else {
            // Неудача cоздания директории:
            return false;
        }
    }

    /**
     * Запись содержимого файла index.html слайдера для скачивания.
     */
    public function writeSlidersIndexHtml()
    {
        $slidesArray = [];
        // Определение деталей файла pdf:
        $pdfFilePathInfo = pathinfo($this->filename);
        // Формирование массива с адресами изображений:
        $imageSet = Image::find()
            ->where(['document_id' => $this->id])
            ->orderBy(['filename' => SORT_ASC])
            ->all();
        if ($imageSet) {
            foreach ($imageSet as $image) {
                $slidesArray[] = "'".\Yii::$app->params['imagesDir'].
                                "/".$image->filename."'";
            }
        }
        // Формирование содержимого файла:
        $content = $this->getSlidersIndexHtmlContent($slidesArray);
        // Запись содержимого в файл:
        file_put_contents(
            \Yii::$app->params['slidersDir'].
            "/".$pdfFilePathInfo["filename"].
            "/index.html",
            $content
        );
    }

    /**
     * Формирование содержимого файла index.html слайдера для скачивания.
     */
    public function getSlidersIndexHtmlContent($slidesArray = [])
    {
        $output  = "<!DOCTYPE html>\r\n";
        $output .= "<html lang=\"ru\">\r\n";
        $output .= "<head>\r\n";
        $output .= "<meta charset=\"utf-8\">\r\n";
        $output .= "<title>Слайдер</title>\r\n";
        $output .= "</head>\r\n";
        $output .= "<style>\r\n";
        $output .= "body { margin: 0; font-family: Arial;";
        $output .= " font-size: 1em; }\r\n";
        $output .= "header { background: #EEE; height: 50px; }\r\n";
        $output .= "h1 { margin: 0; font-size: 1.2em;";
        $output .= " text-align: center; line-height: 50px; }\r\n";
        $output .= "main { min-height: 1000px; }\r\n";
        $output .= "#slider { margin: 50px auto; width: 640px;";
        $output .= " text-align: center; }\r\n";
        $output .= "#slide { width: 620px; border: 1px solid #CCC;";
        $output .= " -moz-box-shadow: 1px 1px 1px #CCC;";
        $output .= " -o-box-shadow: 1px 1px 1px #CCC;";
        $output .= " -webkit-box-shadow: 1px 1px 1px #CCC;";
        $output .= " box-shadow: 1px 1px 1px #CCC; }\r\n";
        $output .= "button { margin: 10px 5px; padding: 3px; }\r\n";
        $output .= "footer { background: #EEE; height: 50px;";
        $output .= " line-height: 50px; text-align: center; }\r\n";
        $output .= "</style>\r\n";
        $output .= "<script>\r\n";
        $output .= "var slider = {\r\n";
        $output .= "slides:[".htmlentities(join(',', $slidesArray))."],\r\n";
        $output .= "index:0,\r\n";
        $output .= "set: function(image) {";
        $output .= " document.getElementById(\"slide\").";
        $output .= "setAttribute(\"src\", image); },\r\n";
        $output .= "init: function() { this.set(this.";
        $output .= "slides[this.index]); },\r\n";
        $output .= "left: function() { this.index--; if (this.index < 0) {";
        $output .= " this.index = this.slides.length-1; }";
        $output .= " this.set(this.slides[this.index]); },\r\n";
        $output .= "right: function() { this.index++; if (";
        $output .= "this.index == this.slides.length) { this.index = 0; }";
        $output .= " this.set(this.slides[this.index]); }\r\n";
        $output .= "};\r\n";
        $output .= "window.onload = function() { slider.init();";
        $output .= " setInterval(function() { slider.right(); },5000); };\r\n";
        $output .= "</script>\r\n";
        $output .= "<body>\r\n";
        $output .= "<header><h1>Слайдер</h1></header>\r\n";
        $output .= "<main>\r\n";
        $output .= "<figure id=\"slider\">\r\n";
        $output .= "<img id=\"slide\" src=\"\" alt=\"слайд\">\r\n";
        $output .= "<button id=\"left\" onclick=\"slider.left();\">";
        $output .= "&laquo; Назад</button>\r\n";
        $output .= "<button id=\"right\" onclick=\"slider.right();\">";
        $output .= "Далее &raquo;</button>\r\n";
        $output .= "</figure>\r\n";
        $output .= "</main>\r\n";
        $output .= "<footer><small>&copy; Copyright</small></footer>\r\n";
        $output .= "</body>\r\n";
        $output .= "</html>\r\n";
        return $output;
    }

    /**
     * Поиск изображений для документа.
     */
    public function getSlidesArray()
    {
        $slidesArray = [];
        $imageSet = Image::find()
            ->where(['document_id' => $this->id])
            ->orderBy(['filename' => SORT_ASC])
            ->all();
        if ($imageSet) {
            foreach ($imageSet as $image) {
                $pdfFilePathInfo = pathinfo($image->document_filename);
                $slidesArray[] = "'".
                    Yii::getAlias('@webRelPath')."/".
                    \Yii::$app->params['slidersDir']."/".
                    $pdfFilePathInfo["filename"]."/".
                    \Yii::$app->params['imagesDir']."/".
                    $image->filename.
                    "'";
            }
        }
        return $slidesArray;
    }

    /**
     * Скачивание zip архива слайдера.
     */
    public function downloadSliderZip()
    {
        // Определение пути слайдера:
        $pdfFilePathInfo = pathinfo($this->filename);
        $source = \Yii::$app->params['slidersDir']."/".
                    $pdfFilePathInfo["filename"];
        // Определение пути архива:
        $destination = $source.".zip";
        // Выполнение архивации:
        if (MyHelper::makeZip($source, $destination)) {
            // Скачивание и удаление архива:
            MyHelper::downloadZip($destination);
        }
    }

    /**
     * Получение массива документов из cookies.
     */
    public static function getCookieDocumentsArray()
    {
        $cookieDocumentsArray = [];
        foreach (Yii::$app->request->cookies->toArray() as $cookie) {
            // Поиск cookie с id:
            if (preg_match("/^id_/", $cookie->name)) {
                // Поиск документа:
                $cookieDocument = self::find()
                    ->where(['id' => (int)$cookie->value])
                    ->one();
                // Добавление документа в массив:
                if ($cookieDocument) {
                    $cookieDocumentsArray[] = $cookieDocument;
                }
            }
        }
        return $cookieDocumentsArray;
    }

    /**
     * Получение массива ссылок на изображения документа по его id.
     */
    public static function getImagesLinks($id)
    {
        // Поиск изображений для переданного id:
        $imageSet = Image::find()
            ->where(['document_id' => (int)$id])
            ->orderBy(['filename' => SORT_ASC])
            ->all();
        if ($imageSet) {
            // Изображения найдены
            foreach ($imageSet as $image) {
                $documentFileNamePathInfo = pathinfo(
                    $image->document_filename
                );
                // Формирование массива со ссылками:
                $result[] = $_SERVER['SERVER_NAME'].'/'.
                            Yii::getAlias('@webRelPath').'/'.
                            \Yii::$app->params['slidersDir'].'/'.
                            $documentFileNamePathInfo["filename"].'/'.
                            \Yii::$app->params['imagesDir'].'/'.
                            $image->filename;
            }
            return $result;
        } else {
            // Изображения не найдены
            return null;
        }
    }
}
