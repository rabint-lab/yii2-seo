<?php

use yii\bootstrap4\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Option */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('rabint', 'Options'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$isModalAjax = Yii::$app->request->isAjax;

$this->context->layout = "@themeLayouts/full";

?>


<div class="box-view option-view"  id="ajaxCrudDatatable">
    <h2 class="ajaxModalTitle" style="display: none"><?=  $this->title; ?></h2>
    <div class="clearfix"></div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card block block-rounded <?= $isModalAjax?'ajaxModalBlock':'';?> ">
                <div class="card-header block-header block-header-default">
                    <h3 class="block-title">
                        <?= Html::encode($this->title) ?>
                    </h3>
                    <div class="block-action float-left">
                            <?= Html::a(Yii::t('rabint', 'Create Option'), ['create'], ['class' => 'btn btn-info btn-sm  btn-noborder']) ?>
                            <?= Html::a(Yii::t('rabint', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-success btn-sm  btn-noborder']) ?>
                            <?= Html::a(Yii::t('rabint', 'Delete'), ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger btn-sm btn-noborder',
                            'data' => [
                            'confirm' => Yii::t('rabint', 'Are you sure you want to delete this item?'),
                            'method' => 'post',
                            ],
                            ]) ?>
                    </div>
                </div>
                <div class="card-body block-content block-content-full">

                    <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        //'created_at' => [
                        //       'attribute' => 'created_at',
                        //    'value' => \rabint\helpers\locality::jdate('j F Y H:i:s', $model->created_at),
                        //    ],
                        //     'transactioner' => [
                        //         'attribute' => 'transactioner',
                        //        'value' => isset($model->transactionerUser)?$model->transactionerUser->displayName:null,
                        //     ],
                        //     'amount',
                        //      [
                        //         'attribute' => 'gateway',
                        //           'value' => function() use($model){
                        //              $value =$model->gateway;
                        //               $enum = \rabint\finance\Config::paymentGateways();
                        //             $data = isset($enum[$value]['title']) ? $enum[$value]['title'] : $value;
                        //             $class = isset($enum[$value]['class']) ? $enum[$value]['class'] : 'default';
                        //             return '<span class="badge badge-' . $class . '">' . $data . '</span>';
                        //         },
                        //         'format' => 'raw',
                        //     ],
                                'id',
            'route',
                        [
                            'class'=>'\kartik\grid\DataColumn',
                            'attribute'=>'type',
                            'value'=>function($model){
                                return \rabint\seo\models\Option::metaTagTypes()[$model->type]['title'];
                            }
                        ],
            'name',
            'content:ntext',
                        [
                            'class'=>'\kartik\grid\DataColumn',
                            'attribute'=>'location',
                            'value'=>function($model){
                                return $model->location == 1 ? Yii::t('rabint','هدر'):Yii::t('rabint','فوتر');
                            }
                        ],
//                        'linked',
//            'created_at',
//            'updated_at',
//            'created_by',
//            'updated_by',
                    ],
                    ]) ?>

                </div>
            </div>
        </div>
    </div>
</div>
