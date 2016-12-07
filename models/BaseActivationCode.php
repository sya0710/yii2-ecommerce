<?php

namespace sya\ecommerce\models;

use Yii;

/**
 * This is the model class for collection "activation_code".
 *
 * @property \MongoDB\BSON\ObjectID|string $_id
 * @property mixed $code
 * @property mixed $user_id
 * @property mixed $product_id
 * @property mixed $order_id
 * @property mixed $status_code
 */
class BaseActivationCode extends \sya\ecommerce\components\ActiveRecordMongo
{
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'activation_code';
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id',
            'code',
            'user_id',
            'product_id',
            'order_id',
            'status_code',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'user_id', 'product_id', 'order_id', 'status_code'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => Yii::t('ecommerce', 'ID'),
            'code' => Yii::t('ecommerce', 'Code'),
            'user_id' => Yii::t('ecommerce', 'User ID'),
            'product_id' => Yii::t('ecommerce', 'Product ID'),
            'order_id' => Yii::t('ecommerce', 'Order ID'),
            'status_code' => Yii::t('ecommerce', 'Status'),
        ];
    }
}
