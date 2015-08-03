<?php

namespace sya\ecommerce\models;

use Yii;

/**
 * This is the model class for collection "order".
 *
 * @property \MongoId|string $_id
 * @property mixed $ecommerce_id
 * @property mixed $creator
 * @property mixed $created_at
 * @property mixed $updater
 * @property mixed $updated_time
 * @property mixed $status
 * @property mixed $product
 * @property mixed $customer
 * @property mixed $payment
 * @property mixed $note
 */
class BaseOrder extends \yii\mongodb\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'order';
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id',
            'ecommerce_id',
            'creator',
            'created_at',
            'updater',
            'updated_at',
            'status',
            'product',
            'customer',
            'payment',
            'note',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ecommerce_id', 'creator', 'created_at', 'updater', 'updated_at', 'status', 'product', 'customer', 'payment', 'note'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => Yii::t('ecommerce', 'ID'),
            'ecommerce_id' => Yii::t('ecommerce', 'Ecommerce Id'),
            'creator' => Yii::t('ecommerce', 'Creator'),
            'created_at' => Yii::t('ecommerce', 'Created Time'),
            'updater' => Yii::t('ecommerce', 'Updater'),
            'updated_at' => Yii::t('ecommerce', 'Updated Time'),
            'status' => Yii::t('ecommerce', 'Status'),
            'product' => Yii::t('ecommerce', 'Product'),
            'customer' => Yii::t('ecommerce', 'Customer'),
            'payment' => Yii::t('ecommerce', 'Payment'),
            'note' => Yii::t('ecommerce', 'Note'),
        ];
    }
}