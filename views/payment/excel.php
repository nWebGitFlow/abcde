<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Payment */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="payment-form">

    <?php if (Yii::$app->user->isGuest) { ?>
        <?= Html::a('Login', ['site/login'], ['class' => 'profile-link']) ?>
    <?php } else { ?>

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'excel')->fileInput(['maxlength' => true]) ?>

        <div class="form-group">
            <?= Html::submitButton('Оплатить', ['load'], ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    <?php } ?>

</div>
