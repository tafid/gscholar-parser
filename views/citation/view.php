<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Citation */

$this->title = $model->user_id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Citations'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="citation-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('<i class="glyphicon glyphicon-pencil"></i>&nbsp; ' . Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('<i class="glyphicon glyphicon-trash"></i>&nbsp; ' . Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'attribute' => 'user_id',
                'format' => 'raw',
                'value' => Html::a($model->user_id, sprintf('https://scholar.google.com.ua/citations?user=%s&hl=uk', $model->user_id), ['target' => '_blank'])
            ],
            'h_index',
            'bib_ref',
            [
                'attribute' => 'missing',
                'value' => $model->missing === 0 ? Yii::t('app', 'Present') : Yii::t('app', 'Missing'),
            ],
            'updated_at',
        ],
    ]) ?>

</div>
