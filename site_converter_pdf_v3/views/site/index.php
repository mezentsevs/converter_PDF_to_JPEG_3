<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::$app->name;
?>
<div class="site-index">

    <div class="jumbotron">
        
        <h1><?= Html::encode($this->title) ?></h1>
        <h2>Добро пожаловать!</h2>
        <p class="lead">Пожалуйста, выберите документ PDF и нажмите кнопку "Конвертировать".</p>

    </div>

    <div class="body-content">

        <div class="row">
            <div class="col-lg-12">

            <!-- Форма для отправки документа: -->
            <?php $form = ActiveForm::begin([
                'options' => ['enctype' => 'multipart/form-data']
                ]) ?>
                <?= $form->field($model, 'documentFile')->fileInput()->label(false) ?>
                <div class="form-group">
                    <?= Html::submitButton(
                        'Конвертировать',
                        ['class' => 'btn btn-primary']
                    ) ?>
                </div>
            <?php ActiveForm::end() ?>
            
            <!-- Секция со ссылками на слайдеры: -->
            <section id="links">
                <?php foreach ($cookieDocumentsArray as $cookieDocument) { ?>
                    <a href="<?=Yii::$app->urlManager->createUrl(['site/view-slider', 'id' => $cookieDocument->id])?>"><?= Html::encode($cookieDocument->filename) ?></a><br>
                <?php } ?>
            </section>

            </div>
        </div>

    </div>
</div>
