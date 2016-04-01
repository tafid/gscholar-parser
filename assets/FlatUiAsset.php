<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FlatUiAsset extends AssetBundle
{
    public $sourcePath = '@bower/flat-ui/dist';

    public $css = [
        'css/flat-ui.min.css',
    ];
    public $js = [
        'js/flat-ui.min.js',
        'js/radiocheck.js',
    ];
    public $depends = [
        'yii\bootstrap\BootstrapAsset'
    ];
}
