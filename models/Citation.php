<?php

namespace app\models;

use DiDom\Document;
use Exception;
use serhatozles\simplehtmldom\SimpleHTMLDom;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "citation".
 *
 * @property integer $id
 * @property string $user_id
 * @property integer $h_index
 * @property integer $bib_ref
 * @property integer $missing
 * @property string $update_at
 */
class Citation extends \yii\db\ActiveRecord
{
    public $file;

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => false,
                'value' => new Expression('NOW()'),
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'citation';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // Default scenarios
            [['user_id'], 'required', 'on' => ['insert', 'update']],
            [['user_id'], 'string', 'max' => '20', 'on' => ['insert', 'update']],
            [['user_id'], 'filter', 'filter' => 'trim', 'skipOnArray' => true, 'on' => ['insert', 'update']],
            [['user_id'], 'unique', 'targetAttribute' => ['user_id'], 'on' => ['insert', 'update']],
            [['h_index', 'bib_ref', 'missing'], 'integer', 'on' => ['insert', 'update']],
            [['updated_at'], 'safe', 'on' => ['insert', 'update']],
            [['user_id'], 'string', 'max' => 255, 'on' => ['insert', 'update']],
            ['user_id', 'match', 'pattern' => '/^[a-zA-Z0-9-_]+$/', 'on' => ['insert', 'update'], 'message' => Yii::t('app', 'This ID does not look like the right one for Google Scholar')],

            // Import data from file
            [['file'], 'file', 'extensions' => ['txt', 'csv'], 'checkExtensionByMimeType' => false, 'maxSize' => 1048576, 'on' => ['import-data']],
            [['file'], 'required', 'on' => ['import-data']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'h_index' => Yii::t('app', 'H Index'),
            'bib_ref' => Yii::t('app', 'Bib Ref'),
            'missing' => Yii::t('app', 'Missing'),
            'updated_at' => Yii::t('app', 'Update At'),
            'file' => Yii::t('app', 'File with user IDs'),
        ];
    }

    public function fetchData() {
        $citations = self::find()->orderBy('updated_at ASC')->all();
        foreach ($citations as $citation) {
            $scholarPage = $this->fetchByDiDom($citation->user_id);
            if (is_array($scholarPage) && !in_array(null, $scholarPage)) {
                $citation->h_index = $scholarPage['h_index'];
                $citation->bib_ref = $scholarPage['bib_ref'];
                $citation->missing = 0;
            } else {
                $citation->h_index = 0;
                $citation->bib_ref = 0;
                $citation->missing = 1;
            }
            $citation->save();
        }
    }

    /**
     * SimpleHTMLDom fetch
     * @param $userId
     * @return array|bool
     */
    private function fetchBySimpleHTMLDom($userId)
    {
        $page = SimpleHTMLDom::file_get_html(sprintf('https://scholar.google.com/citations?user=%s&hl=uk', $userId));
        $result = [];
        try {
            $result['h_index'] = $page->find('#gsc_rsb_st > tbody > .gsc_rsb_std', 0)->plaintext;
            $result['bib_ref'] = $page->find('#gsc_rsb_st > tbody > .gsc_rsb_std', 2)->plaintext;
        } catch (Exception $e) {
            return false;
        }

        return $result;
    }

    /**
     * DiDom fetch
     * @param $userId
     * @return bool|array
     */
    private function fetchByDiDom($userId)
    {
        $result = false;
        $url = sprintf('https://scholar.google.com/citations?user=%s&hl=uk', $userId);
        $document = new Document($url, true);
        if ($document->has('#gsc_rsb_st')) {
            $table = $document->find('#gsc_rsb_st')[0]->find('.gsc_rsb_std');
            $result['h_index'] = $table[0]->innerHtml();
            $result['bib_ref'] = $table[2]->innerHtml();
        }

        return $result;
    }

    public static function exportToFile()
    {
        $content = '';
        $rows = Yii::$app->db->createCommand("SELECT * FROM `citation`")->queryAll();
        foreach($rows as $k => $row) {
            $n = 0;
            foreach ($row as $field => $value) {
                if ($field == 'id') continue;
                if ($field == 'updated_at') {
                    $value = (new \DateTime($value))->format('Ymd');
                }
                $result = preg_replace("/\s+/", " ", $value);
                $n++;
                $content .= sprintf("#%d: %s\r\n", $n, $result);
            }
            $content .= "*****\r\n";
        }

        return $content;
    }
}
