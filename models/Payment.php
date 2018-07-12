<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "payment".
 *
 * @property int $id
 * @property string $email
 * @property int $sum
 * @property string $currency
 * @property int $source
 * @property int $created_at
 */
class Payment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email', 'created_at'], 'required'],
            [['sum', 'source', 'created_at'], 'integer'],
            [['email'], 'string', 'max' => 255],
            [['currency'], 'string', 'max' => 3],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => 'Email',
            'sum' => 'Sum',
            'currency' => 'Currency',
            'source' => 'Source',
            'created_at' => 'Created At',
        ];
    }
}
