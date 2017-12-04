<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use app\models\Document;

class UploadForm extends Model
{
    public $documentId;

    /**
     * @var UploadedFile
     */
    public $documentFile;

    public function rules()
    {
        return [
            [['documentFile'],
            'file',
            'skipOnEmpty' => false,
            'extensions' => \Yii::$app->params['documentExtension'],
            'maxSize' => \Yii::$app->params['maxFileSize']],
        ];
    }

    public function upload()
    {
        if ($this->validate()) {
            // Формирование имени документа:
            $documentFileName = $this->documentFile->baseName.
                                '_'.time().'.'.
                                $this->documentFile->extension;
            // Сохранение документа:
            $this->documentFile->saveAs(
                Yii::getAlias('@documents').
                '/'.
                $documentFileName
            );
            // Запись документа в базу данных:
            $document = new Document;
            $document->filename = $documentFileName;
            $document->type = $this->documentFile->extension;
            $document->size = $this->documentFile->size;
            $document->save();
            $this->documentId = $document->id;
            // Проверка количества страниц в документе:
            if ($document->getPagesNumber()
                > \Yii::$app->params['maxPageNumber']
            ) {
                // Количество страниц превышает допустимое значение.
                // Удаление документа:
                $documentFullPath = Yii::getAlias('@documentsFullPath').
                                    '/'.$document->filename;
                if (is_file($documentFullPath)) {
                    unlink($documentFullPath);
                }
                // Удаление документа из базы данных:
                $document->delete();
                // Сообщение о превышении количества страниц:
                $this->addError(
                    'documentFile',
                    'Документ слишком большой. '.
                    'Количество страниц не должно превышать '.
                    \Yii::$app->params['maxPageNumber'].
                    '.'
                );
                return false;
            } else {
                // Количество страниц в норме.
                $document->convert();
            }
            return true;
        } else {
            return false;
        }
    }
}
