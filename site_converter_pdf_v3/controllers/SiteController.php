<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\UploadForm;
use yii\web\UploadedFile;
use app\models\Document;
use yii\web\Cookie;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        // Загрузка файла:
        $model = new UploadForm();
        if (Yii::$app->request->isPost) {
            $model->documentFile = UploadedFile::getInstance(
                $model,
                'documentFile'
            );
            if ($model->upload()) {
                // Файл успешно загружен
                return $this->redirect([
                    'view-slider',
                    'id' => $model->documentId,
                ]);
            }
        }
        // Получение массива с документами из cookies:
        $cookieDocumentsArray = Document::getCookieDocumentsArray();
        return $this->render('index', [
            'model' => $model,
            'cookieDocumentsArray' => $cookieDocumentsArray,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Установка cookies для ссылок при скачивании архива.
     */
    public function actionDownloadSetCookie()
    {
        $request = Yii::$app->request;
        // Поиск документа:
        $document = Document::find()
            ->where(['id' => (int)$request->get('id')])
            ->one();
        $cookies = Yii::$app->response->cookies;
        // Добавление в cookies:
        $cookies->add(new Cookie([
            'name' => "id_".$document->filename,
            'value' => $document->id,
            'expire' => time() + \Yii::$app->params['linksCookieTime'],
        ]));
        return $this->redirect(['download', 'id' => $document->id]);
    }

    /**
     * Скачивание архива.
     */
    public function actionDownload()
    {
        $request = Yii::$app->request;
        // Поиск документа:
        $document = Document::find()
            ->where(['id' => (int)$request->get('id')])
            ->one();
        // Скачивание:
        $document->downloadSliderZip();
    }

    /**
     * Просмотр слайдера.
     */
    public function actionViewSlider()
    {
        $slidesArray;
        $request = Yii::$app->request;
        // Поиск документа:
        $document = Document::find()
            ->where(['id' => (int)$request->get('id')])
            ->one();
        if ($document) {
            // Получение массива со слайдами:
            $slidesArray = $document->getSlidesArray();
        }
        return $this->render('slider', [
            'id' => $document->id,
            'filename' => $document->filename,
            'slidesArray' => $slidesArray,
        ]);
    }
}
