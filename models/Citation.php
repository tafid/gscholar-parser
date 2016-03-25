<?php

namespace app\models;

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
            [['user_id'], 'required'],
            [['user_id'], 'filter', 'filter' => 'trim', 'skipOnArray' => true],
            [['user_id'], 'unique', 'targetAttribute' => ['user_id']],
            [['h_index', 'bib_ref', 'missing'], 'integer'],
            [['updated_at'], 'safe'],
            [['user_id'], 'string', 'max' => 255],
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
        ];
    }

    public function fetchData() {
        $citations = self::find()->orderBy('updated_at ASC')->all(); // ->where(['missing' => 0])
        foreach ($citations as $citation) {
            $scholarPage = $this->getScholarPage($citation->user_id);
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

    private function getScholarPage($user_id)
    {
        $page = SimpleHTMLDom::file_get_html(sprintf('https://scholar.google.com/citations?user=%s&hl=uk', $user_id)); //$user_id
        $result = [];
        try {
            $result['h_index'] = $page->find('#gsc_rsb_st > tbody > .gsc_rsb_std', 0)->plaintext;
            $result['bib_ref'] = $page->find('#gsc_rsb_st > tbody > .gsc_rsb_std', 2)->plaintext;
        } catch (Exception $e) {
//            print $e->getMessage();
            return false;
        }

        return $result;
    }
}
