<?php
/* @var $this yii\web\View */
/* @var $baseUrl string */
/* @var $name string */

/* @var $apiData array */

use moxuandi\apidoc\assets\LoginAsset;
use yii\helpers\Html;
use yii\widgets\Spaceless;

LoginAsset::register($this);

$this->title = $name;
$request = Yii::$app->request;
?>
<?php $this->beginPage(); Spaceless::begin(); ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
<meta charset="<?= Yii::$app->charset ?>">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
<title><?= Html::encode($this->title) ?></title>
<?php $this->head() ?>
<?php $this->registerCsrfMetaTags() ?>
<style>
body.login {background-color: #eee;padding-bottom: 2.5rem;padding-top: 2.5rem;}
.form-signin {margin: 0 auto;max-width: 20rem;padding: 1rem;}
</style>
</head>

<body class="login">
<?php $this->beginBody() ?>
<div class="container-fluid">
    <form class="form-signin" method="post" role="form">
        <?= Html::hiddenInput($request->csrfParam, $request->getCsrfToken()); ?>
        <h2>请登录</h2>
        <div class="form-group required">
            <input type="password" class="form-control" name="LoginForm[password]" required />
        </div>
        <button type="submit" class="btn btn-lg btn-primary btn-block">登录</button>
    </form>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php Spaceless::end(); $this->endPage(); ?>
