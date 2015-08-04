<?php

namespace sya\ecommerce\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;

class BaseController extends \yii\web\Controller{
    
    // namespace of model use
    public $namespaceModel = '\sya\ecommerce\models\Order';
    
    public function actionIndex(){
        $assign = $this->getDataProvider();
        
        // Set title page
        Yii::$app->view->title = Yii::t($this->module->id, 'Order');
        Yii::$app->view->params['breadcrumbs'][] = Yii::$app->view->title;

        return $this->render('index', $assign);
    }
    
    public function actionCreate(){
        // Get namespace of model
        $namespaceModel = $this->namespaceModel;
        
        $model = new $namespaceModel;
        $model->scenario = 'create';
        
        if ($model->load(Yii::$app->request->post()) AND $model->save()){
            $this->redirect([ArrayHelper::getValue($namespaceModel::buildActions(), $namespaceModel::ACTION_INDEX), 'id' => $model->_id]);
        }
        
        Yii::$app->view->title = Yii::t('common', 'Create') . ' ' . Yii::t($this->module->id, 'Product');
        Yii::$app->view->params['breadcrumbs'][] = ['label' => Yii::t($this->module->id, 'Product')];
        
        return $this->render('form', ['model' => $model]);
    }
    
    /**
     * Deletes an existing model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        // Get namespace of model
        $namespaceModel = $this->namespaceModel;
        
        $this->findModel($id)->delete();
        
        return $this->redirect([ArrayHelper::getValue($namespaceModel::buildActions(), $namespaceModel::ACTION_INDEX)]);
    }
    
    /**
     * Function get data model with dataprovider
     * @return array
     */
    protected function getDataProvider(){
        $queryParams = Yii::$app->request->getQueryParams();
        $searchModel = new $this->namespaceModel;
        $searchModel->scenario = 'search';
        $dataProvider = $searchModel->search($queryParams);
        
        return [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ];
    }

    /**
     * Finds the model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        // Get namespace of model
        $namespaceModel = $this->namespaceModel;
        
        if (($model = $namespaceModel::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
}