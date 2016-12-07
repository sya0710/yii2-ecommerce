# yii2-ecommerce
Module manager ecommerce, CRUD ecommerce

## Installation
Add 

```php
"sya/yii2-ecommerce": "dev-master"
```

to the require section of your composer.json file.

# Module
```php
use sya\gallery\Module;
'modules' => [
    'ecommerce' => [
        'class' => 'sya\ecommerce\Module',
        'itemModule' => 'sya\ecommerce\models\Order',
        'customerTable' => [
            'customerModule' => 'app\modules\account\models\User',
            'customerColumns' => [
                [
                    'attribute' => 'p_fullname',
                    'hAlign'=>'center',
                    'vAlign'=>'middle',
                    'contentOptions' => [
                        'class' => 'customer_p_fullname'
                    ],
                ],
                [
                    'attribute' => 'p_address',
                    'hAlign'=>'center',
                    'vAlign'=>'middle',
                    'contentOptions' => [
                        'class' => 'customer_p_address'
                    ],
                ],
            ],
            'customerSearch' => 'searchCustomer',
            'fieldOrder' => [
                'full_name' => 'p_fullname',
                'address' => 'p_address',
            ]
        ],
        'productTable' => [
            'productModule' => 'app\modules\product\models\Product',
            'productColumns' => [
                [
                    'attribute' => 'sku',
                    'hAlign'=>'center',
                    'vAlign'=>'middle',
                ],
                [
                    'attribute' => 'title',
                    'hAlign'=>'center',
                    'vAlign'=>'middle',
                ],
                [
                    'attribute' => 'price',
                    'hAlign'=>'center',
                    'vAlign'=>'middle',
                    'format'=>['decimal', 0],
                ],
                [
                    'class'=>'kartik\grid\BooleanColumn',
                    'attribute'=>'status',
                    'vAlign'=>'middle',
                    'format'=>'raw',
                    'trueLabel' => 'Hiện',
                    'falseLabel' => 'Ẩn'
                ]
            ],
            'fieldOrder' => [
                'title' => 'title',
                'sku' => 'sku',
                'price' => 'price',
            ]
        ]
    ],
]
```

#Allow chose add one product or multiple product
```php
'multiple' => false,
```

#Enable generate activation code
```php
'enableActivitionCode' => true,
```

## Demo

Controller
```php
use sya\ecommerce\Ecommerce;

public function actionIndex(){
    $queryParams = Yii::$app->request->getQueryParams();
    $ecommerce = Ecommerce::module();
    $searchModel = new $ecommerce->itemModule;
    $searchModel->scenario = 'search';
    $dataProvider = $searchModel->search($queryParams);

    // Set title page
    Yii::$app->view->title = Yii::t($this->module->id, 'Order');
    Yii::$app->view->params['breadcrumbs'][] = Yii::$app->view->title;

    return $this->render('index', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider
    ]);
}
```

View
```php
use sya\ecommerce\Ecommerce;
use sya\ecommerce\Module;
use yii\helpers\Url;

echo Ecommerce::widget([
    'statisticColumns' => [
        [
            'header' => 'New Orders',
        ],
        [
            'header' => 'Orders Pending',
        ],
        [
            'header' => 'Orders',
            'timeOptions' => [
                'class' => 'label-info'
            ],
            'totalStatistic' => '275,800'
        ],
        [
            'header' => 'Income',
            'smallHeader' => 'In first month',
            'time' => 'Low value',
            'timeOptions' => [
                'class' => 'label-danger'
            ]
        ],
    ],
    // Item and gridview setting
    'itemSettings' => [
        'dataProvider' => $dataProvider,
        'searchModel' => $searchModel,
        'actions' => [
            Module::ACTION_CREATE => Url::to(['/order/backend/default/create']),
            Module::ACTION_INDEX => Url::to(['/order/backend/default/index']),
            Module::ACTION_UPDATE => Url::to(['/order/backend/default/update']),
            Module::ACTION_DELETE => Url::to(['/order/backend/default/delete']),
        ],
    ],
]);
```

# Component
```php
'cart' => [
    'class' => 'sya\ecommerce\components\Component'
],
```

# Use component
```php
// Add item product to cart
$product_info = [
    '57ce373574530' => [
        'id' => '57ce373574530',
        'title' => '213213123213',
        'price' => '25000',
        'old_price' => '0',
        'quantity' => 3
    ],
    '581957637371d' => [
        'id' => '581957637371d',
        'title' => 'rrrrrr123',
        'price' => '200000',
        'old_price' => '0',
        'quantity' => 5
    ],
];

Yii::$app->cart->addCart($product_info);

// Create order
$customer_info = [
    'fullname' => 'Minion',
    'phone' => '0123456789',
    'note_customer' => 'Done'
];
Yii::$app->cart->createOrder($product_info, $customer_info);

// Get id cart
Yii::$app->cart->getCartId();

// Get infomation of cart
Yii::$app->cart->getCart();

// Update quantity of product
$product_qty = [
    '581957637371d' => 3,
    '57ce373574530' => 4,
];
Yii::$app->cart->updateQty($product_qty);
```

## I18n
```
'components' => [
    'i18n' => [
        'translations' => [
            'ecommerce' => ['class' => 'yii\i18n\PhpMessageSource', 'basePath' => '@syaEcommerce/messages'],
        ],
    ],
],
```

## License
**yii2-ecommerce** is released under the MIT License. See the bundled LICENSE.md for details.
