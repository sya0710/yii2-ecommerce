<?php
namespace sya\ecommerce;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class Module extends \yii\base\Module{
    CONST MODULE = 'ecommerce';
    
    // Status
    CONST STATUS_ACTIVE = 'Hiện';
    CONST STATUS_INACTIVE = 'Ẩn';
    
    // Action in ecommerce
    CONST ACTION_INDEX = 'index';
    CONST ACTION_DELETE = 'delete';
    CONST ACTION_UPDATE = 'update';
    CONST ACTION_CREATE = 'create';
    
    /**
     * Status of order
     */
    public static $status = [
        '' => '',
        'new' => 'Đơn hàng mới',
        'processing' => 'Đang xử lý',
        'pending' => 'Chờ đặt hàng',
        'goodshad' => 'Hàng đã về',
        'movedtoshop' => 'Đã chuyển xuống cửa hàng',
        'delivery' => 'Đang giao hàng',
        'complete' => 'Hoàn thành',
        'cancel' => 'Hủy bỏ',
        'close' => 'Đóng',
    ];
    
    /**
     * Log status action order
     */
    public static $logStatus = [
        'add' => 'fa-plus',
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
     * @var string namespace of product 
     */
    public $productModule = '';
    
    /**
     * @var array product column in gridview
     */
    public $productColumns = [];
    
    /**
     * @inherit doc
     */
    public function init()
    {
        parent::init();
        $item = ArrayHelper::getValue($this->itemSettings, 'itemSettings', []);
        $actions = ArrayHelper::getValue($item, 'actions', []);
        $actions += [
            self::ACTION_CREATE => Url::to(['/ecommerce/base/create']),
            self::ACTION_INDEX => Url::to(['/ecommerce/base/index']),
            self::ACTION_UPDATE => Url::to(['/ecommerce/base/update']),
            self::ACTION_DELETE => Url::to(['/ecommerce/base/delete']),
        ];
        
        $this->itemSettings['itemSettings']['actions'] = $actions;
    }
    
    /**
     * 
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