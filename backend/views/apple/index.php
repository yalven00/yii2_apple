<?php

use common\models\Apple;
use yii\helpers\Html;
use yii\widgets\ListView;
use yii\data\ActiveDataProvider;

/* @var $this yii\web\View */
/* @var $dataProvider ActiveDataProvider */

$this->title = 'Управление яблоками';
$this->params['breadcrumbs'][] = $this->title;

// Регистрируем JS для модальных окон
$this->registerJs('
    $(document).ready(function() {
        $(\'[data-toggle="modal"]\').click(function() {
            var target = $(this).data(\'target\');
            $(target).modal(\'show\');
        });
    });
');
?>
<div class="apple-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row mb-4">
        <div class="col-md-12">
            <?= Html::a('Сгенерировать яблоки', ['generate'], [
                'class' => 'btn btn-success',
                'data' => [
                    'confirm' => 'Сгенерировать случайные яблоки?',
                    'method' => 'post',
                ],
            ]) ?>
            
            <?= Html::a('Очистить все', ['delete-all'], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Удалить все яблоки?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <div class="row">
        <?php if ($dataProvider->getTotalCount() > 0): ?>
            <?php foreach ($dataProvider->getModels() as $model): ?>
                <div class="col-md-4 mb-4">
                    <?= $this->render('_apple_item', ['model' => $model]) ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">
                    Нет яблок. Нажмите "Сгенерировать яблоки" чтобы создать.
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($dataProvider->pagination): ?>
        <div class="row">
            <div class="col-md-12">
                <?= \yii\bootstrap4\LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                ]) ?>
            </div>
        </div>
    <?php endif; ?>
</div>