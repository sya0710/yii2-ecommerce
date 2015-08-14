<?php
use yii\bootstrap\Modal;
Modal::begin([
    'header' => '<h4 class="modal-title" id="myModalLabel">' . Yii::t('ecommerce', 'List Customer') . '</h4>',
    'toggleButton' => [
        'label' => '<i class="fa fa-plus"></i>&nbsp;' . Yii::t('ecommerce', 'Add') . ' ' . Yii::t('ecommerce', 'Customer'),
        'class' => 'btn btn-xs btn-primary pull-right'
    ],
    'footer' => '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button><button type="button" class="btn btn-primary">Save changes</button>'
]);
?>
sdád123213
<?php
Modal::end();
?>