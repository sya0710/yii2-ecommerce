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
    'id' => 'customer_modal',
    'header' => '<h4 class="modal-title" id="myModalLabel">' . Yii::t('ecommerce', 'List') . ' ' . Yii::t('ecommerce', 'Customer') . '</h4>',
    'size' => 'modal-lg',
    'toggleButton' => [
        'label' => '<i class="fa fa-plus"></i>&nbsp;' . Yii::t('ecommerce', 'Create') . ' ' . Yii::t('ecommerce', 'Customer'),
        'class' => 'btn btn-xs btn-primary pull-right'
    ],
    'footer' => '<button type="button" class="btn btn-primary">' . Yii::t('ecommerce', 'Create') . ' ' . Yii::t('ecommerce', 'Customer') . '</button>'
]);

echo GridView::widget([
    'panel' => [
        'heading' => Yii::t('ecommerce', 'Customer'),
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

$js = [];
foreach ($customerField as $customerFiled => $customerColumn) {
    $js[] = '$(".customer_input_' . $customerColumn . '").val($(this).find(".customer_' . $customerColumn . '").text());';
}

// Register js code
$this->registerJs("
    // Add or remove product when click product
    function customerOrder(){
        $('#customer-grid-container table tbody tr').click(function(){
            " . implode("\n", $js) . "
            $('#customer_modal').modal('hide');
        });
    }
", yii\web\View::POS_END);

$this->registerJs("
    customerOrder();
    $(document).on('pjax:complete', function () {
        customerOrder();
    });
", yii\web\View::POS_READY);
?>