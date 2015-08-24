<?php
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\bootstrap\Html;
use sya\ecommerce\Module;
sya\ecommerce\EcommerceAssets::register($this);

$form = ActiveForm::begin([
    'id' => 'formDefault',
    'layout' => 'horizontal',
    'options' => ['enctype' => 'multipart/form-data'],
    'fieldConfig' => [
        'horizontalCssClasses' => [
            'label' => 'col-sm-2',
            'wrapper' => 'col-sm-10',
            'error' => 'help-block m-b -none',
            'offset' => 'col-sm-offset-0',
            'hint' => 'hr-line-dashed',
        ],
    ],
]);
?>
 
<div class="row">
    <div class="col-md-6">
        <div id="nestable-menu">
            <div class="btn-group">
                <?php if (!$model->getIsNewRecord()): ?>
                    <button class="btn btn-primary " type="submit"><i class="fa fa-check"></i>&nbsp;<?= Yii::t('ecommerce', 'Update') ?> <?= Yii::t('ecommerce', 'Order') ?></button>
                    <a href="<?= Url::to(['create']) ?>" class="btn btn-info "><i class="fa fa-check"></i>&nbsp;<?= Yii::t('ecommerce', 'Create') ?> <?= Yii::t('ecommerce', 'Order') ?></a>
                <?php else: ?>
                    <button class="btn btn-info " type="submit"><i class="fa fa-check"></i>&nbsp;<?= Yii::t('ecommerce', 'Create') ?> <?= Yii::t('ecommerce', 'Order') ?></button>
                <?php endif; ?>
                <a href="<?= Url::to(['backend/default']) ?>" class="btn btn-info "><i class="fa fa-rotate-left"></i>&nbsp;<?= Yii::t('ecommerce', 'Back') ?></a>
            </div>
        </div>
    </div>
</div>

<!-- Begin product -->
<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5><?= Yii::t('product', 'Infomation Product') ?></h5>
                <div class="ibox-tools">
                    <?= $this->render('_product',[
                        'productSearchModel' => $productSearchModel,
                        'productDataProvider' => $productDataProvider,
                        'productColumns' => $productColumns,
                        'form' => $form,
                        'model' => $model,
                    ]); ?>
                </div>
            </div>
            <div class="ibox-content" id="product_info">
                <?= $template ?>
            </div>
        </div>
    </div>
</div>
<!-- End product -->

<!-- Begin customer and payment -->
<div class="row">
    <div class="col-lg-6">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5><?= Yii::t('ecommerce', 'Infomation Customer') ?></h5>
                <div class="ibox-tools">
                    <?= $this->render('_customer'); ?>
                </div>
            </div>
            <div class="ibox-content">
                
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5><?= Yii::t('ecommerce', 'Payment') ?></h5>
            </div>
            <div class="ibox-content">
                <select class="form-control">
                    <option>1</option>
                    <option>2</option>
                    <option>3</option>
                    <option>4</option>
                </select>
            </div>
        </div>
    </div>
</div>
<!-- End customer and payment -->

<!-- Begin note customer and Payment -->
<div class="row">
    <div class="col-lg-6">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5><?= Yii::t('ecommerce', 'Note customer') ?></h5>
            </div>
            <div class="ibox-content">
                <?php if ($model->getIsNewRecord()): ?>
                    <?= $form->field($model, 'note_customer', ['horizontalCssClasses' => ['wrapper' => 'col-sm-12']])->textarea(['rows' => 4])->label(false); ?>
                <?php else: ?>
                    <?= $model->note_customer; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5><?= Yii::t('ecommerce', 'Status') ?></h5>
            </div>
            <div class="ibox-content">
                <?= $this->render('_status', [
                    'model' => $model,
                ]); ?>
            </div>
        </div>
    </div>
</div>
<!-- End note customer and Payment -->

<!-- Begin note admin -->
<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5><?= Yii::t('ecommerce', 'Note Admin') ?></h5>
            </div>
            <div class="ibox-content">
                <?= $this->render('_note_admin', [
                    'model' => $model,
                    'form' => $form,
                ]); ?>
            </div>
        </div>
    </div>
</div>
<!-- End note admin -->

<?php if (!$model->getIsNewRecord()): ?>
<!-- Begin log order -->
<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5><?= Yii::t('ecommerce', 'Log order') ?></h5>
            </div>
            <div class="ibox-content inspinia-timeline" id='syaTimeline'>
               <?= $model->generateLogOrder(); ?>
            </div>
        </div>
    </div>
</div>
<!-- End log order -->
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div id="nestable-menu">
            <div class="btn-group">
                <?php if (!$model->getIsNewRecord()): ?>
                    <button class="btn btn-primary " type="submit"><i class="fa fa-check"></i>&nbsp;<?= Yii::t('ecommerce', 'Update') ?> <?= Yii::t('ecommerce', 'Order') ?></button>
                    <a href="<?= Url::to(['create']) ?>" class="btn btn-info "><i class="fa fa-check"></i>&nbsp;<?= Yii::t('ecommerce', 'Create') ?> <?= Yii::t('ecommerce', 'Order') ?></a>
                <?php else: ?>
                    <button class="btn btn-info " type="submit"><i class="fa fa-check"></i>&nbsp;<?= Yii::t('ecommerce', 'Create') ?> <?= Yii::t('ecommerce', 'Order') ?></button>
                <?php endif; ?>
                <a href="<?= Url::to(['backend/default']) ?>" class="btn btn-info "><i class="fa fa-rotate-left"></i>&nbsp;<?= Yii::t('ecommerce', 'Back') ?></a>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>

<?php
$this->registerJs("
    // Function the total amount of each product
    function totalPriceProduct(element){
        var price = $(element).parents('tr').find('.product_price').val();
        var qty = $(element).val();
        var total = Number(price) * Number(qty);
        var id = $(element).parents('tr').find('.product_id').text();
        
        if (qty != 0) {
            $(element).parents('tr').find('.product_total').text(formatNumber(total) + ' VNĐ');
            $(element).parents('tr').find('.product_total').attr('data-total', total);
            
            // Get id and qty selected
            var product_list = new Array();
            if($('#product_list').val()){
                var productSelected = $('#product_list').val().split(',');
            }else{
                var productSelected = null;
            }
            
            // Change qty product in modal product
            $('#product-grid-container table tbody tr').each(function(index){
                if ($(this).attr('data-key') == id){
                    $(this).find('.productQty input').val(qty);
                    addProductId(productSelected, product_list, id, $(this).find('.productQty input'), this);
                }
            });
        } else {
            // Get id and qty selected
            var product_list = new Array();
            if($('#product_list').val()){
                var productSelected = $('#product_list').val().split(',');
            }else{
                var productSelected = null;
            }
            
            // Remove qty product in modal product
            $('#product-grid-container table tbody tr').each(function(index){
                if ($(this).attr('data-key') == id){
                    removeProductId(productSelected, product_list, id, $(this).find('.productQty input'), this);
                }
            });

            $(element).parents('tr').remove();
        }
        
        totalProduct();
    }
    
    // Format money
    function formatNumber (num) {
        return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
    }
    
    // Function the total amount of all product
    function totalProduct(){
        var product_total = 0;
        $('.product_total').each(function(index){
            product_total = product_total + Number($(this).attr('data-total'));
        });

        $('#product_total').text(formatNumber(product_total));
    }
    
    function addShipping(element){
        var shipping = $(element);
        var price = parseInt(shipping.val().replace(/,/g, ''));
        if (price){
            shipping.val(formatNumber(price));
            $('#syaShipping').val(price);
            $('#syaShipping').attr('data-total', price);
        } else {
            shipping.val(0);
            $('#syaShipping').val(0);
            $('#syaShipping').attr('data-total', 0);
        }
            
        totalProduct();
    }
", yii\web\View::POS_END);
?>