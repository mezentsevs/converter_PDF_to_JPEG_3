<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Слайдер';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php
// Передача массива c адресами изображений в js:
if (isset($slidesArray)) {
    echo "<script type=\"text/javascript\">var slidesArray=[";
    echo htmlentities(join(',', $slidesArray));
    echo "]</script>";
}
?>
<section id="sliderSection">
    <h1><?= Html::encode($this->title) ?></h1>
    <p><?= Html::encode($filename) ?></p>
    <!-- Слайдер: -->
    <figure id="slider">
        <img id="slide" src="" alt="слайд">
        <button id="left" onclick="slider.left();">&laquo; Назад</button>
        <button id="right" onclick="slider.right();">Далее &raquo;</button>
    </figure>
    <a class="btn btn-lg btn-success" href="<?= Url::to(['site/download-set-cookie', 'id' => $id]) ?>">Скачать zip архив</a>
</section>
