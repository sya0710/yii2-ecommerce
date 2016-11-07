<?php
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use sya\ecommerce\Module;
?>
<!-- Begin view status -->
<div id="statusOrder">
<?php if (!$model->getIsNewRecord()): ?>
    <?php if (Module::STATUS_COMPLETE != $model->status AND Module::STATUS_CLOSE != $model->status AND Module::STATUS_CANCEL != $model->status): ?>
        <?= Html::dropDownList('statusOrder', '', Module::getListStatus($model->status), ['class' => 'form-control', 'onchange' => 'changeStatusOrder(this);']); ?>
    <?php else: ?>
        <?= ArrayHelper::getValue(Module::$status, $model->status); ?>
    <?php endif; ?>
<?php else: ?>
    <?= ArrayHelper::getValue(Module::$status, Module::STATUS_NEW); ?>
<?php endif; ?>
</div>
<!-- End view status -->

<!-- Begin register file and code js, css -->
<?php
$this->registerJs("
    // Function change status order
    function changeStatusOrder(element){
        var status = $(element).val();
        var id = '" . $model->_id . "';
        if (status != '" . Module::STATUS_EMPTY . "') {
            var confirmChange = confirm('" . Yii::t('ecommerce', 'Are you sure?') . "');
            if (confirmChange == true){
                $.ajax({
                    url: '" . \yii\helpers\Url::to(['/ecommerce/ajax/changestatus']) . "',
                    type: 'post',
                    dataType: 'json',
                    data: {status: status, id: id},
                }).done(function (data) {
                    $(element).empty();
                    $('#status_text').html(data.status);
                    if (data.arrStatus.length == 0){
                        $('#statusOrder').html(data.status);
                    } else {
                        $.each(data.arrStatus, function(key, value) {
                            $(element).append( new Option(value, key) );
                        });
                    }
                    $('#syaTimeline').html(data.log);
                });
            } else {
                $(element).val('" . Module::STATUS_EMPTY . "');
            }
        }
    }
", yii\web\View::POS_END);
?>
<!-- End register file and code js, css -->