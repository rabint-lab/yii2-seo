<?php
use yii\bootstrap4\ActiveForm;
//فعال و غیر فعال بودن pingback
//غیر فعال شدن کل سئو (جهت جلو گیری از index شدن سایت )
//$config;
$form = ActiveForm::begin(); ?>
    <div class="container mt-2">
        <div class="row">
            <div class="col-12">
                <div class="form-group">
                    <?= \yii\helpers\Html::checkbox('pingBack',isset($config['pingBack'])?$config['pingBack']:false) ?>
                    <label><?=Yii::t('rabint','آیا میخواهید بروزرسانی سایت را به اطلاع موتور های جستجو برسانید؟(پینگ بک)')?></label>
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    <?= \yii\helpers\Html::checkbox('seo',isset($config['seo'])?$config['seo']:false) ?>
                    <label><?=Yii::t('rabint','آیا میخواهید متا تگ ها و اسکیما ها اعمال شوند؟')?></label>
                </div>
            </div><div class="col-12">
                <div class="form-group">
                    <?= \yii\helpers\Html::checkbox('compressAssets',isset($config['compressAssets'])?$config['compressAssets']:false) ?>
                    <label><?=Yii::t('rabint','آیا asset ها فشرده شوند؟')?></label>
                </div>
            </div><div class="col-12">
                <div class="form-group">
                    <?= \yii\helpers\Html::checkbox('index',isset($config['index'])?$config['index']:false) ?>
                    <label><?=Yii::t('rabint','آیا سایت به موتور های جستجو نمایش داده شود؟')?></label>
                </div>
            </div>
            <div class="col-12">
                <?= \yii\helpers\Html::submitButton(Yii::t('rabint','ذخیره'),['class'=>'btn btn-success btn-flat']) ?>
            </div>
        </div>
    </div>
<?php ActiveForm::end(); ?>