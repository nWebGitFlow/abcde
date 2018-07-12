<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Payment */

$this->title = 'Create Payment';
$this->params['breadcrumbs'][] = ['label' => 'Payments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="payment-create">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php if (Yii::$app->user->isGuest) { ?>
        <?= Html::a('Login', ['site/login'], ['class' => 'profile-link']) ?>
    <?php } else { ?>

	    <?= $this->render('_form', [
	        'model' => $model,
	    ]) ?>
    <?php } ?>

</div>
