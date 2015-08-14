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
            'class' => 'productId'
        ]
    ],
];
$productColumns = ArrayHelper::merge($defaultColumns, $productColumns);
$productColumns[] = [
    'header' => Yii::t('ecommerce', 'Quantity'),
//        'mergeHeader' => TRUE,
    'hAlign' => 'center',
    'vAlign' => 'middle',
    'contentOptions' => [
        'class' => 'productQty'
    ],
    'value'=>function ($model, $key, $index, $widget) { 
        return Html::textInput('qty', 0, ['class' => 'form-control qty_' . $model->_id, 'onkeyup' => 'return productQtyOrder(this);']);
    },
    'format'=>'raw',
];

// Modal product
Modal::begin([
    'id' => 'product_modal',
    'size' => 'modal-lg',
    'header' => '<h4 class="modal-title" id="myModalLabel">' . Yii::t('ecommerce', 'List Product') . '</h4>',
    'toggleButton' => [
        'label' => '<i class="fa fa-plus"></i>&nbsp;' . Yii::t('ecommerce', 'Add') . ' ' . Yii::t('ecommerce', 'Product'),
        'class' => 'btn btn-xs btn-primary pull-right'
    ],
    'footer' => '<button type="button" onclick="addProduct();" class="btn btn-primary">' . Yii::t('ecommerce', 'Add Product') . '</button>'
]);

echo GridView::widget([
    'panel' => [
        'heading' => Yii::t('product', 'Product'),
    ],
    'id' => 'product-grid',
    'pjax' => TRUE,
    'dataProvider' => $productDataProvider,
    'filterModel' => $productSearchModel,
    'columns' => $productColumns,
    'responsive' => true,
    'hover' => true,
    'toolbar' => [
    ]
]);

Modal::end();
echo Html::hiddenInput('product_list', \sya\ecommerce\Module::getProductList($model->product), ['id' => 'product_list']);

// Register js code
$this->registerJs("
    // Add or remove product when click product
    function productOrder(){
        $('#product-grid-container table tbody tr').click(function(){
            // Get id of product and qty
            var id = $(this).find('.productId').text();
            var qty = $(this).find('.productQty input');
            
            // Get id and qty selected
            var product_list = new Array();
            if($('#product_list').val()){
                var productSelected = $('#product_list').val().split(',');
            }else{
                var productSelected = null;
            }
            
            if($(this).hasClass('selected')){ // remove from hidden field
                removeProductId(productSelected, product_list, id, qty, this);
            } else { // add too hidden field
                addProductId(productSelected, product_list, id, qty, this);
            }
        });
    }
    
    // Add or remove product when qty = 0
    function productQtyOrder(element){
        // Get id of product and qty
        var id = $(element).parent().parent().find('.productId').text();
        var qty = $(element);
        
        // Get id and qty selected
        var product_list = new Array();
        if($('#product_list').val()){
            var productSelected = $('#product_list').val().split(',');
        }else{
            var productSelected = null;
        }

        if(qty.val() == 0){ // remove from hidden field
            removeProductId(productSelected, product_list, id, qty, $(element).parent().parent());
        } else { // add too hidden field
            addProductId(productSelected, product_list, id, qty, $(element).parent().parent());
        }
    }
    
    // Function remove id product
    function removeProductId(productSelected, product_list, id, qty, element){
        if(productSelected.length){
            j = 0;
            for(i =0;i< productSelected.length;i++){
                info = productSelected[i].split(':');
                if(info[0]!=id){
                    product_list[j] = info[0]+':'+info[1];
                    j++;
                }
            }
        }

        $('#product_list').val(product_list.length?product_list.join():'');
        $(element).removeClass('selected');
        qty.val(0);
    }
    
    // Function add id product
    function addProductId(productSelected, product_list, id, qty, element){
        if (qty.val() == 0) qty.val(1);
        $(element).addClass('selected');

        if(productSelected){
            updateValue = false;
            for(i =0;i< productSelected.length;i++){
                info = productSelected[i].split(':');
                if(info[0]==id){
                    product_list[i] =info[0]+':'+qty.val();
                    updateValue = true;
                }else{
                    product_list[i]=info[0]+':'+info[1];
                }
            }
            if(!updateValue){
                product_list[product_list.length] = id+':'+qty.val();
            }
        }
        $('#product_list').val(product_list.length?product_list.join():(id+':'+qty.val()));
    }
    
    // Selected product
    function setSelect(){
        var product_list = $('#product_list').val();
        if(product_list){
            var field = product_list.split(',');
            var total = field.length;
            for(var i=0; i<total; i++){
                var product = field[i].split(':');
                $('.qty_'+product[0]).val(product[1]);
                $('.qty_'+product[0]).parent().parent().addClass('selected');
            }		
        }
    }
    
    // Add product in order
    function addProduct(){
        $.ajax({
            url: '" . \yii\helpers\Url::to(['/ecommerce/ajax/addproduct']) . "',
            type: 'post',
            data: {data: $('#product_list').val()},
        }).done(function (data) {
            $('#product_info').html(data);
            totalProduct();
            $('#product_modal').modal('hide');
        });
    }
", yii\web\View::POS_END);

$this->registerJs("
    productOrder();
    setSelect();
    $(document).on('pjax:complete', function () {
        productOrder();
        setSelect();
    });
", yii\web\View::POS_READY);
?>