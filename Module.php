<?php
namespace sya\ecommerce;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class Module extends \yii\base\Module{
    // Module name
    CONST MODULE = 'ecommerce';
    
    // Status
    CONST STATUS_ACTIVE = 'Active';
    CONST STATUS_INACTIVE = 'In Active';
    
    // Action in ecommerce
    CONST ACTION_INDEX = 'index';
    CONST ACTION_DELETE = 'delete';
    CONST ACTION_UPDATE = 'update';
    CONST ACTION_CREATE = 'create';
    
    // List Status Order
    CONST STATUS_EMPTY = '';
    CONST STATUS_NEW = 'new';
    CONST STATUS_PROCESS = 'processing';
    CONST STATUS_PENDING = 'pending';
    CONST STATUS_GOOSHAD = 'goods_had';
    CONST STATUS_MOVE = 'moved_to_shop';
    CONST STATUS_DELIVERY = 'delivery';
    CONST STATUS_PAID = 'paid';
    CONST STATUS_COMPLETE = 'complete';
    CONST STATUS_CANCEL = 'cancel';
    CONST STATUS_CLOSE = 'close';
    
    /**
     * Status of order
     */
    public static $status;
    
    /**
     * Log status action order
     */
    public static $logStatus = [
        'create' => 'fa-plus',
        'update' => 'fa-edit',
        'delete' => 'fa-remove'
    ];
    
    /**
     * @var array the default configuration settings for the order widget
     */
    public $itemSettings = [
        'itemSettings' => [],
    ];
    
    /**
     * @var string namespace of order
     */
    public $itemModule = '\sya\ecommerce\models\Order';
    
    /**
     * @var array product config in your table product
     */
    public $productTable = [];
    
    /**
     * @var array User infomation in your table user
     */
    public $userTable = [
        'nameField' => 'p_username',
        'idField' => 'id',
        'linkInfo' => '/account/backend/default/update',
    ];
    
    /**
     * @var array customer config in your table customer
     */
    public $customerTable = [];
    
    /**
     * @inherit doc
     */
    public function init()
    {
        parent::init();

        Yii::setAlias('syaEcommerce', '@vendor/sya/yii2-ecommerce');

        // Setup params itemSettings
        $item = ArrayHelper::getValue($this->itemSettings, 'itemSettings', []);
        $actions = ArrayHelper::getValue($item, 'actions', []);
        $actions += [
            self::ACTION_CREATE => Url::to(['/ecommerce/base/create']),
            self::ACTION_INDEX => Url::to(['/ecommerce/base/index']),
            self::ACTION_UPDATE => Url::to(['/ecommerce/base/update']),
            self::ACTION_DELETE => Url::to(['/ecommerce/base/delete']),
        ];
        
        $this->itemSettings['itemSettings']['actions'] = $actions;
        
        // Setup params status
        self::$status = [
            self::STATUS_EMPTY => Yii::t('ecommerce', '-- Chose Status --'),
            self::STATUS_NEW => Yii::t('ecommerce', 'New'),
            self::STATUS_PROCESS => Yii::t('ecommerce', 'Processing'),
            self::STATUS_PENDING => Yii::t('ecommerce', 'Pending'),
            self::STATUS_GOOSHAD => Yii::t('ecommerce', 'Goods Had'),
            self::STATUS_MOVE => Yii::t('ecommerce', 'Moved To Shop'),
            self::STATUS_DELIVERY => Yii::t('ecommerce', 'Delivery'),
            self::STATUS_PAID => Yii::t('ecommerce', 'Paid'),
            self::STATUS_COMPLETE => Yii::t('ecommerce', 'Complete'),
            self::STATUS_CANCEL => Yii::t('ecommerce', 'Cancel'),
            self::STATUS_CLOSE => Yii::t('ecommerce', 'Close'),
        ];
    }
    
    /**
     * Function get list status by status
     * @param string $status status
     * @return array
     */
    public static function getListStatus($status){
        // Deleted array elements for key
        $allStatus = [
            self::STATUS_NEW => [
                self::STATUS_NEW,
                self::STATUS_GOOSHAD,
                self::STATUS_MOVE,
                self::STATUS_DELIVERY,
                self::STATUS_PAID,
                self::STATUS_COMPLETE,
                self::STATUS_CLOSE,
            ],
            self::STATUS_PROCESS => [
                self::STATUS_PROCESS,
                self::STATUS_NEW,
                self::STATUS_GOOSHAD,
                self::STATUS_CLOSE,
            ],
            self::STATUS_PENDING => [
                self::STATUS_NEW,
                self::STATUS_PROCESS,
                self::STATUS_PENDING,
                self::STATUS_MOVE,
                self::STATUS_DELIVERY,
                self::STATUS_PAID,
                self::STATUS_COMPLETE,
                self::STATUS_CLOSE,
            ],
            self::STATUS_GOOSHAD => [
                self::STATUS_NEW,
                self::STATUS_PROCESS,
                self::STATUS_PENDING,
                self::STATUS_GOOSHAD,
                self::STATUS_CLOSE,
            ],
            self::STATUS_MOVE => [
                self::STATUS_NEW,
                self::STATUS_PROCESS,
                self::STATUS_PENDING,
                self::STATUS_GOOSHAD,
                self::STATUS_MOVE,
            ],
            self::STATUS_DELIVERY => [
                self::STATUS_NEW,
                self::STATUS_PROCESS,
                self::STATUS_PENDING,
                self::STATUS_GOOSHAD,
                self::STATUS_MOVE,
                self::STATUS_DELIVERY,
            ],
            self::STATUS_PAID => [
                self::STATUS_NEW,
                self::STATUS_PROCESS,
                self::STATUS_PENDING,
                self::STATUS_GOOSHAD,
                self::STATUS_PAID,
            ],
            self::STATUS_CANCEL => [
                self::STATUS_NEW,
                self::STATUS_PROCESS,
                self::STATUS_PENDING,
                self::STATUS_GOOSHAD,
                self::STATUS_MOVE,
                self::STATUS_DELIVERY,
                self::STATUS_PAID,
                self::STATUS_COMPLETE,
                self::STATUS_CANCEL,
                self::STATUS_CLOSE,
            ],
            self::STATUS_CLOSE => [
                self::STATUS_NEW,
                self::STATUS_PROCESS,
                self::STATUS_PENDING,
                self::STATUS_GOOSHAD,
                self::STATUS_MOVE,
                self::STATUS_DELIVERY,
                self::STATUS_PAID,
                self::STATUS_COMPLETE,
                self::STATUS_CANCEL,
                self::STATUS_CLOSE,
            ],
            self::STATUS_COMPLETE => [
                self::STATUS_NEW,
                self::STATUS_PROCESS,
                self::STATUS_PENDING,
                self::STATUS_GOOSHAD,
                self::STATUS_MOVE,
                self::STATUS_DELIVERY,
                self::STATUS_PAID,
                self::STATUS_COMPLETE,
                self::STATUS_CANCEL,
            ],
        ];

        if (!isset($allStatus[$status]))
            $status = self::STATUS_NEW;
        
        return array_diff_key(self::$status, array_flip($allStatus[$status]));
    }
    
    /**
     * Function get product list active when chose modal product
     * @param array $product_list List id, quantity of product
     * [
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
    public static function getProductList($product_list = []){
        // List product id and quantity
        if (is_array($product_list) AND !empty($product_list)) {
            $products = [];
            foreach ($product_list as $product) {
                $id = ArrayHelper::getValue($product, 'id', '');
                $quantity = ArrayHelper::getValue($product, 'quantity', 0);
                $products[] = $id . ':' . $quantity;
            }

            return implode(',', $products);
        }
        
        return null;
    }
    
}