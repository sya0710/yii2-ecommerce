<?php

namespace sya\ecommerce\models;

use Yii;
use yii\base\Model;
use yii\helpers\Url;

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
 * @property mixed $product: infomation of product when shopping
 * - id: id of product.
 * - sku: sku of product.
 * - price: price of product when ordering at that time. (No change when price of product change).
 * - quantity: quantity order of product.
 * - is_marketing: promotional products or not at the time. (No change)
 * @property mixed $customer: infomation of customer when shopping
 * - id: id of customer.
 * - buyer: people buy products.
 * - address: address of customer.
 * - phone: phone of customer.
 * - email: email of customer.
 * @property mixed $payment
 * @property mixed $note
 */
class Order extends BaseOrder
{
    // Status
    CONST STATUS_ACTIVE = 'Hiện';
    CONST STATUS_INACTIVE = 'Ẩn';
    
    // Action in ecommerce
    CONST ACTION_INDEX = 'index';
    CONST ACTION_DELETE = 'delete';
    CONST ACTION_UPDATE = 'update';
    CONST ACTION_CREATE = 'create';
    
    // Conllection name
    public static $collectionName = 'order';
    
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return self::$collectionName;
    }
    
    /**
     * @return array Build action default in ecommerce
     */
    public static function buildActions(){
        return [
            self::ACTION_CREATE => Url::to(['/ecommerce/base/create']),
            self::ACTION_INDEX => Url::to(['/ecommerce/base/index']),
            self::ACTION_UPDATE => Url::to(['/ecommerce/base/update']),
            self::ACTION_DELETE => Url::to(['/ecommerce/base/delete']),
        ];
    }


    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return array_merge(Model::scenarios(), [
            'search' => ['ecommerce_id', 'creator', 'created_at', 'updater', 'updated_at', 'status', 'product', 'customer', 'payment', 'note'],
            'default' => ['ecommerce_id', 'creator', 'created_at', 'updater', 'updated_at', 'status', 'product', 'customer', 'payment', 'note']
        ]);
    }
    
    public function search($params, $pageSize = 30) {
        $query = self::find();
        $query->orderBy('created_at DESC');

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        if (!($this->load($params) AND $this->validate())) {
            return $dataProvider;
        }

        return $dataProvider;
    }
}