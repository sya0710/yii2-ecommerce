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
        ]
    ],
]
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

## License
**yii2-ecommerce** is released under the MIT License. See the bundled LICENSE.md for details.
