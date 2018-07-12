<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Payment */

$this->title = 'Update Payment: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Payments', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="payment-update">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php if (Yii::$app->user->isGuest) { ?>
        <?= Html::a('Login', ['site/login'], ['class' => 'profile-link']) ?>
    <?php } else { ?>

	    <?= $this->render('_form', [
	        'model' => $model,
	    ]) ?>

    <?php } ?>

</div>
