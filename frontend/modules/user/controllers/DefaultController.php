<?php

namespace frontend\modules\user\controllers;

use common\models\ParentNanny;
use common\models\UserNotify;
use common\models\WidgetCarousel;
use common\modules\file\models\UserFile;
use Yii;
use common\models\Nannies;
use frontend\modules\user\models\AccountForm;
use Intervention\Image\ImageManagerStatic;
use trntv\filekit\actions\DeleteAction;
use trntv\filekit\actions\UploadAction;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use common\models\Refs;
use common\models\Employment;
use yii\web\NotFoundHttpException;



class DefaultController extends Controller
{
    /**
     * @return array
     */
    public function actions()
    {
        return [
            'avatar-upload' => [
                'class' => UploadAction::className(),
                'deleteRoute' => 'avatar-delete',
                'on afterSave' => function ($event) {
                    /* @var $file \League\Flysystem\File */
                    $file = $event->file;
                    $img = ImageManagerStatic::make($file->read())->fit(215, 215);
                    $file->put($img->encode());
                }
            ],
            'avatar-delete' => [
                'class' => DeleteAction::className()
            ]
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@']
                    ]
                ]
            ]
        ];
    }

    public function beforeAction($action)
    {
        $userRoles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        if (array_key_exists('nanny', $userRoles)) {
            Yii::$app->view->params['offslide'] = true;
            Yii::$app->view->params['slider'] = 'nanny-account';
        } elseif (array_key_exists('seeker', $userRoles)) {
            Yii::$app->view->params['offslide'] = true;
            Yii::$app->view->params['slider'] = 'family-account';
        }
        return parent::beforeAction($action);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {

        $accountForm = new AccountForm();
        $accountForm->setUser(Yii::$app->user->identity);
        /** 说明：这里print_r($tmpArr)之后，是一个array，
         * 如果是家长，就是seeker, print_r的结果： Array ([seeker]=> .......)
         * 如果是nanny身份，就是Array ([nanny]=> .......)
         * 
         * getRolesByUser 是获取指定用户（ID）在权限表（rbac_auth_assignment）中的角色（item_name）
         * 返回一个数组，即一个用户可以有多个角色，数组的键为用户所拥有的角色名，值为一个Role对象
         * Role对象是属性为角色表（rbac_auth_item）的字段值
         */
        $tmpArr = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());

        if(array_key_exists('nanny', $tmpArr))
        {

            if ($accountForm->load(Yii::$app->request->post()) && $accountForm->save()) 
            {

                (new \common\lib\SendEmail([
                    'subject' => Yii::t('frontend', 'Password changed for {name}', ['name'=>Yii::$app->name]),
                    'to' => Yii::$app->user->identity->email,
                    'body' => 'Hello ' . \yii\helpers\Html::encode(Yii::$app->user->identity->username) . ', Your password was successfully changed.'
                ]))->handle();

                Yii::$app->session->setFlash('forceUpdateLocale');
                Yii::$app->session->setFlash('alert', [
                    'options' => ['class'=>'alert-success'],
                    'body' => Yii::t('frontend', 'Your account has been successfully saved', [], Yii::$app->user->identity->userProfile->locale)
                ]);
                return $this->refresh();
            }
            $refs=Refs::find()->where(['email' => \Yii::$app->user->identity->email]);
            if(count($refs)<3){
                Yii::$app->session->setFlash('alert', [
                    'options' => ['class'=>'alert-warning'],
                    'body' => Yii::t('frontend', 'Thank you for registering wth NannyCare.com. Please follow the steps below to complete your profile. Nannies must complete the application, upload CPR/First Aid and fill out the background check form to be considered. You can also upload a resume and any references letters and/or certifications you have, since some families may ask us to verify your references (added service we offer families). We will contact you via email when your profile has been approved. As soon as you are approved, you\'re profile will be displayed on our site and you can start connecting with families.
                    Thanks!~Team NannyCare.com', [], Yii::$app->user->identity->userProfile->locale)
                ]);
            }
            $dataProvider = new ActiveDataProvider([
                'query' => $refs,
                'sort' => false
            ]);
            
            $dataProvider1 = new ActiveDataProvider([
                'query' => Employment::find()->where(['email' => \Yii::$app->user->identity->email]),
                'sort' => false
            ]);
            return $this->render('user_index_nanny', ['model'=>$accountForm, 'dataProvider' => $dataProvider, 'dataProvider1' => $dataProvider1, 'refs'=>$refs]);
        } 
        else 
        {
            if ($accountForm->load(Yii::$app->request->post()) && $accountForm->save()) 
            {
                (new \common\lib\SendEmail([
                    'subject' => Yii::t('frontend', 'Password changed for {name}', ['name'=>Yii::$app->name]),
                    'to' => Yii::$app->user->identity->email,
                    'body' => 'Hello ' . \yii\helpers\Html::encode(Yii::$app->user->identity->username) . ', Your password was successfully changed.'
                ]))->handle();
                Yii::$app->session->setFlash('forceUpdateLocale');
                Yii::$app->session->setFlash('alert', [
                    'options' => ['class'=>'alert-success'],
                    'body' => Yii::t('frontend', 'Your account has been successfully saved', [], Yii::$app->user->identity->userProfile->locale)
                ]);
                return $this->refresh();
            }
            return $this->render('user_index_parent', ['model'=>$accountForm]);
        }
        
    }

    
    public function actionMain(){
        $user = Yii::$app->user->identity;
        $model= Nannies::findOne(Yii::$app->user->id);
        if ($model->load($_POST) && $model->save()) {
            Yii::$app->session->setFlash('alert', [
                'options'=>['class'=>'alert-success'],
                'body'=>Yii::t('backend', 'Your profile has been successfully saved', [], $model->locale)
            ]);
            //$user->step=$user->step+1;
            $user->save();
            return $this->redirect('questions-n-schedule');
        }
        return $this->render('main', ['model' => $model]);
    }
    
    public function actionQuestionsNSchedule(){
        $user = Yii::$app->user->identity;
        $model= Nannies::findOne(Yii::$app->user->id);
        if ($model->load($_POST) && $model->save()) {
            Yii::$app->session->setFlash('alert', [
                'options'=>['class'=>'alert-success'],
                'body'=>Yii::t('backend', 'Your profile has been successfully saved', [], $model->locale)
            ]);
            //$user->step=$user->step+1;
            $user->save();
            return $this->redirect('education-n-driving');
        }
        return $this->render('questions-n-schedule', ['model' => $model]);
    }
    public function actionEducationNDriving(){
        $user = Yii::$app->user->identity;
        $model= Nannies::findOne(Yii::$app->user->id);
        if ($model->load($_POST) && $model->save()) {
            Yii::$app->session->setFlash('alert', [
                'options'=>['class'=>'alert-success'],
                'body'=>Yii::t('backend', 'Your profile has been successfully saved', [], $model->locale)
            ]);
            //$user->step=$user->step+1;
            $user->save();
            return $this->redirect('housekeeping');
        }
        return $this->render('education-n-driving', ['model' => $model]);
    }
    public function actionHousekeeping(){
        $user = Yii::$app->user->identity;
        $model= Nannies::findOne(Yii::$app->user->id);
        if ($model->load($_POST) && $model->save()) {
            Yii::$app->session->setFlash('alert', [
                'options'=>['class'=>'alert-success'],
                'body'=>Yii::t('backend', 'Your profile has been successfully saved', [], $model->locale)
            ]);
            //$user->step=$user->step+1;
            $user->save();
            return $this->redirect('about-you');
        }
        return $this->render('housekeeping', ['model' => $model]);
    }
    public function actionAboutYou(){
        $user = Yii::$app->user->identity;
        $model= Nannies::findOne(Yii::$app->user->id);
        if ($model->load($_POST) && $model->save()) {
            Yii::$app->session->setFlash('alert', [
                'options'=>['class'=>'alert-success'],
                'body'=>Yii::t('backend', 'Your profile has been successfully saved', [], $model->locale)
            ]);
            //$user->step=$user->step+1;
            $user->save();
            return $this->redirect('upload-files');
        }
        return $this->render('aboutyou', ['model' => $model]);
    }

    /**
     * 用户上传文件
     */
    public function actionUploadFiles()
    {
        $model = new UserFile();
        return $this->render('upload_files', ['model' => $model]);
    }

    /**
     * 文件列表页
     */
    public function actionUploadFilesList()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => UserFile::find()->where(['user_id' => Yii::$app->user->id, 'status' => UserFile::STATUS_ACTIVE]),
            'pagination' => [
                'pageSize' => 20
            ],
            'sort' => false
        ]);
        return $this->render('upload_files_list', ['dataProvider' => $dataProvider]);
    }

    public function actionView_ref($id)
    {
        $model=$this->findRef($id);
        if ($model->email==\Yii::$app->user->identity->email){
            return $this->render('view_ref', [
                'model' => $model,
            ]);
        }else{
            return $this->redirect(['view_incorrect', 'id' => 1]);
        }
    }
    public function actionUpdate_ref($id)
    {
        $model=$this->findRef($id);
        if ($model->email==\Yii::$app->user->identity->email){
    
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['view_ref', 'id' => $model->id]);
            } else {
                return $this->render('update_ref', [
                    'model' => $model,
                ]);
            }
        }else{
            return $this->redirect(['view_incorrect', 'id' => 1]);
        }
    }
    public function actionCreateReference(){
        $model = new Refs();
        if ($model->load(Yii::$app->request->post())) 
        {
            $model->email=\Yii::$app->user->identity->email;
            if($model->save())
            {
                return $this->redirect('/user/default/index');
            }
            else
            {
                return $this->render('create', ['model' => $model]);
            }
            // return $this->redirect(['view_ref', 'id' => $model->id]);
        } 
        else 
        {
            return $this->render('create', ['model' => $model]);
        }
    }
    protected function findRef($id)
    {
        if (($model = Refs::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    public function actionView_emp($id)
    {
        $model=$this->findEmp($id);
        if ($model->email==\Yii::$app->user->identity->email){
            return $this->render('view_emp', [
                'model' => $model,
            ]);
        }else{
            return $this->redirect(['view_incorrect', 'id' => 1]);
        }
    }
    public function actionUpdate_emp($id)
    {
        $model=$this->findEmp($id);
        if ($model->email==\Yii::$app->user->identity->email){
    
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['view_emp', 'id' => $model->id]);
            } else {
                return $this->render('update_employment', [
                    'model' => $model,
                ]);
            }
        }else{
            return $this->redirect(['view_incorrect', 'id' => 1]);
        }
    }
    public function actionCreateEmployment(){
        $model = new Employment();
        if ($model->load(Yii::$app->request->post())) {
            $model->email= Yii::$app->user->identity->email;
            if(!$model->save()){
                return $this->render('create_employment', [
                    'model' => $model,
                ]);
            }
            return $this->redirect(['view_emp', 'id' => $model->id]);
        } else {
            return $this->render('create_employment', [
                'model' => $model,
            ]);
        }
    }
    
    public function actionGetCredits(){
        
        $tmpArr = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $model = Yii::$app->user;
        //这里意思是，如果是家长，就调转到家长付款页面，保姆的话，就是保姆的付款页面
        if( array_key_exists('nanny', $tmpArr) ){
            return $this->render('get_credits_nanny', [
                'model' => $model
            ]);
        }else{
            // return $this->render('coming_soon');
            return $this->render('get_credits_parent', [
                'model' => $model,
            ]);
        }

        // print_r(Yii::$app->user);
    }

    public function actionNotify($id = null, $role = null, $group_id = null)
    {
        if ($id) {
            if ($role == 'sent' || $role == 'grouped_by_receivers') {
                $model = UserNotify::findOne(['sender_id' => Yii::$app->user->id, 'id' => $id]);
            } else if ($role == 'received') {
                $model = UserNotify::findOne(['receiver_id' => Yii::$app->user->id, 'id' => $id]);
            } else if ($role == 'all') {
                $model = UserNotify::find()->where(['receiver_id' => Yii::$app->user->id])->orWhere(['sender_id' => Yii::$app->user->id])->andWhere(['id' => $id])->one();
            } else{
                $model = UserNotify::findOne(['receiver_id' => Yii::$app->user->id, 'id' => $id]);
            }
            if (!$model) {
                throw new NotFoundHttpException('The requested page does not exist.');
            }
            return $this->render('notify_view', ['model' => $model]);
        }
        if ($group_id) {
            $query = UserNotify::find();
            if ($role == 'grouped_by_senders') {
                $query->where(['sender_id' => $group_id, 'receiver_id' => Yii::$app->user->id]);
            } elseif ($role == 'grouped_by_receivers') {
                $query->where(['receiver_id' => $group_id, 'sender_id' => Yii::$app->user->id]);
            } else {
                throw new NotFoundHttpException('The requested page does not exist.');
            }
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 5
                ]
            ]);
            return $this->render('notify_list', ['dataProvider' => $dataProvider]);
        }

        if ($role == 'sent') {
            $query = UserNotify::find()
                ->where(['sender_id' => Yii::$app->user->id])
                ->orderBy(['created_at' => SORT_DESC]);
        } elseif ($role == 'all') {
            $query = UserNotify::find()
                ->where(['sender_id' => Yii::$app->user->id])
                ->orWhere(['receiver_id' => Yii::$app->user->id])
                ->orderBy(['created_at' => SORT_DESC]);
        } elseif ($role == 'grouped_by_senders') {
            $query = (new \yii\db\Query())
                ->select('tmp.*,username')
                ->from("(select `sender_id`, count(*) as count FROM `user_notify` WHERE `receiver_id`=". Yii::$app->user->id ." GROUP BY `sender_id`) as tmp")
                ->leftJoin('user','`sender_id` = `id`');
        } elseif ($role == 'grouped_by_receivers') {
            $query = (new \yii\db\Query())
                ->select('tmp.*,username')
                ->from("(select `receiver_id`, count(*) as count FROM `user_notify` WHERE `sender_id`=". Yii::$app->user->id ." GROUP BY `receiver_id`) as tmp")
                ->leftJoin('user','`receiver_id` = `id`');
        } else {
            $query = UserNotify::find()
                ->where(['receiver_id' => Yii::$app->user->id])
                ->orderBy(['is_read' => SORT_ASC, 'created_at' => SORT_DESC]);
            UserNotify::updateAll(['is_read' => 1], ['receiver_id' => Yii::$app->user->id, 'is_read' => 0]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 5
            ]
        ]);
        return $this->render('notify_list', ['dataProvider' => $dataProvider]);
    }

    public function actionContact()
    {
        if (!Yii::$app->request->isAjax) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        Yii::$app->response->format = 'json';

        $model = new UserNotify();
        if (parse_url(Yii::$app->request->referrer, PHP_URL_PATH) === \yii\helpers\Url::to(['/user/default/message'])) {
            $model->pid = Yii::$app->request->post('pid');
            $replyNotify = UserNotify::findOne(['id' => $model->pid, 'receiver_id' => Yii::$app->user->id]);
            if (!$replyNotify) {
                throw new NotFoundHttpException('The requested page does not exist.');
            }
            $model->receiver_id = $replyNotify->sender_id;
        } else {
            if (!ParentNanny::findOne(['parentid' => Yii::$app->user->id, 'nannyid' => Yii::$app->request->post('uid')])) {
                return ['status' => false, 'message' => 'Invalid Nanny ID'];
            } else {
                $model->receiver_id = Yii::$app->request->post('uid');
            }
        }
        $model->content = Yii::$app->request->post('content');
        $model->subject = Yii::$app->request->post('subject');
        if ($model->save()) {
            Yii::$app->session->setFlash('alert', [
                'options' => ['class'=>'alert-success'],
                'body' => Yii::t('frontend', 'The message has been sent', [], Yii::$app->user->identity->userProfile->locale)
            ]);
            return ['status' => true];
        } else {
            return ['status' => false, 'message' => 'Unknown Error'];
        }
    }
    
    protected function findEmp($id)
    {
        if (($model = Employment::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    
    
    
    
}
