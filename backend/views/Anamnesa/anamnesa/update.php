<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Anamnesa */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Anamnesa',
]) . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Anamnesas'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="anamnesa-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'faktor_resiko_riwayat' => $faktor_resiko_riwayat,
        'faktor_resiko_kebiasaan' => $faktor_resiko_kebiasaan,
        'psikososial_tingber' => $psikososial_tingber
    ]) ?>

</div>
