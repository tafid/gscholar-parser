<?php

namespace app\models;

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
}
