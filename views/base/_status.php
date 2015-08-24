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
$this->registerJs("
    // Function change status order
    function changeStatusOrder(element){
        var status = $(element).val();
        var id = '" . $model->_id . "';
        if (status != '" . Module::STATUS_CANCEL . "') {
            var confirmChange = confirm('" . Yii::t('ecommerce', 'Are you sure?') . "');
            if (confirmChange == true){
                $.ajax({
                    url: '" . \yii\helpers\Url::to(['/ecommerce/ajax/changestatus']) . "',
                    type: 'post',
                    dataType: 'json',
                    data: {status: status, id: id},
                }).done(function (data) {
                    $(element).empty();
                    $.each(data.status, function(key, value) {
                        $(element).append( new Option(value, key) );
                    });
                    $('#syaTimeline').html(data.log);
                });
            }
        }
    }
", yii\web\View::POS_END);
?>
<!-- End register file and code js, css -->