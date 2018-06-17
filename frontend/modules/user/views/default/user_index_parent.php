<?php

use trntv\filekit\widget\Upload;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $model \frontend\modules\user\models\AccountForm */
/* @var $form yii\widgets\ActiveForm */
/* @var $record common\models\ParentNanny */
$this->registerJs(
    '
    $(document).ready(function () {
        $("html, body").animate({scrollTop: $(".slide").height()+$(".navbar").height()},"slow");
        console.log($("slide").height());
        $("#reset_button").click(function(){
            $(this).addClass("hidden");
            $("#reset_pwd").removeClass("hidden");
        })
     });
    ',
    View::POS_READY,
    'my-button-handler'
);
$this->title = Yii::t('frontend', 'Parent Account Page')
?>

<div class="user-profile-form">

    <?php $form = ActiveForm::begin(); ?>
    <br>
    <div class="col-md-6">   
        <h2 style="color: #414141;">My NannyCare Account</h2>

        <?= $form->field($model, 'username')->textInput(['readOnly' => true]) ?>

        <?= $form->field($model, 'email')->textInput(['readOnly' => true]) ?>

        <div class="hidden" id="reset_pwd">
            <?= $form->field($model, 'password')->passwordInput() ?>

            <?= $form->field($model, 'password_confirm')->passwordInput() ?>

            <div class="form-group">
                <?= Html::submitButton(Yii::t('frontend', 'Confirm Reset Password'), ['class' => 'nav-btn']) ?>
            </div>
        </div>

        <div class="form-group">
            <?= Html::Button(Yii::t('frontend', 'Reset Password'), ['class' => 'nav-btn', 'id' => 'reset_button']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

    <div class="col-md-6 parent-profile">
        <h2 style="color: #414141;">My Profile</h2>
        <h3><b>Personal data:</b><span style="float: right;"><a href="/user/sign-in/continue-family" class="btn btn-inverse">Edit Profile</a></span></h3>
        <h3><b>Credits:</b> <?= $model->credits; ?><span style="float: right;"><a href="get-credits" class="btn btn-inverse">Get Credits</a></span></h3>
        <h3><b>Nannies Selected:</b><span style="float: right;"><a href="/nannies/index" class="btn btn-inverse">Find A Nanny</a></span></h3>
        <div class="nannies-selected-table">
            <?php
            $id = Yii::$app->user->id;
            $parentnannyrecords = \common\models\ParentNanny::find()->where(['parentid'=>$id])->all();
            if (count($parentnannyrecords)) :
            ?>
            <table class="table table-hover">
                <thead><tr><th>Name</th><th>Email</th><th>Profile Link</th></tr></thead>
                <tbody>
                <?php
                    foreach ($parentnannyrecords as $record) {
                        echo '<tr><td>'. preg_split('/\s+/', $record->nanny->name)[0] .'</td><td>'. $record->nanny->email .'</td><td>'. Html::a('Click Here', '/nannies/view?id=' . $record->nannyid) .'</td></tr>';
                    }
                ?>
                </tbody>
            </table>
            <?php endif; ?>

        </div>
    </div>
</div>
