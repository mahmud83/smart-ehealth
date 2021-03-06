<?php

namespace backend\controllers;

use Yii;
use yii\filters\AccessControl;
use backend\models\Registrasi;
use backend\models\Icdx;
use backend\models\RegistrasiSearch;
use backend\models\Anamnesa;
use backend\models\AnamnesaSearch;
use backend\models\Diagnosa;
use backend\models\PemeriksaanFisik;
use backend\models\UserHasFaskes;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\models\Pasien;
use yii\web\Response;
use kartik\widgets\ActiveForm;
use yii\helpers\Json;
use yii\db\Query;

/**
 * RegistrasiController implements the CRUD actions for Registrasi model.
 */
class RegistrasiController extends Controller {

    public function behaviors() {
        return [
//            'access' => [
//                'class' => AccessControl::className(),
//                'rules' => [
//                    [
//                        'actions' => ['pasien-list', 'error'],
//                        'allow' => true,
//                    ],
//                    [
//                        'actions' => ['index', 'create', 'update', 'delete', 'mdaddpasien'],
//                        'allow' => true,
//                        'roles' => ['@'],
//                    ],
//                ],
//            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Registrasi models.
     * @return mixed
     */
    public function actionIndex($pId = null) {
        $faskes = UserHasFaskes::findOne(Yii::$app->user->getId())->faskes_id;
        if(Yii::$app->user->can('Perawat')){
            $searchModel = new RegistrasiSearch();
            $searchModel->status_registrasi = 'Antrian';
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        }
        elseif(Yii::$app->user->can('Apoteker')){
            $searchModel = new RegistrasiSearch();
            $searchModel->status_registrasi = 'Selesai';
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        }
        else {
            $searchModel = new RegistrasiSearch();
    //        $searchModel->faskes_id = $faskes;
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        }
        $model = new Registrasi();
        if (Yii::$app->request->post()) {
            $model->load(Yii::$app->request->post());
            $model->tanggal_registrasi = date('Y-m-d H:i:s');
            $model->tanggal_kunjungan = date('Y-m-d');
//            $faskes = UserHasFaskes::findOne(Yii::$app->user->getId())->faskes_id;
            if($faskes){$model->asal_registrasi = 'Faskes';}
            if ($model->asuransi_tgl_lahir){
            $model->asuransi_tgl_lahir = Yii::$app->get('helper')->dateFormatingStrip($model->asuransi_tgl_lahir);}
            if($faskes){
                $model->faskes_id = $faskes;
                $model->no_antrian = $model->getNoAntrian(date('Y-m-d'), $faskes);
            }
            $model->status_registrasi = 'Antrian';
            $model->save();
            $noregis = (string) sprintf('%08d',$model->id);
            $model->no_reg= $noregis;
            $model->save();
            if($model->save()){
                $modelResume = new Anamnesa;
                $modelResume->registrasi_id = $model->id;
                $modelResume->save();

                //insert diagnosa
                $modelDiagnosa = new Diagnosa();
                $modelDiagnosa->registrasi_id = $model->id;
                $modelDiagnosa->save();

                //insert pemeriksaan fisik
                $modelPemeriksaanFisik = new PemeriksaanFisik();
                $modelPemeriksaanFisik->registrasi_id = $model->id;
                $modelPemeriksaanFisik->save();
            }
        }


        $queryParams = Yii::$app->request->queryParams;
        $input = isset($queryParams['RegistrasiSearch']) && isset($queryParams['RegistrasiSearch']['tanggal_kunjungan']) ? $queryParams['RegistrasiSearch']['tanggal_kunjungan'] : null; 
        
        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'pId' => $pId,
                    'model' => $model,
                    'input' => $input
        ]);
    }

    /**
     * Displays a single Registrasi model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {

        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Registrasi model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $model = new Registrasi();

        if (Yii::$app->request->post()) {
            $model->load(Yii::$app->request->post());
            $model->tanggal_registrasi = date('Y-m-d H:i:s');
            if ($model->asuransi_tgl_lahir)
                $model->asuransi_tgl_lahir = Yii::$app->get('helper')->dateFormatingStrip($model->asuransi_tgl_lahir);

            if ($model->save()) {
                return $this->render('create', [
                            'model' => $model,
                ]);
            }
        } else {
            return $this->render('create', [
                        'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Registrasi model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id) {
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
     * Deletes an existing Registrasi model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id) {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionMdaddpasien() {
        $model = new Pasien;
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($_POST) {
            $model->load(Yii::$app->request->post());
            if ($model->save()) {
                return $this->redirect(['index', 'pasien_id' => $model->id]);
            }
            //return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->renderAjax('popup/_addPasien', [
            'model' => $model,
        ]);
    }
    
    public function actionMdeditreg($id) {
        $model = $this->findModel($id);
        $model->status_rawat_reged=$model->status_rawat;
        $model->dr_penanggung_jawab_reged=$model->dr_penanggung_jawab;
        $model->icdx_id_reged=$model->icdx_id;
        $model->catatan_reged=$model->catatan;
        $model->status_asuransi_reged=$model->status_asuransi;
        $model->asuransi_provider_id_reged=$model->asuransi_provider_id;
        $model->asuransi_penanggung_jawab_reged=$model->asuransi_penanggung_jawab;
        $model->asuransi_alamat_reged=$model->asuransi_alamat;
        $model->asuransi_notelp_reged=$model->asuransi_notelp;
        $model->asuransi_noreg_reged=$model->asuransi_noreg;
        $model->asuransi_nama_reged=$model->asuransi_nama;
        $model->asuransi_tgl_lahir_reged=$model->asuransi_tgl_lahir;
        $model->asuransi_status_jaminan_reged=$model->asuransi_status_jaminan;
        if ($_POST) {
            $model->status_rawat=$_POST['Registrasi']['status_rawat_reged'];
            $model->dr_penanggung_jawab=$_POST['Registrasi']['dr_penanggung_jawab_reged'];
            $model->icdx_id=$_POST['Registrasi']['icdx_id_reged'];
            $model->catatan=$_POST['Registrasi']['catatan_reged'];
            $model->status_asuransi=$_POST['Registrasi']['status_asuransi_reged'];
            $model->asuransi_provider_id=$_POST['Registrasi']['asuransi_provider_id_reged'];
            $model->asuransi_penanggung_jawab=$_POST['Registrasi']['asuransi_penanggung_jawab_reged'];
            $model->asuransi_alamat=$_POST['Registrasi']['asuransi_alamat_reged'];
            $model->asuransi_notelp=$_POST['Registrasi']['asuransi_notelp_reged'];
            $model->asuransi_noreg=$_POST['Registrasi']['asuransi_noreg_reged'];
            $model->asuransi_nama=$_POST['Registrasi']['asuransi_nama_reged'];
            $model->asuransi_tgl_lahir=$_POST['Registrasi']['asuransi_tgl_lahir_reged'];
            if ($model->asuransi_tgl_lahir)
                $model->asuransi_tgl_lahir = Yii::$app->get('helper')->dateFormatingStrip($model->asuransi_tgl_lahir);
            $model->asuransi_status_jaminan=$_POST['Registrasi']['asuransi_status_jaminan_reged'];
            
            if ($model->save()) {
                //return $this->redirect(['index', 'pasien_id' => $model->id]);
                return $this->redirect(['index']);
            }
        }
        return $this->renderAjax('popup/_editRegistrasi', [
            'model' => $model,
        ]);
    }
    
    public function actionResume($id)
    {

        $model = $this->findModel($id);

//        if($model->status_registrasi != 'Pemeriksaan') {
//            
//            //insert anamnesa
//            $modelResume = new Anamnesa;
//            $modelResume->registrasi_id = $id;
//            $modelResume->save();
//
//            //insert diagnosa
//            $modelDiagnosa = new Diagnosa();
//            $modelDiagnosa->registrasi_id = $id;
//            $modelDiagnosa->save();
//
//            //insert pemeriksaan fisik
//            $modelPemeriksaanFisik = new PemeriksaanFisik();
//            $modelPemeriksaanFisik->registrasi_id = $id;
//            $modelPemeriksaanFisik->save();
//
//            $model->status_registrasi = 'Pemeriksaan';
//            $model->save();
//        }

        if(Yii::$app->user->can('Perawat')){
            return $this->redirect(['Anamnesa/pemeriksaan-fisik/update', 'id' => $model->id]);
        }
        elseif (Yii::$app->user->can('Apoteker')) {
            //$modelAnamnesa = Anamnesa::findOne(['registrasi_id'=>$id]);
            //return $this->redirect(['diagnosa/show-resep-obat-form', 'id' => $modelAnamnesa->id]);
            return $this->redirect(['diagnosa/show-resep-obat-form', 'id' => $model->id]);
        }
        else {
            return $this->redirect(['Anamnesa/anamnesa/main', 'id' => $model->id]);
        }
            
    }

    /**
     * Finds the Registrasi model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Registrasi the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Registrasi::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionPasienList($search = null, $id = null) {
        $out = ['more' => false];
        if (!is_null($search)) {
            $query = new Query;
            $query->select(['id', 'nama as text'])
                    ->from('pasien')
                    ->where('nama LIKE "%' . $search . '%"')
                    ->limit(20);
            $command = $query->createCommand();
            $data = $command->queryAll();
            $out['results'] = array_values($data);
        } elseif ($id > 0) {
            $out['results'] = ['id' => $id, 'text' => Pasien::find($id)->nama];
        } else {
            $out['results'] = ['id' => 0, 'text' => 'Pasien tidak ditemukan'];
        }
        echo Json::encode($out);
    }

    public function actionIdList($search = null, $id = null) {
        $out = ['more' => false];
        if (!is_null($search)) {
            $query = new Query;
            $query->select(['id', 'id as text'])
                    ->from('pasien')
                    ->where('id LIKE "%' . $search . '%"')
                    ->limit(20);
            $command = $query->createCommand();
            $data = $command->queryAll();
            $out['results'] = array_values($data);
        } elseif ($id > 0) {
            $out['results'] = ['id' => $id, 'text' => Pasien::find($id)->nama];
        } else {
            $out['results'] = ['id' => 0, 'text' => 'Pasien tidak ditemukan'];
        }

        echo Json::encode($out);
    }
    
    public function actionIcdxList($search = null, $id = null) {
        $out = ['more' => false];
        if (!is_null($search)) {
            $query = new Query;
            $query->select(['id', 'concat(kode,"||",inggris) as text'])
                    ->from('icdx')
                    ->where('concat(kode,"||",inggris) LIKE "%' . $search . '%"')
                    ->limit(20);
            $command = $query->createCommand();
            $data = $command->queryAll();
            $out['results'] = array_values($data);
        } elseif ($id > 0) {
            $out['results'] = ['id' => $id, 'text' => Icdx::findOne($id)->inggris];
        } else {
            $out['results'] = ['id' => 0, 'text' => 'Icdx tidak ditemukan'];
        }
        echo Json::encode($out);
    }

    private function showInfo($pasien) {

        $tempat = is_null($pasien->tempat_lahir) ? '-' : $pasien->tempat_lahir;
        $tanggal = is_null($pasien->tgl_lahir) ? '-' : $pasien->tgl_lahir;

        return '<div class="form-group no-margin-botom">
                    <label class="col-sm-3 col-md-offset-1 control-label">No RM</label>
                    <div class="col-sm-7">
                        <p class="form-control-static">'.str_pad($pasien->id, 6, '0', STR_PAD_LEFT).'</p>
                    </div>
                </div>
                <div class="form-group no-margin-botom">
                    <label class="col-sm-3 col-md-offset-1 control-label">Nama</label>
                    <div class="col-sm-7">
                        <p id="patienName" class="form-control-static">'.$pasien->nama.'</p>
                    </div>
                </div>
                <div class="form-group no-margin-botom">
                    <label class="col-sm-3 col-md-offset-1 control-label">Gender</label>
                    <div class="col-sm-7">
                        <p class="form-control-static">'.$pasien->jenkel.'</p>
                    </div>
                </div>
                <div class="form-group no-margin-botom">
                    <label class="col-sm-3 col-md-offset-1 control-label">TTL</label>
                    <div class="col-sm-7">
                        <p class="form-control-static">'.$tempat.', '.$tanggal.'</p>
                    </div>
                </div>
                <div class="form-group no-margin-botom">
                    <label class="col-sm-3 col-md-offset-1 control-label">Usia</label>
                    <div class="col-sm-7">
                        <p class="form-control-static">'.$pasien->getUsia().' Tahun</p>
                    </div>
                </div>
                <div class="form-group no-margin-botom">
                    <label class="col-sm-3 col-md-offset-1 control-label">Alamat</label>
                    <div class="col-sm-7">
                        <p class="form-control-static">'.$pasien->alamat.'</p>
                    </div>
                </div>
                <div class="form-group no-margin-botom">
                    <div class="col-sm-offset-4 col-sm-4">
                        <button type="button" id="btnUpdate" data-pasien="'.$pasien->id.'" class="btn btn-primary"><span class="glyphicon glyphicon-pencil"></span> Update</button>                        </div>
                    </div>
                </div>';
    }

    public function actionRegistrasi($id){
        $registrasi = Registrasi::findOne($id);
        $pasien = $registrasi->pasien;
        
        echo $this->showInfo($pasien);
    }

    public function actionPasien($id) {
        $pasien = Pasien::findOne($id);

        echo $this->showInfo($pasien);
    }
}
