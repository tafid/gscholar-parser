<?php

namespace app\commands;

use app\models\Citation;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Class ParserController
 * Usage: php yii parser/parse
 * @package app\commands
 */
class ParserController extends Controller
{
    public function actionParse()
    {
        (new Citation())->fetchData();
         print $this->ansiFormat(PHP_EOL . "Done" . PHP_EOL, Console::FG_YELLOW);
    }
}
