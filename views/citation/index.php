<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Citations');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="citation-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('<i class="glyphicon glyphicon-plus"></i>&nbsp; ' . Yii::t('app', 'Add user'), ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('<i class="glyphicon glyphicon-floppy-save"></i>&nbsp; ' . Yii::t('app', 'Export data'), ['export'], ['class' => 'btn btn-info']) ?>
    </p>
    <div class="table-responsive">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => [
                'class' => 'table-condensed table table-striped table-bordered'
            ],
            'columns' => [
                [
                    'attribute' => 'user_id',
                    'enableSorting' => false,
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Html::a($model->user_id, sprintf('https://scholar.google.com.ua/citations?user=%s&hl=uk', $model->user_id), ['target' => '_blank']);
                    }
                ],
                'h_index',
                'bib_ref',
                [
                    'attribute' => 'missing',
                    'enableSorting' => false,
                    'value' => function ($model) {
                        return $model->missing === 0 ? Yii::t('app', 'Present') : Yii::t('app', 'Missing');
                    },
                    'filter' => Html::activeDropDownList(
                        $searchModel,
                        'missing',
                        [1 => Yii::t('app', 'Missing'), 0 => Yii::t('app', 'Present')],
                        ['class' => 'form-control', 'prompt' => '--']
                    ),
                ],
                'updated_at',

                ['class' => 'yii\grid\ActionColumn'],
            ],
        ]); ?>
    </div>
</div>
