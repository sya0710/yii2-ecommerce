<?php

namespace sya\ecommerce\components;

use Yii;
use sya\ecommerce\models\Quote;
use sya\ecommerce\models\Order;
use yii\helpers\ArrayHelper;
use sya\ecommerce\Module;
use sya\payment\Payment;
use yii\web\NotFoundHttpException;

class Component extends \yii\base\Component {

	/**
	* Function get number product had add to cart
	* @return int
	*/
	public function getNumberItemCart(){
		return Yii::$app->session->get('numberItemCart', 0);
	}

	/**
	 * Function get id quote
	 * @return string
	 */
	public function getCartId(){
		return Yii::$app->session->get('quote_id', null);
	}

	/**
	* Function get info cart
	* @return object
	*/
	public function getCart(){
		$quote_id = Quote::getQuoteId();

		// Get cart info
		$model = $this->findCart($quote_id);

		return $model;
	}

	/**
	 * Function add product to cart
	 * @param array $product_info infomation of product $product_info['id'] = [
	 * 	'id' => '',
	 *	'title' => '',
	 * 	'price' => '',
	 *	'old_price' => '',
	 *	'quantity' => 1
	 * ]
	 * @return bolean
	 */
	public function addCart($product_info = []){
		if (empty($product_info))
			return false;

		// Get cart info
		$cart = $this->getCart();

		// Add or update item to cart
		$update_cart = $cart->addCart($product_info);

		return $update_cart;
	}

	/**
	 * Function change quantity of product
	 * @param array $product_qty Array product and quantity $product_qty['product_id'] = 1
	 * @return bolean
	 */
	public function updateQty($product_qty = []){
		if (empty($product_qty))
			return;

		// Get cart info
		$cart = $this->getCart();

		// Update quantity of product
		$update_qty = $cart->updateQty($product_qty);

		return $update_qty;
	}

	/**
	 * Function change cart to order
	 * @param  array $product_info  infomation of product
	 * @param  array $customer_info infomation of customer
	 * @param  array $payment_info infomation of payment
	 * @return boolean
	 */
	public function createOrder($product_info, $customer_info, $payment_info = []){
		if (empty($product_info))
			return false;

		$order = new Order;

		$product_text = [];
		foreach ($product_info as $id => $info) {
			$title = ArrayHelper::getValue($info, 'title', null);
			if (!empty($title))
				$product_text[] = $title;
		}

		$order->product_text = !empty($product_text) ? implode($product_text, ',') : null;
		$order->product = $product_info;
		$order->note_customer = ArrayHelper::getValue($customer_info, 'note_customer', null);

		if (isset($customer_info['note_customer']))
			unset($customer_info['note_customer']);

		$order->customer = $customer_info;
		$order->status = Module::STATUS_NEW;

		if (empty($payment_info))
			$order->payment = ['status' => Payment::STATUS_PAYATHOME];
		else {
			$payment['status'] = ArrayHelper::getValue($payment_info, 'status');
			$payment['infomation'] = ArrayHelper::getValue($payment_info, 'infomation');
			$order->payment = $payment;
		}

		$order->shipping = '0';
		$order->quote_id = $this->getCartId();
		$saveOrder = $order->save();

		if ($saveOrder){
			Yii::$app->session->remove('numberItemCart');
			Yii::$app->session->remove('quote_id');
		}

		return $saveOrder;
	}

	/**
     * Finds the model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findCart($id){
        if (($model = Quote::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
}
