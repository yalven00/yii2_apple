<?php

use common\models\Apple;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model Apple */

$colorClasses = [
    Apple::COLOR_GREEN => 'bg-success',
    Apple::COLOR_RED => 'bg-danger',
    Apple::COLOR_YELLOW => 'bg-warning',
];

$statusClasses = [
    Apple::STATUS_ON_TREE => 'success',
    Apple::STATUS_ON_GROUND => 'warning',
    Apple::STATUS_ROTTEN => 'danger',
    Apple::STATUS_EATEN => 'secondary',
];

$statusTexts = [
    Apple::STATUS_ON_TREE => 'На дереве',
    Apple::STATUS_ON_GROUND => 'На земле',
    Apple::STATUS_ROTTEN => 'Испорчено',
    Apple::STATUS_EATEN => 'Съедено',
];

$colorTexts = [
    Apple::COLOR_GREEN => 'Зеленое',
    Apple::COLOR_RED => 'Красное',
    Apple::COLOR_YELLOW => 'Желтое',
];

// Безопасное получение значений
$colorClass = $colorClasses[$model->color] ?? 'bg-secondary';
$colorText = $colorTexts[$model->color] ?? $model->color;
$statusClass = $statusClasses[$model->status] ?? 'secondary';
$statusText = $statusTexts[$model->status] ?? $model->status;
?>

<div class="card h-100">
    <div class="card-header <?= $colorClass ?> text-white">
        <h5 class="card-title mb-0">
            Яблоко #<?= $model->id ?>
            <span class="badge bg-light text-dark float-end">
                <?= $colorText ?>
            </span>
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-6">
                <strong>Статус:</strong><br>
                <span class="badge badge-<?= $statusClass ?>">
                    <?= $statusText ?>
                </span>
            </div>
            <div class="col-6">
                <strong>Осталось:</strong><br>
                <span class="badge badge-info">
                    <?= $model->getRemainingPercent() ?>%
                </span>
            </div>
        </div>
        
        <hr>
        
        <div class="mb-2">
            <strong>Создано:</strong> <?= $model->getCreatedAtText() ?>
        </div>
        
        <?php if ($model->fallen_at): ?>
            <div class="mb-2">
                <strong>Упало:</strong> <?= date('d.m.Y H:i', $model->fallen_at) ?>
            </div>
        <?php endif; ?>
        
        <?php 
        $timeToRot = $model->getTimeToRot();
        if ($timeToRot !== null): ?>
            <div class="mb-2">
                <strong>Испортится через:</strong> <?= $timeToRot ?>
            </div>
        <?php elseif ($model->status === Apple::STATUS_ROTTEN): ?>
            <div class="mb-2">
                <strong>Состояние:</strong> <span class="text-danger">Испортилось</span>
            </div>
        <?php endif; ?>
        
        <div class="progress mb-3">
            <div class="progress-bar <?= $colorClass ?>" 
                 role="progressbar" 
                 style="width: <?= $model->getRemainingPercent() ?>%"
                 aria-valuenow="<?= $model->getRemainingPercent() ?>" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
                <?= $model->getRemainingPercent() ?>%
            </div>
        </div>
        
        <div class="btn-group btn-block" role="group">
            <?php if ($model->canFall()): ?>
                <?= Html::a('Уронить', ['fall', 'id' => $model->id], [
                    'class' => 'btn btn-warning',
                    'data' => [
                        'confirm' => 'Уронить яблоко?',
                        'method' => 'post',
                    ],
                ]) ?>
            <?php endif; ?>
            
            <?php if ($model->canEat()): ?>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#eatModal<?= $model->id ?>">
                    Съесть
                </button>
            <?php endif; ?>
            
            <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Удалить яблоко?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>
</div>

<!-- Modal для съедания -->
<div class="modal fade" id="eatModal<?= $model->id ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Съесть яблоко #<?= $model->id ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php $form = ActiveForm::begin([
                'action' => ['eat', 'id' => $model->id],
            ]); ?>
            <div class="modal-body">
                <div class="form-group">
                    <label for="percent<?= $model->id ?>">
                        Сколько съесть (%)? Осталось: <?= $model->getRemainingPercent() ?>%
                    </label>
                    <input type="number" 
                           class="form-control" 
                           id="percent<?= $model->id ?>" 
                           name="percent" 
                           min="1" 
                           max="<?= $model->getRemainingPercent() ?>" 
                           value="25" 
                           required>
                    <small class="form-text text-muted">
                        Введите процент от 1 до <?= $model->getRemainingPercent() ?>
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                <button type="submit" class="btn btn-primary">Съесть</button>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>