<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'About';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1>О данном Приложении Yii2</h1>

    <p>
		<ol>
		<li>Регистрация пользователя</li>
		<li>Для зарегистрированного пользователя:
			<ul>
			<li>загрузка Excel-файла;</li> 
			<li>сохранение данных из файла в базе данных;</li>
			<li>отправка уведомлений на email-адреса из загруженных данных.</li>
			</ul>
		</li> 
		</ol>
    </p>

</div>
