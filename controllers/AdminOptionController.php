<?php

namespace rabint\seo\controllers;

use Yii;
use rabint\seo\models\Option;
use rabint\seo\models\SearchOption;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * AdminOptionController implements the CRUD actions for Option model.
 */
class AdminOptionController extends \rabint\controllers\AdminController {

    const BULK_ACTION_SETDRAFT = 'bulk-draft';
    const BULK_ACTION_SETPUBLISH = 'bulk-publish';
    const BULK_ACTION_DELETE = 'bulk-delete';
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return parent::behaviors([
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'bulk' => ['POST'],
                ],
            ],
        ]);
    }

     /**
     * list of bulk action as static
     * @return array
     */
    public static function bulkActions() {
        return [
            //static::BULK_ACTION_SETPUBLISH => ['title' =>  Yii::t('rabint', 'set publish'),'class'=>'success','icon'=>'fas fa-check'],
            //static::BULK_ACTION_SETDRAFT => ['title' =>  Yii::t('rabint', 'set draft'),'class'=>'warning','icon'=>'fas fa-times'],
            static::BULK_ACTION_DELETE => ['title' =>  Yii::t('rabint', 'delete all'), 'class' => 'danger', 'icon' => 'fas fa-trash-alt'],
        ];
    }
   
    
    /**
     * bulk action
     * @return mixed
     */
    public function actionBulk($action)
    {

        $request = Yii::$app->request;
        $pks = explode(',', $request->post('pks')); // Array or selected records primary keys

        if (!isset(static::bulkActions()[$action])) {
            Yii::$app->session->setFlash('warning',  Yii::t('rabint', 'Bulk action Not found!'));
            return $this->redirect(\rabint\helpers\uri::referrer());
        }
        $selection = (array) $pks;
        $err = 0;
        switch ($action) {
            case static::BULK_ACTION_SETPUBLISH:
                if (Option::updateAll(['status' => Option::STATUS_DRAFT], ['id' => $selection])) {
                    Yii::$app->session->setFlash('success',  Yii::t('rabint', 'Bulk action was successful'));
                } else {
                    $err++;
                }
                break;
            case static::BULK_ACTION_SETDRAFT:
                if (Option::updateAll(['status' => Option::STATUS_DRAFT], ['id' => $selection])) {
                    Yii::$app->session->setFlash('success',  Yii::t('rabint', 'Bulk action was successful'));
                } else {
                    $err++;
                }
                break;
            case static::BULK_ACTION_DELETE:
                if (Option::deleteAll(['id' => $selection])) {
                    Yii::$app->session->setFlash('success',  Yii::t('rabint', 'Bulk action was successful'));
                } else {
                    $err++;
                }
                break;
        }
        if ($err) {
            Yii::$app->session->setFlash('danger',  Yii::t('rabint', 'عملیات ناموفق بود'));
        }

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose' => true, 'forceReload' => '#ajaxCrudDatatable'];
        }
        return $this->redirect(\rabint\helpers\uri::referrer());
    }

    /**
     * Lists all Option models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SearchOption();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Option model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Option model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {

        $model = new Option();

        $request = Yii::$app->request;

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success',  Yii::t('rabint', 'Item successfully created.'));

                if ($request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'forceReload' => '#ajaxCrudDatatable',
                        'title' =>  Yii::t('rabint', 'Create new').' '. Yii::t('rabint', 'Option'),
                        'content' => '<span class="text-success">' .  Yii::t('rabint', 'Create {item} success', [
    'item' => '<?= $modelClass ?>',
]) . '</span>',
                        'footer' => Html::button( Yii::t('rabint', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::a( Yii::t('rabint', 'Create More'), ['create'], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])

                    ];
                }
                return $this->redirect(['index']);
            } else {
                Yii::$app->session->setFlash('danger', \rabint\helpers\str::modelErrors($model->errors));
            }
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Option model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {

        
        $model = $this->findModel($id);

        $request = Yii::$app->request;

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success',  Yii::t('rabint', 'Item successfully updated.'));

                if ($request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    [
                        'forceReload' => '#ajaxCrudDatatable',
                        'title' =>  Yii::t('rabint', 'Updating').' '. Yii::t('rabint', 'Option'),
                        'content' => $this->renderAjax('view', [
                            'model' => $model,
                        ]),
                        'footer' => Html::button( Yii::t('rabint', 'Close'), ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::a('Edit', [ Yii::t('rabint', 'update'), 'id' => $id], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
                    ];
                }
                return $this->redirect(['index']);
            } else {
                Yii::$app->session->setFlash('danger', \rabint\helpers\str::modelErrors($model->errors));
            }
        }
        return $this->render('update', [
            'model' => $model,
        ]);
                    
    }

    /**
     * Deletes an existing Option model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {

        $request = Yii::$app->request;

        if($this->findModel($id)->delete()){
            Yii::$app->session->setFlash('success', Yii::t('rabint', 'Item successfully deleted.'));
        }else{
            Yii::$app->session->setFlash('danger', Yii::t('rabint', 'Unable to delete item.'));
        }

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose' => true, 'forceReload' => '#ajaxCrudDatatable'];
        } 

        return $this->redirect(['index']);

    }

    /**
     * Finds the Option model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Option the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Option::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(\Yii::t('rabint', 'The requested page does not exist.'));
        }
    }


    public function actionSpecialOptions(){
        if($post = Yii::$app->request->post()){
            //  'description','key_words','about','Writer','genre'
            $content = [
                'keywords' => $post['key_words'],
                'description' => $post['description'],
                'about' => $post['about'],
                'author' => $post['Writer'],
                'genre' => $post['genre']
            ];
            $keyWords=self::saveOption($post['key_words'],'home-meta-keywords');
            $description=self::saveOption($post['description'],'home-meta-description');
            $jsonId=self::saveOption(json_encode($content),'home-schema-json-id');
            if(!($keyWords===true && $description===true && $jsonId===true)){
                $message = [];
                $message[] = is_array($keyWords)?$keyWords:[];
                $message[] = is_array($description)?$description:[];
                $message[] = is_array($jsonId)?$jsonId:[];
                Yii::$app->session->setFlash('danger', \rabint\helpers\str::modelErrors($message));
            }else{
                Yii::$app->session->setFlash('success', Yii::t('rabint','موارد با موفقیت ثبت شد!'));
            }
        }
        $headerScript = Option::find()->where(['name'=>'header-scripts'])->one();
        $footerScript = Option::find()->where(['name'=>'footer-scripts'])->one();
        return render('special-options/index.php',compact(['headerScript','footerScript']));
    }

    public function actionSaveScripts(){
//        header-scripts,footer-scripts
        if($post = Yii::$app->request->post()){
            $header=Option::find()->where(['name'=>'header-scripts'])->one();
            if($header==null){
                $header = new Option();
                $header->name = 'header-scripts';
                $header->location = Option::LOCATION_HEAD;
                $header->type = Option::META_TYPE_SCRIPT;
                $header->route = '*';
            }
            $header->content = $post['header-scripts'];
            $header->save();

            $footer=Option::find()->where(['name'=>'footer-scripts'])->one();
            if($footer==null){
                $footer = new Option();
                $footer->name = 'footer-scripts';
                $footer->location = Option::LOCATION_FOOTER;
                $footer->type = Option::META_TYPE_SCRIPT;
                $footer->route = '*';
            }
            $footer->content = $post['footer-scripts'];
            $footer->save();
        }
        $headerScript = Option::find()->where(['name'=>'header-scripts'])->one();
        $footerScript = Option::find()->where(['name'=>'footer-scripts'])->one();
        return render('special-options/index.php',compact(['headerScript','footerScript']));
    }

    public static function saveOption($content,$key){
        $default = Option::defultItems()[$key];
        $model=Option::find()->where(['name'=>$default['name']])->one();
        if($model==null){
            $model = new Option();
        }
        $model->name = $default['name'];
        $model->type = $default['type'];
        $model->route = $default['route'];
        $model->location = $default['location'];
        if(empty( $default['target'])){
            $model->content = $content;
        }else{
            $array = $default['default'];
            $array[$default['target']] = $content;
            $model->content = json_encode($array);
        }
        if(!$model->save()){
            $message[]=$model->errors;
        }
        if(!empty($message)){
            return $message;
        }
        return true;
    }

    public function actionModuleOption(){
        $config = Option::getConfigArray();
        if($post=Yii::$app->request->post()){
            $config['pingBack']=isset($post['pingBack'])&&$post['pingBack']==true;
            $config['seo']=isset($post['seo'])&&$post['seo']==true;
            $config['compressAssets']=isset($post['compressAssets'])&&$post['compressAssets']==true;
            Option::setConfigArray($config);
        }
        return $this->render('module-option',compact(['config']));
    }
}