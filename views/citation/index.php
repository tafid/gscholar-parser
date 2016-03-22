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
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => "<div class='row'></div><div class=\"table-responsive\">{items}</div>\n<div class='row'><div class='col-xs-6'><div class='dataTables_info'>{summary}</div></div>\n<div class='col-xs-6'><div class='dataTables_paginate paging_bootstrap'>{pager}</div></div></div>",
        'tableOptions' => [
            'class' => 'table table-hovertable-condensed'
        ],
        'columns' => [
            [
                'attribute' => 'user_id',
                'enableSorting' => false,
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a($model->user_id, sprintf('https://scholar.google.com.ua/citations?user=%s', $model->user_id), ['target' => '_blank']);
                }
            ],
            'h_index',
            'bib_ref',
            [
                'attribute' => 'missing',
                'format' => 'raw',
                'enableSorting' => false,
                'value' => function ($model) {
                    return $model->missing === 0 ?
                        Html::tag('span', Yii::t('app', 'Present'), ['class' => 'label label-success']) :
                        Html::tag('span', Yii::t('app', 'Missing'), ['class' => 'label label-danger']);
                },
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'missing',
                    [
                        1 => Yii::t('app', 'Missing'),
                        0 => Yii::t('app', 'Present')
                    ],
                    ['class' => 'form-control', 'prompt' => '--']
                ),
            ],
            'updated_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
