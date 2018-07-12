<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PaymentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Payments';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="payment-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php if (Yii::$app->user->isGuest) { ?>
        <?= Html::a('Login', ['site/login'], ['class' => 'profile-link']) ?>
    <?php } else { ?>
        <p>
            <?= Html::a('Download payments from excel-file', ['get-excel'], ['class' => 'btn btn-success']) ?>
        </p>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                // 'id',
                'email:email',
                'sum',
                'currency',
                //'source',
                //'created_at',

                ['class' => 'yii\grid\ActionColumn'],
            ],
        ]); ?>
    <?php } ?>
</div>
