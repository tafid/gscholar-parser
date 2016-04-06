<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Citations');
$this->params['breadcrumbs'][] = $this->title;
$this->registerCss('.btn {margin-bottom: 5px;');
$this->registerJs(<<<JS
// Refresh data
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
    $('.overlay').hide();
    // Scroll to Grid
    $('html, body').animate({ scrollTop: $('#citation-gridview').offset().top - 53}, 'fast');
});
JS
)
?>
<div class="citation-index" style="margin-bottom: 1em">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->session->hasFlash('info')) : ?>
        <!-- Modal -->
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel"><?= Yii::t('app', 'The lines that could not be imported') ?></h4>
                    </div>
                    <div class="modal-body">
                        <p><?= Yii::$app->session->getFlash('info') ?></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('app', 'Close') ?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php $this->registerJs('$(\'#myModal\').modal(\'show\')') ?>
    <?php endif; ?>
        <div class="row" style="margin-bottom: 1em">
            <div class="col-md-12">
                <?php if (Yii::$app->session->hasFlash('error')) : ?>
                    <div class="alert alert-danger" role="alert">
                        <p><?= Yii::$app->session->getFlash('error') ?></p>
                    </div>
                <?php endif; ?>
                <div class="well well-sm">
                    <fieldset>
                        <legend><?= Yii::t('app', 'Import users from file') ?></legend>
                        <?php $form = ActiveForm::begin([
                            'id' => 'import-data-form',
                            'action' => Url::toRoute(['import-data']),
                            'options' => ['enctype' => 'multipart/form-data']
                        ]); ?>
                        <?= $form->field($model, 'file')->fileInput()->hint(Yii::t('app', 'Select the file with IDs users to import. File size should not exceed 1MB.')) ?>
                        <div class="form-group">
                            <?= Html::submitButton(Yii::t('app', 'Upload'), ['class' => 'btn btn-primary']) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </fieldset>
                </div>
            </div>
            <div id="citation-buttons" class="col-md-12">
                <?= Html::a('<i class="glyphicon glyphicon-plus"></i>&nbsp; ' . Yii::t('app', 'Add user'), ['create'], ['class' => 'btn btn-success visible-lg-inline-block visible-md-inline-block visible-sm-inline-block visible-xs-block']) ?>
                <?= Html::a('<i class="glyphicon glyphicon-floppy-save"></i>&nbsp; ' . Yii::t('app', 'Export data'), ['export'], ['class' => 'btn btn-info visible-lg-inline-block visible-md-inline-block visible-sm-inline-block visible-xs-block']) ?>
                <?= Html::a('<i class="fa fa-refresh"></i>&nbsp; ' . Yii::t('app', 'Fetch data'), ['export'], [
                    'id' => 'refresh-data',
                    'class' => 'btn btn-warning visible-lg-inline-block visible-md-inline-block visible-sm-inline-block visible-xs-block',
                    'data-loading-text' => '<i class="fa fa-refresh fa-spin"></i>&nbsp;&nbsp;' . Yii::t('app', 'Loading') . '...',
                    'data-complete-text' => '<i class="fa fa-refresh"></i>&nbsp; ' . Yii::t('app', 'Fetch data')
                ]) ?>
            </div>
    </div>
    <?php Pjax::begin(['id' => 'citation-grid', 'scrollTo' => true, 'timeout' => 0]) ?>
    <div class="overlay" style="display: none">
        <i class="fa fa-refresh fa-spin"></i>
    </div>
    <?= GridView::widget([
        'id' => 'citation-gridview',
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
                    ['class' => 'form-control', 'prompt' => '--',  'data-toggle' => 'select']
                ),
            ],
            'updated_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
