<nav>
    <div class="nav nav-tabs" id="nav-tab" role="tablist">
        <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true"><?=Yii::t('rabint','صفحه اول')?></a>
        <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false"><?= Yii::t('rabint','ابزار های سئو') ?></a>
        <a class="nav-item nav-link" id="nav-contact-tab" data-toggle="tab" href="#nav-contact" role="tab" aria-controls="nav-contact" aria-selected="false"><?= Yii::t('rabint','آنالیز')?></a>
    </div>
</nav>
<div class="tab-content" id="nav-tabContent">
    <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
        <?= $this->render('home') ?>
    </div>
    <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
        که فیلد های مانند google webmaster tools و google console و را دارد که باعث ثبت رکورد از نوع script در جدول اولیه خواهد شد
    </div>
    <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">
        صرفا شامل لینک هایی به سایت های بررسی کننده وضعیت سيو و بهینگی سایت است که کاربر با کلیک بروی آنها وارد صفحه آنالیز سایت می شود
    </div>
</div>