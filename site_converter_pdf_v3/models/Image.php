<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "images".
 *
 * @property integer $id
 * @property integer $document_id
 * @property string $document_filename
 * @property string $filename
 * @property string $type
 * @property integer $size
 */
class Image extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'images';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['document_id', 'document_filename', 'filename', 'type', 'size'], 'required'],
            [['document_id', 'size'], 'integer'],
            [['document_filename', 'filename'], 'string', 'max' => 255],
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
            'document_id' => 'Document ID',
            'document_filename' => 'Document Filename',
            'filename' => 'Filename',
            'type' => 'Type',
            'size' => 'Size',
        ];
    }
}
