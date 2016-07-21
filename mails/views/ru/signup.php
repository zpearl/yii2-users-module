<?php

/**
 * @var yii\web\View $this
 * @var zpearl\users\models\frontend\User $model
 */

use yii\helpers\Html;
use yii\helpers\Url;

$url = Url::toRoute(['/users/guest/activation', 'key' => $model['secure_key']], true); ?>
<p>Здравствуйте <?= Html::encode($model['username']) ?>!</p>
<p>Перейдите по ссылке ниже чтобы подтвердить свой электронный адрес и активировать свой аккаунт:</p>
<p><?= Html::a(Html::encode($url), $url) ?></p>