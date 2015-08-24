<?php

namespace sya\ecommerce\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use sya\ecommerce\Ecommerce;
use yii\web\NotFoundHttpException;
use sya\ecommerce\Module;

class AjaxController extends \yii\web\Controller{
    
    /**
     * Action add product in order with ajax
     */
    public function actionAddproduct(){
        // List product include: id of product and quantity of product.
        $data = Yii::$app->request->post('data');
        $shipping = Yii::$app->request->post('shipping', 0);
        $products = explode(',', $data);
        $list_product = [];
        
        // Check list product must be is array and non empty
        if (is_array($products) AND !empty($products)) {
            // List id of product by array
            $ids = [];
            foreach ($products as $product) {
                $product = explode(':', $product);
                $id = ArrayHelper::getValue($product, 0, '');
                $quantity = ArrayHelper::getValue($product, 1);
                $ids[] = $id;
                
                if (!empty($quantity)) {
                    $list_product[$id] = [
                        'id' => $id,
                        'quantity' => $quantity,
                    ];
                }
            }
            
            // Get namespace of model
            $ecommerce = Ecommerce::module();
            
            // Namespace of product model
            $productModel = $ecommerce->productModule;
            
            // Get infomation of product
            $model = $productModel::find()->where([
                '_id' => [
                    '$in' => $ids
                ]
            ])->all();
            
            foreach ($model as $product) {
                $productInfomation = [
                    'title' => $product->title,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'is_marketing' => $product->is_marketing,
                ];
                $list_product[$product->_id] = ArrayHelper::merge($list_product[$product->_id], $productInfomation);
            }
            
            $modelOrder = new $ecommerce->itemModule;
            echo $modelOrder->generateProductOrder($list_product, $shipping);
        }
    }
    
    /**
     * Action add note admin in order
     */
    public function actionAddnoteadmin(){
        $id = Yii::$app->request->post('id');
        $note_admin_content  = Yii::$app->request->post('note_admin_content');
        
        $model = $this->findModel($id);
        
        if (!empty($note_admin_content)){
            $model->note_admin_content = $note_admin_content;
            $model->save();
        }
        
        echo $model->generateNoteAdmin();
    }
    
    /**
     * Action change status order
     */
    public function actionChangestatus(){
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        
        $model = $this->findModel($id);
        
        $model->status = $status;
        $model->save();
        
        echo json_encode([
            'status' => Module::getListStatus($model->status),
            'log' => $model->generateLogOrder()
        ]);
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