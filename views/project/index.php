<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\tProjectSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '公益项目列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-index">


    <p>
        <?= Html::a('添加项目', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?php \app\common\widgets\BoxWidget::begin();?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [


            'id',
            'title',
            'sub_title',
            'receiver',
            'expect_money',
            'now_money',
            //'count',
            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    <?php \app\common\widgets\BoxWidget::end();?>
</div>
