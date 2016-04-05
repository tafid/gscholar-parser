<?php

namespace app\controllers;

use app\models\CitationSearch;
use Exception;
use serhatozles\simplehtmldom\SimpleHTMLDom;
use Yii;
use app\models\Citation;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * CitationController implements the CRUD actions for Citation model.
 */
class CitationController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
        ];
    }

    /**
     * Lists all Citation models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CitationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'model' => new Citation(['scenario' => 'import-data'])
        ]);
    }

    /**
     * Displays a single Citation model.
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
     * Creates a new Citation model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Citation();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Citation model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Citation model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Citation model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Citation the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Citation::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionExport()
    {
        Yii::$app->response->sendContentAsFile(Citation::exportToFile(), sprintf('citation_%s.txt', date('Y-m-d')));
    }

    public function actionRefreshData()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if ($request->isPost && $request->isAjax) {
            (new Citation())->fetchData();
            return ['status' => true];
        }
        Yii::$app->end();
    }

    public function actionImportData()
    {
        $model = new Citation(['scenario' => 'import-data']);
        if (Yii::$app->request->isPost) {
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($model->file && $model->validate()) { //  && $model->validate()
                $userMessages = [];
                $content = file_get_contents($model->file->tempName);
                $userIds = str_getcsv($content, "\n"); //parse the rows
                foreach ($userIds as $uid) {
                    $messages = Html::tag('span', Yii::t('app', 'Added'), ['class' => 'text-success']);
                    $rec = new Citation(['scenario' => 'insert']);
                    $rec->user_id = $uid;
                    if ($rec->validate()) {
//                        $rec->save();
                    } else {
                        $messages = Html::tag('span', $rec->getFirstError('user_id'), ['class' => 'text-danger']);
                    }
                    $userMessages[] = Yii::t('app', '{user} - {status}', ['user' => $uid, 'status' => $messages]);
                }
                \Yii::$app->getSession()->setFlash('info', implode('<br>', $userMessages));
            } else {
                $errors = $model->getErrors('file');
                \Yii::$app->getSession()->setFlash('error', implode('<br>', $errors));
            }
        }
        $this->redirect(['index']);
    }
}
