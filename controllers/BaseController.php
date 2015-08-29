<?php

namespace sya\ecommerce\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use sya\ecommerce\Ecommerce;
use yii\helpers\ArrayHelper;

class BaseController extends \yii\web\Controller{
    
    public function actionIndex(){
        $assign = $this->getDataProvider();
        
        // Set title page
        Yii::$app->view->title = Yii::t($this->module->id, 'Order');
        Yii::$app->view->params['breadcrumbs'][] = Yii::$app->view->title;

        return $this->render('index', $assign);
    }
    
    public function actionCreate(){
        // Get namespace of model
        $ecommerce = Ecommerce::module();
        
        // Get namespace product
        $productModule = ArrayHelper::getValue($ecommerce->productTable, 'productModule');
        
        // Get search function product
        $productSearch = ArrayHelper::getValue($ecommerce->productTable, 'productSearch', 'search');
        
        // Get data product
        $productData = $this->getGridViewModel($productModule, $productSearch);
        
        // Get namespace customer
        $customerModule = ArrayHelper::getValue($ecommerce->customerTable, 'customerModule');
        
        // Get search function customer
        $customerSearch = ArrayHelper::getValue($ecommerce->customerTable, 'customerSearch', 'search');
        
        // Get data customer
        $customerData = $this->getGridViewModel($customerModule, $customerSearch);
        
        // Order module load
        $model = new $ecommerce->itemModule;
        $model->scenario = 'create';
        
        if ($model->load(Yii::$app->request->post()) AND $model->save()){
            $this->redirect(['update', 'id' => $model->_id]);
        }
        
        Yii::$app->view->title = Yii::t($this->module->id, 'Create') . ' ' . Yii::t($this->module->id, 'Order');
        Yii::$app->view->params['breadcrumbs'][] = ['label' => Yii::t($this->module->id, 'Order'), 'url' => ['index']];
        Yii::$app->view->params['breadcrumbs'][] = ['label' => Yii::$app->view->title];
        
        return $this->render('@vendor/sya/yii2-ecommerce/views/base/form', [
            'model' => $model,
            'productSearchModel' => ArrayHelper::getValue($productData, 'searchModel'),
            'productDataProvider' => ArrayHelper::getValue($productData, 'dataProvider'),
            'customerSearchModel' => ArrayHelper::getValue($customerData, 'searchModel'),
            'customerDataProvider' => ArrayHelper::getValue($customerData, 'dataProvider'),
            'productColumns' => ArrayHelper::getValue($ecommerce->productTable, 'productColumns', []),
            'customerColumns' => ArrayHelper::getValue($ecommerce->customerTable, 'customerColumns', []),
            'template' => $model->generateProductOrder($model->product, $model->shipping)
        ]);
    }
    
    public function actionUpdate($id){
        // Get namespace of model
        $ecommerce = Ecommerce::module();
        
        // Get namespace product
        $productModule = ArrayHelper::getValue($ecommerce->productTable, 'productModule');
        
        // Get search function product
        $productSearch = ArrayHelper::getValue($ecommerce->productTable, 'productSearch', 'search');
        
        // Get data product
        $productData = $this->getGridViewModel($productModule, $productSearch);
        
        // Get namespace customer
        $customerModule = ArrayHelper::getValue($ecommerce->customerTable, 'customerModule');
        
        // Get search function customer
        $customerSearch = ArrayHelper::getValue($ecommerce->customerTable, 'customerSearch', 'search');
        
        // Get data customer
        $customerData = $this->getGridViewModel($customerModule, $customerSearch);
        
        // Order module load
        $model = $this->findModel($id);
        $model->scenario = 'create';
        
        if ($model->load(Yii::$app->request->post()) AND $model->save()){
            $this->redirect(['update', 'id' => $model->_id]);
        }
        
        Yii::$app->view->title = Yii::t($this->module->id, 'Update') . ' ' . Yii::t($this->module->id, 'Order') . ': ' . $model->ecommerce_id;
        Yii::$app->view->params['breadcrumbs'][] = ['label' => Yii::t($this->module->id, 'Order'), 'url' => ['index']];
        Yii::$app->view->params['breadcrumbs'][] = ['label' => Yii::$app->view->title];
        
        return $this->render('@vendor/sya/yii2-ecommerce/views/base/form', [
            'model' => $model,
            'productSearchModel' => ArrayHelper::getValue($productData, 'searchModel'),
            'productDataProvider' => ArrayHelper::getValue($productData, 'dataProvider'),
            'customerSearchModel' => ArrayHelper::getValue($customerData, 'searchModel'),
            'customerDataProvider' => ArrayHelper::getValue($customerData, 'dataProvider'),
            'productColumns' => ArrayHelper::getValue($ecommerce->productTable, 'productColumns', []),
            'customerColumns' => ArrayHelper::getValue($ecommerce->customerTable, 'customerColumns', []),
            'template' => $model->generateProductOrder($model->product, $model->shipping),
            'templateCustomer' => $model->generateCustomerOrder()
        ]);
    }
    
    /**
     * Deletes an existing model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id){
        $model = $this->findModel($id);
        $model->status = \sya\ecommerce\Module::STATUS_EMPTY;
        $model->save();
        
        return $this->redirect(['index']);
    }
    
    /**
     * Function get data model with dataprovider
     * @return array
     */
    protected function getDataProvider(){
        $queryParams = Yii::$app->request->getQueryParams();
        $ecommerce = Ecommerce::module();
        $searchModel = new $ecommerce->itemModule;
        $searchModel->scenario = 'search';
        $dataProvider = $searchModel->search($queryParams);
        
        return [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ];
    }
    
    /**
     * Function get data of module
     * @param type $module Namespace of module
     * @param type $functionName Name of function return data module
     * @return array Array search model and dataprovider of $module
     * @throws \yii\base\Exception
     */
    protected function getGridViewModel($module, $functionName) {
        // Check have exists product module namespace
        if (!empty($module)){
            $queryParams = Yii::$app->request->getQueryParams();
            $searchModel = new $module;
            $searchModel->scenario = 'search';
            $dataProvider = $searchModel->$functionName($queryParams);
            
            return [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider
            ];
        } else {
            throw new \yii\base\Exception('Module that have not been declared.', '500');
        }
    }

    /**
     * Finds the model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id){
        // Get namespace of model
        $ecommerce = Ecommerce::module();
        $namespaceModel = $ecommerce->itemModule;
        
        if (($model = $namespaceModel::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
}