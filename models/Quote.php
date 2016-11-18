<?php

namespace sya\ecommerce\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

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
class Quote extends BaseQuote
{
    /**
     * Function get or create id of quote when customer add to cart
     */
    public static function getQuoteId(){
        $quote_id = Yii::$app->session->get('quote_id');
        if (empty($quote_id)) {
            $quote = new self;
            $quote->save();
            $quote_id = $quote->_id;
            Yii::$app->session->set('quote_id', $quote->_id);
        }

        return $quote_id;
    }

    /**
     * @param array $product_info infomation of product $product_info[$primary_key_value] = [
     *   $primary_key => '',
     *   $title => '',
     *   $price => '',
     *   $special_price => '',
     *   'quantity' => 1
     * ]
     * @return bolean
     */
    public function addCart($product_info = []){
        if (empty($product_info))
            return false;

        $arrProduct = !empty($this->product) ? $this->product : [];
        if (!empty($arrProduct)){
            // Get id of product add to cart
            $arrIdProduct = array_keys($product_info);

            foreach ($arrProduct as $id => $info) {
                // Check product had cart
                if (in_array($id, $arrIdProduct)) {
                    // Qty of product add to cart
                    $qty_product_add = (int) ArrayHelper::getValue($product_info, $id . '.quantity', 1);

                    // Update qty product had in cart
                    $arrProduct[$id]['quantity'] = isset($arrProduct[$id]['quantity']) ? (int) $arrProduct[$id]['quantity'] + $qty_product_add : $qty_product_add;

                    // Remove product had in cart
                    unset($product_info[$id]);
                }
            }
        }

        $this->product = $arrProduct + $product_info;
        $numberItemCart = 0;
        foreach ($this->product as $id => $info) {
            $qty_product = (int) ArrayHelper::getValue($info, 'quantity', 0);
            $numberItemCart += $qty_product;
        }

        $saveCart = $this->save();

        if ($saveCart)
            Yii::$app->session->set('numberItemCart', $numberItemCart);

        return $saveCart;
    }

    /**
     * Function update quantity of product
     * @param  array  $product_qty Array quantity of product need change
     * $product_qty = [
     *     idProduct => quantity
     * ]
     * @return object|null quote
     */
    public function updateQty($product_qty = []) {
        if (empty($product_qty))
            return;

        $arrProduct = !empty($this->product) ? $this->product : [];

        if (empty($arrProduct))
            return;

        foreach ($arrProduct as $id => $info) {
            $arrIdProduct = array_keys($product_qty);
            if (!in_array($id, $arrIdProduct))
                continue;

            // Qty of product need change
            $qty_product_change = (int) ArrayHelper::getValue($product_qty, $id, 0);

            $arrProduct[$id]['quantity'] = $qty_product_change;

            // Remove product when quantity = 0
            if ($qty_product_change <= 0)
                unset($arrProduct[$id]);
        }

        $this->product = $arrProduct;
        $numberItemCart = 0;
        foreach ($this->product as $id => $info) {
            $qty_product = (int) ArrayHelper::getValue($info, 'quantity', 0);
            $numberItemCart += $qty_product;
        }

        $saveCart = $this->save();

        if (!$saveCart)
            return;

        Yii::$app->session->set('numberItemCart', $numberItemCart);

        return $this;
    }
}