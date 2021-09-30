<?php

use yii\bootstrap4\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Option */


$this->title = Yii::t('rabint', 'Create') .  ' ' . Yii::t('rabint', 'Option') . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('rabint', 'Options'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box-form option-create"  id="ajaxCrudDatatable">

    <h2 class="ajaxModalTitle" style="display: none"><?=  $this->title; ?></h2>
    <?= $this->render('_form', [
        'model' => $model,
        'url' => $url
    ]) ?>

</div>
