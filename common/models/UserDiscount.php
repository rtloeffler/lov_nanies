<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_discount".
 *
 * @property integer $user_id
 * @property integer $type
 * @property double $discount
 * @property integer $created_at
 * @property integer $expired_at
 */
class UserDiscount extends \yii\db\ActiveRecord
{
    const TYPE_NANNY = 1;
    const TYPE_FAMILY_POST = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_discount';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'discount'], 'required'],
            ['type', 'default', 'value' => self::TYPE_NANNY],
            ['type', 'in', 'range' => [self::TYPE_FAMILY_POST, self::TYPE_NANNY]],
            ['discount', 'integer', 'min' => 0, 'max' => 100],
            [['user_id', 'created_at'], 'integer'],
            ['expired_at', 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'type' => 'Type',
            'discount' => 'Discount ( % off )',
            'created_at' => 'Created At',
            'expired_at' => 'Expired At',
        ];
    }

    /**
     * 获取所有 nanny 用户折扣
     *
     * @return mixed|null
     */
    public static function getDiscountForAllNannies()
    {
        $model = self::find()
            ->where(['user_id' => 0, 'type' => self::TYPE_NANNY])
            ->andWhere(['>', 'expired_at', time()])
            ->one();
        if ($model) {
            //美式折扣是多少off，例如，30%，对应了中国的打7折
            $off = $model->discount; 
            return $off;
        }
        return null;
    }

    /**
     * 获取单个 nanny 用户折扣（如果不存在，返回所有用户折扣）
     *
     * @param null $user_id
     * @return mixed|null
     */
    public static function getDiscountForOneNanny($user_id = null)
    {
        $user_id = $user_id === null ? Yii::$app->user->id : $user_id;
        $model = self::find()
            ->where(['user_id' => $user_id, 'type' => self::TYPE_NANNY])
            ->andWhere(['>', 'expired_at', time()])
            ->one();
        if ($model) {
            //美式折扣是多少off，例如，30%，对应了中国的打7折
            $off = $model->discount; 
            return $off;
        }
        return self::getDiscountForAllNannies();
    }

    /**
     * 获取所有 family 用户折扣(post)
     *
     * @return mixed|null
     */
    public static function getPostDiscountForAllFamilies()
    {
        $model = self::find()
            ->where(['user_id' => 0, 'type' => self::TYPE_FAMILY_POST])
            ->andWhere(['>', 'expired_at', time()])
            ->one();
        if ($model) {
            //美式折扣是多少off，例如，30%，对应了中国的打7折
            $off = $model->discount;
            return $off;
        }
        return null;
    }

    /**
     * 获取单个 family 用户折扣（如果不存在，返回所有用户折扣）(post)
     *
     * @param null $user_id
     * @return mixed|null
     */
    public static function getPostDiscountForOneFamily($user_id = null)
    {
        $user_id = $user_id === null ? Yii::$app->user->id : $user_id;
        $model = self::find()
            ->where(['user_id' => $user_id, 'type' => self::TYPE_FAMILY_POST])
            ->andWhere(['>', 'expired_at', time()])
            ->one();
        if ($model) {
            //美式折扣是多少off，例如，30%，对应了中国的打7折
            $off = $model->discount;
            return $off;
        }
        return self::getPostDiscountForAllFamilies();
    }
}
