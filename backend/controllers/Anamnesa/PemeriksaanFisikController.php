<?php

namespace backend\controllers\Anamnesa;

use Yii;
use yii\web\Response;
use backend\models\PemeriksaanFisik;
use yii\db\Query;
use yii\helpers\Json;

class PemeriksaanFisikController extends AnamnesaController{

 public function actionCreate($id){
     $model = $this->findModel($id);
       
     //   if ($model->load(Yii::$app->request->post()) && $model->save()) {
       //     return $this->redirect(['view', 'id' => $model->id]);
       // } else {
            return $this->render('create', [
                'model' => $model,
               
                
            ]);
        //}
     
 }
 
 public function actionSaveStatusterkini($id){
     $model = $this->findModel($id);
       $return =0;
      if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        $model->save();

            $return=1; 
        }
    //$datakeluhan =  $app->getRequest()->getQueryParam('keluhan');
        
         
        return Json::encode($return);
     
 }
 
  public function actionSaveTinggibadan($id){
     $model = $this->findModel($id);
       $return =0;
      if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        $model->save();

            $return=1; 
        }
    //$datakeluhan =  $app->getRequest()->getQueryParam('keluhan');
        
         
        return Json::encode($return);
     
 }
 
  public function actionPopupGcs($id){
     $model = $this->findModel($id);
       
     //   if ($model->load(Yii::$app->request->post()) && $model->save()) {
       //     return $this->redirect(['view', 'id' => $model->id]);
       // } else {
            return $this->renderAjax('_popupGcs', [
                'model' => $model,
               
                
            ]);
        //}
     
 }
 
   public function actionPopupTinggibadan($id){
     $model = $this->findModel($id);
       
     //   if ($model->load(Yii::$app->request->post()) && $model->save()) {
       //     return $this->redirect(['view', 'id' => $model->id]);
       // } else {
            return $this->renderAjax('_popupTinggibadan', [
                'model' => $model,
               
                
            ]);
        //}
     
 }
 
 public function actionTandaVital(){
     
     $model = $this->findModel( Yii::$app->getRequest()->getQueryParam('id'));
       Yii::$app->response->format = Response::FORMAT_JSON;
     //   if ($model->load(Yii::$app->request->post()) && $model->save()) {
       //     return $this->redirect(['view', 'id' => $model->id]);
       // } else {
            return $this->renderAjax('_tandaVital', [
                'model' => $model,
               
                
            ]);
        //}
     
 }
 
    public function actionPopupNadi(){
     $model = new PemeriksaanFisik();
       
     //   if ($model->load(Yii::$app->request->post()) && $model->save()) {
       //     return $this->redirect(['view', 'id' => $model->id]);
       // } else {
            return $this->renderAjax('_popupNadi', [
                'model' => $model,
               
                
            ]);
        //}
     
 }
 
  public function actionPopupPernapasan(){
     $model = new PemeriksaanFisik();
       
     //   if ($model->load(Yii::$app->request->post()) && $model->save()) {
       //     return $this->redirect(['view', 'id' => $model->id]);
       // } else {
            return $this->renderAjax('_popupPernapasan', [
                'model' => $model,
               
                
            ]);
        //}
     
 }
 
  
  public function actionPopupSuhu(){
     $model = new PemeriksaanFisik();
       
     //   if ($model->load(Yii::$app->request->post()) && $model->save()) {
       //     return $this->redirect(['view', 'id' => $model->id]);
       // } else {
            return $this->renderAjax('_popupSuhu', [
                'model' => $model,
               
                
            ]);
        //}
     
 }
 
 public function actionEvaluasi(){
      $model = $this->findModel( Yii::$app->getRequest()->getQueryParam('id'));
       Yii::$app->response->format = Response::FORMAT_JSON;
     //   if ($model->load(Yii::$app->request->post()) && $model->save()) {
       //     return $this->redirect(['view', 'id' => $model->id]);
       // } else {
            return $this->renderAjax('_evaluasi', [
                'model' => $model,
               
                
            ]);
        //}
     
 }
 
  public function actionPopupKulit(){
     $model = new PemeriksaanFisik();
       
            return $this->renderAjax('_popupKulit', [
                'model' => $model,
               
                
            ]);
        
 }
 
   public function actionPopupKepala(){
     $model = new PemeriksaanFisik();
       
            return $this->renderAjax('_popupKepala', [
                'model' => $model,
               
                
            ]);
        
 }
 
    public function actionPopupMata($id){
     $model = $this->findModel($id);
       
            return $this->renderAjax('_popupMata', [
                'model' => $model,
               
                
            ]);
        
 }
 
 
 
 
     protected function findModel($id)
    {
        if (($model = PemeriksaanFisik::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}