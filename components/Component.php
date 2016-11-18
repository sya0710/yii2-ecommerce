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
	 * @param primary key of product
	 */
	public $primary_key = 'id';

	/**
	 * @param title of product
	 */
	public $title = 'title';

	/**
	 * @param price buy of product
	 */
	public $price = 'price';

	/**
	 * @param special price of product
	 */
	public $old_price = 'old_price';

	/**
	* Function get number product had add to cart
	*/
	public function getNumberItemCart(){
		return Yii::$app->session->get('numberItemCart');
	}

	public function getCartId(){
		return Yii::$app->session->get('quote_id');
	}

	/**
	 * Function add product to cart
	 * @param array $product_info infomation of product $product_info[$primary_key_value] = [
	 * 	$primary_key => '',
	 *	$title => '',
	 *	$price => '',
	 *	$old_price => '',
	 *	'quantity' => 1
	 * ]
	 * @return bolean
	 */
	public function addCart($product_info = []){
		if (empty($product_info))
			return false;

		$quote_id = Quote::getQuoteId();

		// Get cart info
		$cart = $this->findCart($quote_id);

		// Add or update item to cart
		$update_cart = $cart->updateCart($product_info);

		return $update_cart;
	}

	/**
	* Function get info cart
	*/
	public function getCart(){
		$quote_id = Quote::getQuoteId();

		$model = $this->findCart($quote_id);

		return $model;
	}

	public function createOrder($product_info, $customer_info){
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
		$order->payment = 'pay_at_home';
		$saveOrder = $order->save();

		if ($saveOrder){
			Yii::$app->session->set('numberItemCart', 0);
			Yii::$app->session->set('quote_id', null);
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
