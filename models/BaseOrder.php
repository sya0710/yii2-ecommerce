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
 * @property mixed $updated_at
 * @property mixed $status
 * @property mixed $product
 * @property mixed $product_text
 * @property mixed $shipping
 * @property mixed $customer
 * @property mixed $payment
 * @property mixed $note_customer
 * @property mixed $note_admin
 * @property mixed $note_admin_content
 * @property mixed $log
 */
class BaseOrder extends \sya\ecommerce\components\ActiveRecordMongo
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
            'product_text',
            'shipping',
            'customer',
            'payment',
            'note_customer',
            'note_admin',
            'note_admin_content',
            'log',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ecommerce_id', 'creator', 'created_at', 'updater', 'updated_at', 'status', 'product', 'product_text', 'shipping', 'customer', 'payment', 'note_customer', 'note_admin', 'note_admin_content', 'log'], 'safe']
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
            'created_at' => Yii::t('ecommerce', 'Created At'),
            'updater' => Yii::t('ecommerce', 'Updater'),
            'updated_at' => Yii::t('ecommerce', 'Updated At'),
            'status' => Yii::t('ecommerce', 'Status'),
            'product' => Yii::t('ecommerce', 'Product'),
            'product_text' => Yii::t('ecommerce', 'Product Text'),
            'customer' => Yii::t('ecommerce', 'Customer'),
            'payment' => Yii::t('ecommerce', 'Payment'),
            'note_customer' => Yii::t('ecommerce', 'Note Customer'),
            'note_admin' => Yii::t('ecommerce', 'Note Admin'),
            'note_admin_content' => Yii::t('ecommerce', 'Note Admin Content'),
            'log' => Yii::t('ecommerce', 'Log'),
            'shipping' => Yii::t('ecommerce', 'Shipping'),
        ];
    }
}