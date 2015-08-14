<?php

namespace sya\ecommerce\models;

use Yii;
use yii\base\Model;
use yii\bootstrap\Html;
use sya\ecommerce\Ecommerce;
use yii\i18n\Formatter;
use yii\helpers\ArrayHelper;

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
 * @property mixed $log
 */
class Order extends BaseOrder
{
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
     * @inheritdoc
     */
    public function scenarios()
    {
        return array_merge(Model::scenarios(), [
            'search' => ['ecommerce_id', 'creator', 'created_at', 'updater', 'updated_at', 'status', 'product', 'customer', 'payment', 'note', 'log'],
            'default' => ['ecommerce_id', 'creator', 'created_at', 'updater', 'updated_at', 'status', 'product', 'customer', 'payment', 'note', 'log'],
            'create' => ['ecommerce_id', 'creator', 'created_at', 'updater', 'updated_at', 'status', 'product', 'customer', 'payment', 'note', 'log'],
        ]);
    }
    
    /**
     * Function list order
     * @param array $params Param search
     * @param int $pageSize The number of products on one page
     * @return \yii\data\ActiveDataProvider
     */
    public function search($params, $pageSize = 30) {
        $query = self::find();
        $query->orderBy('created_at DESC');
        $query->where([
            'status' => [
                '$ne' => \sya\ecommerce\Module::$status['']
            ]
        ]);

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
    
    /**
     * Function generate list product order
     * @param array $products list product [
     *      1 => [
     *          'id' => 1,
     *          'sku' => '123',
     *          'title' => 'sdsd',
     *          'price' => 50000,
     *          'quantity' => 1,
     *          'is_marketing' => '1',
     *      ]
     * ]
     * @return string
     */
    public function generateProductOrder($products = []){
        if (!empty($products)) {
            // Get model name of product
            $ecommerce = Ecommerce::module();
            $modelName = end(explode('\\', $ecommerce->itemModule));

            // Begin list product order
            $template = Html::beginTag('table', ['class' => 'table table-striped']);

            // Begin header table
                $template .= Html::beginTag('thead');
                    $template .= Html::beginTag('tr');
                        $template .= Html::beginTag('th');
                            $template .= Yii::t('ecommerce', 'ID');
                        $template .= Html::endTag('th');
                        $template .= Html::beginTag('th');
                            $template .= Yii::t('ecommerce', 'Sku');
                        $template .= Html::endTag('th');
                        $template .= Html::beginTag('th');
                            $template .= Yii::t('ecommerce', 'Title');
                        $template .= Html::endTag('th');
                        $template .= Html::beginTag('th');
                            $template .= Yii::t('ecommerce', 'Price');
                        $template .= Html::endTag('th');
                        $template .= Html::beginTag('th');
                            $template .= Yii::t('ecommerce', 'Quantity');
                        $template .= Html::endTag('th');
                        $template .= Html::beginTag('th');
                            $template .= Yii::t('ecommerce', 'Total Price');
                        $template .= Html::endTag('th');
                    $template .= Html::endTag('tr');
                $template .= Html::endTag('thead');
            // End header table

            // Begin list product
                $template .= Html::beginTag('tbody');
                    foreach ($products as $product) {
                        // Get value in product
                        $id = ArrayHelper::getValue($product, 'id', '');
                        $sku = ArrayHelper::getValue($product, 'sku', '');
                        $title = ArrayHelper::getValue($product, 'title', '');
                        $price = ArrayHelper::getValue($product, 'price', 0);
                        $quantity = ArrayHelper::getValue($product, 'quantity', 0);
                        $is_marketing = ArrayHelper::getValue($product, 'is_marketing', '1');
                        $total = $price * $quantity;

                        $template .= Html::beginTag('tr');
                            $template .= Html::beginTag('td', ['class' => 'text-vertical']);
                                $template .= $id;
                                $template .= Html::hiddenInput($modelName . '[product][' . $id . '][id]', $id, ['class' => 'form-control', 'readonly' => true]);
                            $template .= Html::endTag('td');
                            $template .= Html::beginTag('td', ['class' => 'text-vertical']);
                                $template .= $sku;
                                $template .= Html::hiddenInput($modelName . '[product][' . $id . '][sku]', $sku, ['class' => 'form-control', 'readonly' => true]);
                            $template .= Html::endTag('td');
                            $template .= Html::beginTag('td', ['class' => 'text-vertical']);
                                $template .= $title;
                            $template .= Html::endTag('td');
                            $template .= Html::beginTag('td', ['class' => 'text-vertical']);
                                $template .= (new Formatter)->asDecimal($price, 0) . ' VNĐ';
                                $template .= Html::hiddenInput($modelName . '[product][' . $id . '][price]', $price, ['class' => 'form-control product_price', 'readonly' => true]);
                            $template .= Html::endTag('td');
                            $template .= Html::beginTag('td');
                                $template .= Html::textInput($modelName . '[product][' . $id . '][quantity]', $quantity, ['class' => 'form-control product_qty', 'onkeyup' => 'return totalPriceProduct(this);']);
                            $template .= Html::endTag('td');
                            $template .= Html::beginTag('td', ['class' => 'text-vertical']);
                                $template .= Html::tag('span', (new Formatter)->asDecimal($total, 0) . ' VNĐ', ['class' => 'product_total', 'data-total' => $total]);
                            $template .= Html::endTag('td');
                        $template .= Html::endTag('tr');
                    }
                $template .= Html::endTag('tbody');
            // End list product

            $template .= Html::endTag('table');
            // End list product order

            // Begin total product
            $template .= Html::beginTag('div', ['class' => 'row']);
                $template .= Html::beginTag('div', ['class' => 'col-sm-12 m-b-xs text-right']);
                    $template .= 'Tổng tiền: ' . Html::tag('span', 0, ['id' => 'product_total']) . ' VNĐ';
                $template .= Html::endTag('div');
            $template .= Html::endTag('div');
            // End total product
            
            return $template;
        }
        
        return null;
    }
}