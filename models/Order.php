<?php

namespace sya\ecommerce\models;

use Yii;
use yii\base\Model;
use yii\bootstrap\Html;
use sya\ecommerce\Ecommerce;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use sya\ecommerce\helpers\SyaHelper;

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
 * @property mixed $product infomation of product when shopping
 * - id: id of product.
 * - sku: sku of product.
 * - price: price of product when ordering at that time. (No change when price of product change).
 * - quantity: quantity order of product.
 * - is_marketing: promotional products or not at the time. (No change)
 * @property mixed $product_text
 * @property mixed $shipping
 * @property mixed $customer infomation of customer when shopping
 * @property mixed $payment
 * @property mixed $note_customer
 * @property mixed $note_admin
 * @property mixed $note_admin_content
 * @property mixed $log
 * @property mixed $quote_id
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
    public function afterSave($insert, $changedAttributes) {
        $attributes = array_keys($this->getAttributes());

        // Begin generate code active product
        $activation_code = $this->_checkCode();

        $user_id = !Yii::$app->user->isGuest ? Yii::$app->user->id : ArrayHelper::getValue($this->customer, 'id');

        if (!empty($activation_code)) {
            foreach ($activation_code as $product_id_activation => $code_activation) {
                $code_order = new ActivationCode;
                $code_order->code = $code_activation;
                $code_order->product_id = $product_id_activation;
                $code_order->user_id = $user_id;
                $code_order->order_id = $this->_id;
                $code_order->status_code = "0";
                $code_order->save();
            }
        }
        // End generate code active product

        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Function generate code active product
     * @param  array  $activation_code Array code and product
     * @return array  Array code and product
     */
    private function _checkCode($activation_code = []){
        // Get model name of product
        $ecommerce = Ecommerce::module();

        if (!$ecommerce->enableActivitionCode)
            return [];

        $user_id = ArrayHelper::getValue($this->customer, 'id');

        foreach ($this->product as $product_id => $item) {
            $activation_code[$product_id] = substr(md5("order".uniqid().$user_id.$this->_id.$product_id), 0, 12);
        }

        // Get all code for product_id had generate
        $arrCode = array_values($activation_code);

        // Check code exits
        $codes = ActivationCode::find()->select(['code', 'product_id'])->where(['code' => $arrCode])->all();

        if (!empty($codes)) {
            foreach ($codes as $item_code) {
                unset($activation_code[$item_code['product_id']]);
                $activation_code += $this->_checkCode($activation_code);
            }
        }

        return $activation_code;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(BaseOrder::attributeLabels(), [
            'quote_id' => Yii::t('ecommerce', 'Quote Id'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        $data[] = 'quote_id';
        return array_merge(parent::attributes(), $data);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $data = [
            [['quote_id'], 'safe']
        ];
        return array_merge(parent::rules(), $data);
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return array_merge(Model::scenarios(), [
            'search' => ['ecommerce_id', 'creator', 'created_at', 'updater', 'updated_at', 'status', 'product', 'product_text', 'shipping', 'customer', 'payment', 'note_customer', 'note_admin', 'note_admin_content', 'quote_id', 'log'],
            'default' => ['ecommerce_id', 'creator', 'created_at', 'updater', 'updated_at', 'status', 'product', 'product_text', 'shipping', 'customer', 'payment', 'note_customer', 'note_admin', 'note_admin_content', 'log', 'quote_id'],
            'create' => ['ecommerce_id', 'creator', 'created_at', 'updater', 'updated_at', 'status', 'product', 'product_text', 'shipping', 'customer', 'payment', 'note_customer', 'note_admin', 'note_admin_content', 'log', 'quote_id'],
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
                '$ne' => ''
            ],
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

        // Get Module in ecommerce
        $ecommerce = Ecommerce::module();

        // Customer field
        $customerField = ArrayHelper::getValue($ecommerce->customerTable, 'fieldOrder');
        
        $query = SyaHelper::addMongoFilter($query, 'ecommerce_id', $this->ecommerce_id);
        $query = SyaHelper::addMongoFilter($query, 'status', $this->status);
        $query = SyaHelper::addMongoFilter($query, 'customer', $this->customer, 'or', $customerField);
        $query = SyaHelper::addMongoFilter($query, 'product_text', $this->product_text, 'like', $customerField);
        $query = SyaHelper::addMongoFilter($query, 'quote_id', $this->quote_id, 'like');

        if (!empty($this->created_at)){
            list($minDate, $maxDate) = explode(' to ', $this->created_at);
            $min_date = strtotime($minDate . ' 00:00:00');
            $max_date = strtotime($maxDate . ' 23:59:59');
            $query = SyaHelper::addMongoFilter($query, 'created_at', [$min_date, $max_date], 'between');
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
    public function generateProductOrder($products = [], $shipping = 0){
        if (!empty($products)) {
            // IF shipping is not number then assign value shipping = 0
            if (!is_integer(intval($shipping)))
                $shipping = 0;
                
            // Get model name of product
            $ecommerce = Ecommerce::module();
            $modelName = end(explode('\\', $ecommerce->itemModule));
            $columnProduct = ArrayHelper::getValue($ecommerce->productTable, 'fieldOrder');

            // Begin list product order
            $template = Html::beginTag('div', ['class' => 'table-responsive']);
                $template .= Html::beginTag('table', ['class' => 'table table-striped']);

                // Begin header table
                    $template .= Html::beginTag('thead');
                        $template .= Html::beginTag('tr');
                            $template .= Html::beginTag('th');
                                $template .= Yii::t('ecommerce', 'ID');
                            $template .= Html::endTag('th');
                            foreach ($columnProduct as $column => $item) {
                                $template .= Html::beginTag('th');
                                    $template .= Yii::t('ecommerce', ucfirst($column));
                                $template .= Html::endTag('th');
                            }

                            $template .= Html::beginTag('th');
                                $template .= Yii::t('ecommerce', 'Quantity');
                            $template .= Html::endTag('th');
                            $template .= Html::beginTag('th', ['colspan' => 2]);
                                $template .= Yii::t('ecommerce', 'Total Price');
                            $template .= Html::endTag('th');

                            if ($ecommerce->enableActivitionCode){
                                $template .= Html::beginTag('th');
                                    $template .= Yii::t('ecommerce', 'Activation Code');
                                $template .= Html::endTag('th');
                            }
                        $template .= Html::endTag('tr');
                    $template .= Html::endTag('thead');
                // End header table

                // Begin list product
                    $template .= Html::beginTag('tbody');
                        $sumTotal = 0;
                        foreach ($products as $product) {
                            // Get value in product
                            $id = ArrayHelper::getValue($product, 'id', '');
                            $sku = ArrayHelper::getValue($product, 'sku', '');
                            $title = ArrayHelper::getValue($product, 'title', '');
                            $price = ArrayHelper::getValue($product, 'price', 0);
                            $quantity = ArrayHelper::getValue($product, 'quantity', 1);
                            $is_marketing = ArrayHelper::getValue($product, 'is_marketing', '1');
                            $activationCode = ActivationCode::find()->select(['code'])->where(['product_id' => $id, 'order_id' => Yii::$app->request->get('id')])->one();
                            $total = $price * $quantity;
                            $sumTotal += $total; 

                            $template .= Html::beginTag('tr');
                                $template .= Html::beginTag('td', ['class' => 'text-vertical']);
                                    $template .= Html::tag('span', $id, ['class' => 'product_id']);
                                    $template .= Html::hiddenInput($modelName . '[product][' . $id . '][id]', $id, ['class' => 'form-control', 'readonly' => true]);
                                $template .= Html::endTag('td');
                                foreach ($columnProduct as $column => $item) {
                                    $template .= Html::beginTag('td', ['class' => 'text-vertical']);
                                        $value = $valueColumn = ArrayHelper::getValue($product, $column, '');
                                        if ('price' == $column){
                                            $value = ArrayHelper::getValue($product, $column, 0);
                                            $valueColumn = Yii::$app->formatter->asDecimal($value, 0) . ' VNĐ';
                                        }

                                        $template .= $valueColumn;
                                        $template .= Html::hiddenInput($modelName . '[product][' . $id . '][' . $column . ']', $value, ['class' => 'form-control', 'readonly' => true]);
                                    $template .= Html::endTag('td');
                                }
                                
                                $template .= Html::beginTag('td', ['style' => 'width: 5%;']);
                                    if ($ecommerce->multiple){
                                        $template .= Html::textInput($modelName . '[product][' . $id . '][quantity]', $quantity, ['class' => 'form-control product_qty text-center', 'onkeyup' => 'return totalPriceProduct(this);']);
                                    } else {
                                        $template .= $quantity;
                                        $template .= Html::hiddenInput($modelName . '[product][' . $id . '][quantity]', $quantity, ['class' => 'form-control product_qty text-center', 'onkeyup' => 'return totalPriceProduct(this);']);
                                    }
                                $template .= Html::endTag('td');

                                $template .= Html::beginTag('td', ['class' => 'text-vertical', 'colspan' => 2]);
                                    $template .= Html::tag('span', Yii::$app->formatter->asDecimal($total, 0) . ' VNĐ', ['class' => 'product_total', 'data-total' => $total]);
                                $template .= Html::endTag('td');

                                if ($ecommerce->enableActivitionCode){
                                    $template .= Html::beginTag('td', ['class' => 'text-vertical']);
                                        $template .= ArrayHelper::getValue($activationCode, 'code', null);
                                    $template .= Html::endTag('td');
                                }
                            $template .= Html::endTag('tr');
                        }

                        $colspan = count($columnProduct) + 3;
                        if (!$ecommerce->enableActivitionCode){
                            $colspan = count($columnProduct) + 2;
                        }
                        
                        // Begin shipping
                        $template .= Html::beginTag('tr');
                            $template .= Html::beginTag('td', ['colspan' => $colspan, 'class' => 'text-right', 'style' => 'vertical-align: middle;']);
                                $template .= Yii::t('ecommerce', 'Shipping') . ': ';
                            $template .= Html::endTag('td');
                            $template .= Html::beginTag('td', ['width' => '100px']);
                                $template .= Html::textInput('shipping', Yii::$app->formatter->asDecimal($shipping, 0), ['class' => 'form-control pull-left', 'onkeyup' => 'return addShipping(this);']);
                                $template .= Html::hiddenInput($modelName . '[shipping]', $shipping, ['id' => 'syaShipping','class' => 'form-control product_total', 'readonly' => true, 'data-total' => $shipping]);
                            $template .= Html::endTag('td');
                            $template .= Html::beginTag('td', ['style' => 'vertical-align: middle;']);
                                $template .= ' VNĐ';
                            $template .= Html::endTag('td');
                        $template .= Html::endTag('tr');
                        // End shipping
                        
                        // Begin total product
                        $template .= Html::beginTag('tr');
                            $template .= Html::beginTag('td', ['colspan' => $colspan, 'class' => 'text-right']);
                                $template .= Yii::t('ecommerce', 'Total') . ': ';
                            $template .= Html::endTag('td');
                            $template .= Html::beginTag('td', ['colspan' => '2']);
                                $template .= Html::tag('span', Yii::$app->formatter->asDecimal($sumTotal + $shipping, 0), ['id' => 'product_total']) . ' VNĐ';
                            $template .= Html::endTag('td');
                        $template .= Html::endTag('tr');
                        // End total product
                        
                    $template .= Html::endTag('tbody');
                // End list product

                $template .= Html::endTag('table');
                // End list product order
                
            $template .= Html::endTag('div'); // End table-responsive
            
            return $template;
        }
        
        return null;
    }

    /**
     * Function generate customer infomation
     * @return string
     */
    public function generateCustomerOrder(){
        $ecommerce = Ecommerce::module();
        
        // Customer field
        $customerField = ArrayHelper::getValue($ecommerce->customerTable, 'fieldOrder');
        
        // Model order
        $modelOrder = end(explode('\\', $ecommerce->itemModule));
        
        $template = '';
        foreach ($customerField as $filedCustomerOrder => $fieldCustomerTable) {
            $placeHolder = Yii::t('ecommerce', ucwords(str_replace('_', ' ', $filedCustomerOrder)));
            $template .= Html::textInput($modelOrder . '[customer][' . $filedCustomerOrder . ']', ArrayHelper::getValue($this->customer, $filedCustomerOrder, ''), ['class' => 'form-control m-b customer_input_' . $fieldCustomerTable, 'placeHolder' => $placeHolder]);
        }
        
        return $template;
    }
    
    /**
     * Function generate note admin
     * @return string
     */
    public function generateNoteAdmin(){
        $template = Html::beginTag('div', ['class' => 'feed-activity-list']);
        
        if (is_array($this->note_admin) AND ! empty($this->note_admin)): 
            foreach ($this->note_admin as $note_admin):
                $admin = ArrayHelper::getValue($note_admin, 'creator');
                $adminName = ArrayHelper::getValue($note_admin, 'creator_name');
                $content = ArrayHelper::getValue($note_admin, 'content');
                $created_at = ArrayHelper::getValue($note_admin, 'created_at');
                
                // Get namespace of model
                $ecommerce = Ecommerce::module();

                // User field
                $linkUser = ArrayHelper::getValue($ecommerce->userTable, 'linkInfo');
                $idUser = ArrayHelper::getValue($ecommerce->userTable, 'idField');
                
                $template .= Html::beginTag('div', ['class' => 'feed-element']);
                    $template .= Html::beginTag('div');
                        $template .= Html::tag('small', Yii::$app->formatter->asRelativeTime($created_at), ['class' => 'pull-right text-navy']);
                        $template .= Html::a(Html::tag('strong', $adminName), Url::to([$linkUser, $idUser => $admin]));
                        $template .= Html::tag('div', $content);
                        $template .= Html::tag('small', date('l h:i:s a \- d.m.Y', $created_at));
                    $template .= Html::endTag('div');
                $template .= Html::endTag('div');
            endforeach;
        endif;
        
        $template .= Html::endTag('div');
        
        return $template;
    }
    
    /**
     * Function generate log order
     * @return string
     */
    public function generateLogOrder(){
        $template = null;
        if (is_array($this->log) AND !empty($this->log)){
            foreach ($this->log as $log){
                // Declare infomation log
                $created_at = ArrayHelper::getValue($log, 'created_at', null);
                $action = ArrayHelper::getValue($log, 'action');
                $note = ArrayHelper::getValue($log, 'note');
                $creator = ArrayHelper::getValue($log, 'creator');
                $logCreator = ArrayHelper::getValue($log, 'creator_name');
                
                // Get namespace of model
                $ecommerce = Ecommerce::module();

                // User field
                $linkUser = ArrayHelper::getValue($ecommerce->userTable, 'linkInfo');
                $idUser = ArrayHelper::getValue($ecommerce->userTable, 'idField');
                
                $template .= Html::beginTag('div', ['class' => 'timeline-item']);
                    $template .= Html::beginTag('div', ['class' => 'row']);
                        $template .= Html::beginTag('div', ['class' => 'col-xs-3 date']);
                            $template .= Html::tag('i', '', ['class' => 'fa ' . ArrayHelper::getValue(\sya\ecommerce\Module::$logStatus, $action)]);
                                $template .= !empty($created_at) ? date('H:i a', $created_at) : null;
                                $template .= Html::tag('br');
                                $template .= Html::tag('small', Yii::$app->formatter->asRelativeTime($created_at), ['class' => 'text-navy']);
                        $template .= Html::endTag('div');
                        $template .= Html::beginTag('div', ['class' => 'col-xs-7 content no-top-border']);
                                $template .= Html::tag('p', Html::tag('strong', Yii::t('ecommerce', ucfirst($action))), ['class' => 'm-b-xs']);
                                $template .= Html::tag('p', Html::a($logCreator, Url::to([$linkUser, $idUser => $creator])) . ' ' . Yii::t('ecommerce', ucfirst($action)) . ' ' . Yii::t('ecommerce', 'Order') . ' ' . $this->generateNote($note));
                        $template .= Html::endTag('div');
                    $template .= Html::endTag('div');
                $template .= Html::endTag('div');
            }
        }
        
        return $template;
    }

    private function generateNote($note){
        $patterns = [
            '/{follow}/',
            '/{of}/',
            '/{from}/',
            '/{to}/',
            '/({delete}|{Delete})/',
            '/({create}|{Create})/',
        ];

        $replace = [
            Yii::t('ecommerce', 'width change follow') . ': ',
            Yii::t('ecommerce', 'of'),
            Yii::t('ecommerce', 'from'),
            Yii::t('ecommerce', 'to'),
            Yii::t('yii', 'Delete'),
            Yii::t('ecommerce', 'Create'),
        ];

        // Search and replace attribute in ecommerce
        $attributes = Order::attributes();

        foreach ($attributes as $attribute) {
            $patterns[] = '/{attribute_' . $attribute . '}/';
            $replace[] = ucfirst(Order::getAttributeLabel($attribute));
        }

        // Search and replace language in ecommerce
        preg_match_all('/{ecommerce_(\w)+}/', $note, $matches);

//        var_dump($matches[0]);die;

        if (isset($matches[0])) {
            foreach ($matches[0] as $match) {
                if (strstr($match, 'ecommerce_')) {
                    $patterns[] = '/' . $match . '/';
                    $replace[] = Yii::t('ecommerce', Order::getAttributeLabel(preg_replace(['/{ecommerce_/', '/}/'], '', $match)));
                }
            }
        }

        return preg_replace($patterns, $replace, $note);
    }

    public function checkQuantityProductOrder() {

    }
}