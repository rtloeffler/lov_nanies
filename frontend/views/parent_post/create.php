<?php
/**
 * User: xczizz
 * Date: 2018/6/16
 * Time: 22:02
 */

use yii\widgets\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \common\models\ParentPost */

$this->registerJs(
    '
    $(document).ready(function () {
        $("html, body").animate({scrollTop: $(".slide").height()+$(".navbar").height()},"slow");
     });
    '
);
?>
<div class="post-job">
    <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'zip_code')->input('number') ?>
    <?= $form->field($model, 'job_type')->dropDownList([
        '1' => 'Full time',
        '2' => 'Part time',
        '3' => 'Live in',
        '4' => 'Babysitter',
        '5' => 'Temporary',
        '6' => 'Overnight care',
    ]) ?>
    <?= $form->field($model, 'type_of_help')->dropDownList([
        '1' => 'Nanny',
        '2' => 'Babysitter',
        '3' => 'Newborn Specialist',
        '4' => 'Special Needs',
        '5' => 'Caregiver',
        '6' => 'Housekeeper',
    ]) ?>
    <?= $form->field($model, 'summary')->textInput(); ?>
    <?= $form->field($model, 'description')->textarea(['rows' => 10]) ?>
    <div class="form-group">
        <?php echo Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
