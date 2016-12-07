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
class ActivationCode extends BaseActivationCode
{

}
