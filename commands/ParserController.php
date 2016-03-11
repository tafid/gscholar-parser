<?php

namespace app\commands;

use app\models\Citation;
use Exception;
use serhatozles\simplehtmldom\SimpleHTMLDom;
use yii\console\Controller;

/**
 * Class ParserController
 * Usage: php yii parser/parse
 * @package app\commands
 */
class ParserController extends Controller
{
    public function actionParse()
    {
        $citations = Citation::find()->orderBy('updated_at ASC')->all(); // ->where(['missing' => 0])
        foreach ($citations as $citation) {
            $scholarPage = $this->getScholarPage($citation->user_id);
            if ($scholarPage) {
                $citation->h_index = $scholarPage['h_index'];
                $citation->bib_ref = $scholarPage['bib_ref'];
                $citation->missing = 0;
                $citation->save();
            } else {
                $citation->h_index = 0;
                $citation->bib_ref = 0;
                $citation->missing = 1;
                $citation->save();
            }
        }
    }

    private function getScholarPage($user_id)
    {
        $page = SimpleHTMLDom::file_get_html(sprintf('https://scholar.google.com/citations?user=%s&hl=uk', $user_id)); //$user_id
        $result = [];
        try {
            $result['h_index'] = $page->find('#gsc_rsb_st > tbody > .gsc_rsb_std', 0)->plaintext;
            $result['bib_ref'] = $page->find('#gsc_rsb_st > tbody > .gsc_rsb_std', 2)->plaintext;

        } catch (Exception $e) {
//            print 'Exception: ' .  $e->getMessage() . "\n";
            return false;
        }

        return $result;
    }
}
