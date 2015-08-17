<?php
use yii\helpers\ArrayHelper;
?>

<?php if (!$model->getIsNewRecord()): ?>
    <div id="note_admin">
        <?= $model->generateNoteAdmin() ?>
    </div>
    <div class="m-t">
        <?= $form->field($model, 'note_admin_content', ['horizontalCssClasses' => ['wrapper' => 'col-sm-12']])->textarea(['rows' => 4])->label(false); ?>
        <button onclick="addNoteAdmin(this);" type="button" class="btn btn-primary btn-block m-t"> <?= Yii::t('ecommerce', 'Add note admin') ?></button>
    </div>
<?php else: ?>
    <?= $form->field($model, 'note_admin_content', ['horizontalCssClasses' => ['wrapper' => 'col-sm-12']])->textarea(['rows' => 4])->label(false); ?>
<?php endif; ?>

<?php
$this->registerJs("
    function addNoteAdmin(element){
        var note_admin_content = $('#order-note_admin_content').val();
        var id = '" . $model->_id . "';
        $.ajax({
            url: '" . \yii\helpers\Url::to(['/ecommerce/ajax/addnoteadmin']) . "',
            type: 'post',
            data: {id: id, note_admin_content: note_admin_content},
        }).done(function (data) {
            $('#order-note_admin_content').val('');
            $('#note_admin').html(data);
        });
    }
", yii\web\View::POS_END);