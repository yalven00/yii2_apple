<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Apple */

$this->title = 'Яблоко #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Яблоки', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="apple-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Назад', ['index'], ['class' => 'btn btn-default']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'color',
                'value' => $model->getColorText(),
            ],
            [
                'attribute' => 'created_at',
                'value' => $model->getCreatedAtText(),
            ],
            [
                'attribute' => 'fallen_at',
                'value' => $model->getFallenAtText(),
            ],
            [
                'attribute' => 'status',
                'value' => $model->getStatusText(),
            ],
            [
                'attribute' => 'size',
                'value' => $model->getRemainingPercent() . '%',
            ],
            [
                'attribute' => 'rotten_at',
                'value' => $model->rotten_at ? date('d.m.Y H:i', $model->rotten_at) : '-',
            ],
            [
                'attribute' => 'is_deleted',
                'value' => $model->is_deleted ? 'Да' : 'Нет',
            ],
        ],
    ]) ?>

</div>