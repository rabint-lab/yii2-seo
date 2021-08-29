<?php
use yii\bootstrap4\ActiveForm;

$form = ActiveForm::begin(); ?>
<div class="container mt-2">
    <div class="row">
        <div class="col-12">
            <div class="form-group">
                <label><?=Yii::t('rabint','توضیحات')?></label>
                <?= \yii\helpers\Html::textInput('description','',['class'=>'form-control']) ?>
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                <label><?=Yii::t('rabint','کلمات کلیدی')?></label>
                <?= \yii\helpers\Html::textInput('key_words','',['class'=>'form-control']) ?>
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                <label><?=Yii::t('rabint','درباره وبسایت')?></label>
                <?= \yii\helpers\Html::textInput('about','',['class'=>'form-control']) ?>
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                <label><?=Yii::t('rabint','نویسنده')?></label>
                <?= \yii\helpers\Html::textInput('Writer','',['class'=>'form-control']) ?>
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                <label><?=Yii::t('rabint','ژانر')?></label>
                <?= \yii\helpers\Html::textInput('genre','',['class'=>'form-control']) ?>
            </div>
        </div>
        <div class="col-12">
            <?= \yii\helpers\Html::submitButton(Yii::t('rabint','ذخیره'),['class'=>'btn btn-success btn-flat']) ?>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>