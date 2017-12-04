<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Document;

/**
 * ApiController - класс контроллера для api.
 */
class ApiController extends Controller
{
    /**
     * Действие по умолчанию.
     */
    public function actionIndex()
    {
    }

    /**
     * Возврат ссылок изображений слайдера.
     */
    public function actionSlider($id)
    {
        // Получение id /api/slider/{$id}
//        $request = Yii::$app->request;
//        $id = (int)$request->get('id');
        // Проверка переданного id:
        if (empty($id)) {
            // Не передан id:
            $this->sendResponse(400, 'Error. Invalid ID supplied.');
        } else {
            // Получение списка ссылок на изображения:
            $imagesLinks = Document::getImagesLinks($id);
            if (!isset($imagesLinks)) {
                // Изображения не найдены в базе данных:
                $this->sendResponse(404, 'Error. Slider not found.');
            } else {
                $slider = [
                    'id' => $id,
                    'imagesLinks' => $imagesLinks
                ];
                // Кодировка:
                if (\Yii::$app
                    ->params['restApiResponseContentType'] ==
                    'application/json'
                ) {
                    // Кодировка в формат json:
                    $slider = json_encode($slider);
                } else {
                    // Кодировка в другой формат
                }
                $this->sendResponse(
                    200,
                    $slider,
                    \Yii::$app->params['restApiResponseContentType']
                );
            }
        }
    }

    /**
     * Отправка ответа.
     */
    public function sendResponse(
        $statusCode = 200,
        $content = '',
        $contentType = 'text/html'
    ) {
        // Отправка стартовой строки:
        $responseStartLine = 'HTTP/1.1 '.$statusCode.' '.
            $this->getStatusCodeMessage($statusCode);
        header($responseStartLine);
        // Отправка заголовков:
        header('Content-type: '.$contentType);
        // Отправка содержимого:
        echo $content;
        // Завершение:
        //exit;
    }

    /**
     * Получение сообщения кода статуса.
     */
    public function getStatusCodeMessage($statusCode)
    {
        $codes = [
            200 => 'Successful operation',
            400 => 'Invalid ID supplied',
            404 => 'Slider not found'
        ];
        return (isset($codes[$statusCode])) ? $codes[$statusCode] : '';
    }
}
