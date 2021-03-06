<?php

namespace common\models;

use phpDocumentor\Reflection\Types\Null_;
use Yii;
use yii\behaviors\TimestampBehavior;
use common\queue\EmailJob;

/**
 * This is the model class for table "user_notify".
 *
 * @property integer $id
 * @property string $subject
 * @property string $content
 * @property integer $sender_id
 * @property integer $receiver_id
 * @property integer $pid
 * @property integer $job_post_id
 * @property integer $status
 * @property integer $is_read
 * @property integer $send_mail
 * @property integer $created_at
 * @property integer $sender_at
 */
class UserNotify extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_notify';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subject', 'content'], 'required'],
            [['content'], 'string'],
            [['subject'], 'string', 'max' => 1000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'subject' => 'Subject',
            'content' => 'Content',
            'sender_id' => 'Sender ID',
            'receiver_id' => 'Receiver ID',
            'pid' => 'Pid',
            'job_post_id' => 'Job Post ID',
            'status' => 'Status',
            'is_read' => 'Is Read',
            'send_mail' => 'Send Mail',
            'created_at' => 'Created At',
            'sender_at' => 'Sender At',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'updatedAtAttribute' => false
            ]
        ];
    }

    public static function UnreadNotifyCount()
    {
        return UserNotify::find()->where(['receiver_id' => Yii::$app->user->id, 'is_read' => 0])->count();
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->sender_id = Yii::$app->user->id;
        }
        return parent::beforeSave($insert);
    }

    /**
     * 保存之后给用户发送邮件
     * @param bool $insert 「true 表示是新增记录， false 为更新记录」
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
        
            $email = User::findById($this->receiver_id)->email;

            $sender_username = User::findById($this->sender_id)->username;

            Yii::$app->queue->push(new EmailJob([
                'email' => $email,
                'subject' => $sender_username . " sent a new message: " .$this->subject,
                'body' => "Full message: <br>". $this->content . "<br><br><strong>Please log in to send a reply</strong>: <a href='https://membership.nannycare.com'>https://membership.nannycare.com</a>.",
                'category' => self::class,
                'callback_id' => $this->id
            ]));
        }
    }
}
