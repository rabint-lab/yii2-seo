<?php
use yii\bootstrap4\ActiveForm;

$form = ActiveForm::begin(['action'=>'/seo/admin-option/save-scripts']); ?>
    <div class="container mt-2">
        <div class="row">
            <div class="col-12">
                <div class="form-group">
                    <label><?=Yii::t('rabint','اسکریپت های هدر')?></label>
                    <?= \yii\helpers\Html::textarea('header-scripts',$headerScript==null?'':$headerScript->content,['class'=>'form-control']) ?>
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    <label><?=Yii::t('rabint','اسکریپت های فوتر')?></label>
                    <?= \yii\helpers\Html::textarea('footer-scripts',$footerScript==null?'':$footerScript->content,['class'=>'form-control']) ?>
                </div>
            </div>
            <div class="col-12">
                <?= \yii\helpers\Html::submitButton(Yii::t('rabint','ذخیره'),['class'=>'btn btn-success btn-flat']) ?>
            </div>
        </div>
    </div>
<?php \yii\helpers\Html::hiddenInput('type') ?>
<?php ActiveForm::end(); ?>