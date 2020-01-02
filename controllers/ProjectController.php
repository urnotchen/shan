<?php

namespace app\controllers;

use app\assets\AppAsset;
use app\common\actions\Upload;
use app\common\helpers\SidebarActiveWidget;
use app\common\helpers\SidebarItems;
use Yii;
use app\models\Project;
use app\models\search\tProjectSearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * ProjectController implements the CRUD actions for Project model.
 */
class ProjectController extends Controller
{

    public function init()
    {
        parent::init();
        /* 注入左侧栏分项 */

        $this->on(\yii\base\Module::EVENT_BEFORE_ACTION, [$this, 'handleSidebarItems']);

        Yii::configure(Yii::$app, [
            'components' => [
                'timeFormatter' => [
                    'class' => 'app\components\TimeFormatter',
                ],
            ],
        ]);
    }

    public function handleSidebarItems($event)
    {/*{{{*/
        $items = Yii::$app->sidebarItems->getItems();


            $items[] = $this->getSystemItems();
        //  $items[] = $this->getDeveloperItems();


        Yii::$app->sidebarItems->setItems($items);

    }/*}}}*/

    public function getSystemItems()
    {/*{{{*/
        return [
            'label' => '<span class="fa fa-gear (alias)"></span> 系统设置',
            'items' =>[
                [
                    'label' => '微博帐号',
                    'url' => ['/weiboc/account/index'],
                ],
                [
                    'label' => '转发率邮件提醒',
                    'url' => ['/weiboc/option/forward-reminder'],
                ],
                [
                    'label' => '微博私信',
                    'url' => ['/weiboc/weibo-im/manage'],
                ],
                [
                    'label' => '评论管理',
                    'url' => ['/weiboc/weibo-comment/manage'],
                ],
            ],
            'options' => [
                'class' => SidebarActiveWidget::widget([
                    'activeArr' => [
                        'account', 'cookie', 'category', 'author', 'option','weibo-im', 'weibo-comment'
                    ],
                    'activeControllerArr' => [
                        'weiboc',
                    ],
                ]),
            ],
        ];
    }/*}}}*/
    public function handleSidebarItems1()
    {


        $items = SidebarItems::getItems();
        $items[] = [
            'label' => '<span class="fa fa-file-code-o"></span> 公益项目 ',
            'items' =>[
                [
                    'label' => '微博帐号',
                    'url' => ['/weiboc/account/index'],
                ],
                [
                    'label' => '转发率邮件提醒',
                    'url' => ['/weiboc/option/forward-reminder'],
                ],
                [
                    'label' => '微博私信',
                    'url' => ['/weiboc/weibo-im/manage'],
                ],
                [
                    'label' => '评论管理',
                    'url' => ['/weiboc/weibo-comment/manage'],
                ],
            ],
//            'url' => '/project/index',
            'options' => [
                'class' => SidebarActiveWidget::widget([
                    'activeArr' => ['account-exam'],
                    'activeControllerArr' => [
                        'check',
                    ],
                ])
            ],
        ];


        SidebarItems::setItems($items);

    }
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'password','create-pt','upload','upload-img'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'matchCallback' => function ($rule, $action) {
//                            return Yii::$app->getUser()->can(\backend\modules\rights\components\Rights::PERMISSION_USER_MANAGE);
//                        },
                    ],

                ],
            ],
        ];
    }

    public function actions() {
        return [
            'upload' => [
                'class' => Upload::className(),
                'uploadBasePath' => '@webroot', //file system path ps:当前运行应用的 Web 入口目录
                'uploadBaseUrl' => '@web', //web path @web ps:当前运行应用的根 URL
                'csrf' => true, //csrf校验

                'configPatch' => [
                    'imageMaxSize' =>  2 * 1024 * 1024, //图片
                    'scrawlMaxSize' => 500 * 1024, //涂鸦
                    'catcherMaxSize' => 500 * 1024, //远程
                    'videoMaxSize' => 1024 * 1024, //视频
                    'fileMaxSize' => 1024 * 1024, //文件
                    'imageManagerListPath' => '/', //图片列表
                    'fileManagerListPath' => '/', //文件列表
                ],

                // OR Closure
                'pathFormat' => [
                    'imagePathFormat' => '/img/project/{yyyy}{mm}{dd}/{time}{rand:6}',
                    'scrawlPathFormat' => '/uploads/images/{yyyy}{mm}{dd}/{time}{rand:6}',
                    'snapscreenPathFormat' => '/uploads/images/{yyyy}{mm}{dd}/{time}{rand:6}',
                    'catcherPathFormat' => '/uploads/images/{yyyy}{mm}{dd}/{time}{rand:6}',
                    'videoPathFormat' => '/uploads/videos/{yyyy}{mm}{dd}/{time}{rand:6}',
                    'filePathFormat' => '/uploads/files/{yyyy}{mm}{dd}/{time}{rand:6}',
                ],
                'configPatch' => [
                    'imageManagerListPath' => 'uploads/images', //图片列表
                    'fileManagerListPath' => 'uploads/images', //文件列表
                ],

                'beforeUpload' => function($action) {
                    //throw new \yii\base\Exception('error message'); //break
                },
                'afterUpload' => function($action) {
                    /*@var $action \xj\ueditor\actions\Upload */
//                    gettype($action->result);


                },
            ],
        ];
    }
    /**
     * Lists all Project models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new tProjectSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Project model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Project model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Project();
//        $image =  UploadedFile::getInstance($model,'img_url');
//        $ext = $image->getExtension();
//        $imageName = time().rand(100,999).'.'.$ext;
//        $image->saveAs('project/'.date("Ymd".$imageName);//设置图片的存储位置
//        $model->img_url =
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }
    public function actionUploadImg(){
        Yii::$app->response->format = 'json';
        $dir_base = "img/project/".date("Ymd")."/";     //文件上传根目录
        if (!is_dir($dir_base)) {
            mkdir(iconv("UTF-8", "GBK", $dir_base),0777,true);
        }
            //没有成功上传文件，报错并退出。
        if (empty($_FILES['myfile'])) {
            echo "die";
            exit(0);
        }

//        $filename = time().rand(11111,99999);
        $filename = $dir_base . uniqid().strrchr($_FILES["myfile"]["name"],'.');
        $res = move_uploaded_file($_FILES["myfile"]["tmp_name"], $filename);

        return ['show_url' => APP_DOMAIN_SCHEMA.APP_BASE_DOMAIN.'/'.$filename,'base_url' => $filename];
    }
    /**
     * Updates an existing Project model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }
        $model->time_range = date("Y-m-d",$model->begin_at).' - '.date("Y-m-d",$model->end_at);
        $model->img_url = APP_DOMAIN_SCHEMA.APP_BASE_DOMAIN.'/'.$model->img_url;
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Project model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Project model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Project the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Project::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
