<?php

namespace sya\ecommerce\models;

use Yii;

/**
 * This is the model class for collection "quote".
 *
 * @property \MongoId|string $_id
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property mixed $product
 * @property mixed $payment
 * @property mixed $note_customer
 */
class BaseQuote extends \sya\ecommerce\components\ActiveRecordMongo
{
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'quote';
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id',
            'created_at',
            'updated_at',
            'product',
            'payment',
            'note_customer',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'product', 'payment', 'note_customer'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => Yii::t('ecommerce', 'ID'),
            'created_at' => Yii::t('ecommerce', 'Created At'),
            'updated_at' => Yii::t('ecommerce', 'Updated At'),
            'product' => Yii::t('ecommerce', 'Product'),
            'payment' => Yii::t('ecommerce', 'Payment'),
            'note_customer' => Yii::t('ecommerce', 'Note Customer'),
        ];
    }
}