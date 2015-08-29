<?php
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\bootstrap\Html;

// Declare column in product
$defaultColumns = [
    [
        'attribute' => '_id',
        'hAlign'=>'center',
        'vAlign'=>'middle',
        'contentOptions' => [
            'class' => 'customerId'
        ]
    ],
];
$customerColumns = ArrayHelper::merge($defaultColumns, $customerColumns);
    
Modal::begin([
    'header' => '<h4 class="modal-title" id="myModalLabel">' . Yii::t('ecommerce', 'List Customer') . '</h4>',
    'size' => 'modal-lg',
    'toggleButton' => [
        'label' => '<i class="fa fa-plus"></i>&nbsp;' . Yii::t('ecommerce', 'Add') . ' ' . Yii::t('ecommerce', 'Customer'),
        'class' => 'btn btn-xs btn-primary pull-right'
    ],
    'footer' => '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button><button type="button" class="btn btn-primary">Save changes</button>'
]);

echo GridView::widget([
    'panel' => [
        'heading' => Yii::t('product', 'Product'),
    ],
    'id' => 'customer-grid',
    'pjax' => TRUE,
    'dataProvider' => $customerDataProvider,
    'filterModel' => $customerSearchModel,
    'columns' => $customerColumns,
    'responsive' => true,
    'hover' => true,
    'toolbar' => [
    ]
]);

Modal::end();
?>