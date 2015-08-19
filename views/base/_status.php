<?php
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use sya\ecommerce\Module;
?>
<!-- Begin view status -->
<?php if (!$model->getIsNewRecord()): ?>
    <?= Html::dropDownList('statusOrder', '', Module::getListStatus($model->status), ['class' => 'form-control', 'onchange' => 'changeStatusOrder(this);']); ?>
<?php else: ?>
    <?= ArrayHelper::getValue(Module::$status, Module::STATUS_NEW); ?>
<?php endif; ?>
<!-- End view status -->

<!-- Begin register file and code js, css -->
<?php
$this->registerCssFile('@web/vendor/bower/sweetalert/dist/sweetalert.css', ['depends' => yii\bootstrap\BootstrapAsset::className()]);
$this->registerJsFile('@web/vendor/bower/sweetalert/dist/sweetalert.min.js', ['depends' => yii\bootstrap\BootstrapPluginAsset::className(), 'position' => \yii\web\View::POS_END]);

$this->registerJs("
    // Function change status order
    function changeStatusOrder(element){
        var status = $(element).val();
        var id = '" . $model->_id . "';
        if (status != '" . Module::STATUS_CANCEL . "') {
            swal({
                title: '" . Yii::t('yii', 'Are you sure?') . "',
                text: 'Your will not be able to recover this imaginary file!',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DD6B55',
                confirmButtonText: 'Yes, change it!',
                cancelButtonText: 'No, cancel plx!',
                closeOnConfirm: false,
                closeOnCancel: false },
            function (isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: '" . \yii\helpers\Url::to(['/ecommerce/ajax/changestatus']) . "',
                        type: 'post',
                        dataType: 'json',
                        data: {status: status, id: id},
                    }).done(function (data) {
                        swal('Change!', 'Your imaginary file has been change.', 'success');
                        $(element).empty();
                        $.each(data, function(key, value) {
                            $(element).append( new Option(value, key) );
                        });
                    });
                } else {
                    swal('Cancelled', 'Your imaginary file is safe :)', 'error');
                }
            });
        }
    }
", yii\web\View::POS_END);
?>
<!-- End register file and code js, css -->