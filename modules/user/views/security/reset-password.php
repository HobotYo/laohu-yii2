<?php

use app\modules\core\extensions\HuCaptcha;
use kartik\password\PasswordInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model \app\modules\user\models\ResetPassword
 */

$this->title = '重置密码';
?>
<div class="site-reset-password">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'password')->widget(PasswordInput::className(), ['options' => ['maxlength' => 20]]) ?>

    <?= $form->field($model, 'password_repeat')->passwordInput(['maxlength' => 20]) ?>

    <?= $form->field($model, 'verifyCode')->widget(HuCaptcha::className()) ?>

    <?= Html::submitButton('重置密码', ['class' => 'btn btn-primary']) ?>

    <?php ActiveForm::end(); ?>

</div>
