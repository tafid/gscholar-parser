<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Citations');
$this->params['breadcrumbs'][] = $this->title;
$this->registerCss('.btn {margin-bottom: 5px;');
$this->registerJs(<<<JS
$(document).on('click', '#refresh-data', function(event) {
    event.preventDefault();
    var refreshDataBtn = $(this);
    refreshDataBtn.button('loading');
    $.post('refresh-data', function(data) {
        if (data.status === true) {
            $.pjax.reload({container:"#citation-grid"});
            refreshDataBtn.button('complete');
        } else {
            alert('Error');
        }
    });

    return false;
});
$(document).on('pjax:send', function() {
    $('.overlay').show()
});
$(document).on('pjax:beforeReplace', function() {
    $('.overlay').hide()
});
JS
)
?>
<div class="citation-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('<i class="glyphicon glyphicon-plus"></i>&nbsp; ' . Yii::t('app', 'Add user'), ['create'], ['class' => 'btn btn-success visible-lg-inline-block visible-md-inline-block visible-sm-inline-block visible-xs-block']) ?>
        <?= Html::a('<i class="glyphicon glyphicon-floppy-save"></i>&nbsp; ' . Yii::t('app', 'Export data'), ['export'], ['class' => 'btn btn-info visible-lg-inline-block visible-md-inline-block visible-sm-inline-block visible-xs-block']) ?>
        <?= Html::a('<i class="fa fa-refresh"></i>&nbsp; ' . Yii::t('app', 'Fetch data'), ['export'], [
            'id' => 'refresh-data',
            'class' => 'btn btn-warning visible-lg-inline-block visible-md-inline-block visible-sm-inline-block visible-xs-block',
            'data-loading-text' => '<i class="fa fa-refresh fa-spin"></i>&nbsp;&nbsp;' . Yii::t('app', 'Loading') . '...',
            'data-complete-text' => '<i class="fa fa-refresh"></i>&nbsp; ' . Yii::t('app', 'Fetch data')
        ]) ?>
    </p>

    <?php Pjax::begin(['id' => 'citation-grid']) ?>
    <div class="overlay" style="display: none">
        <i class="fa fa-refresh fa-spin"></i>
    </div>
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
    <?php Pjax::end(); ?>
</div>
