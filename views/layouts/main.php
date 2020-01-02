<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;


    if (in_array(Yii::$app->controller->action->id, ['login', 'error']) && Yii::$app->getUser()->isGuest) {

        echo $this->render('main-login', [
            'content' => $content
        ]);exit();
    }else{
        if ( Yii::$app->getUser()->isGuest) {

            return Yii::$app->controller->redirect("location:".Yii::$app->user->loginUrl);

        }
        \app\assets\AppAsset::register($this);

        \dmstr\web\AdminLteAsset::register($this);

        $directoryAsset = Yii::$app->assetManager->getPublishedUrl('@bower/admin-lte/dist');

        $userIdentity = Yii::$app->getUser()->identity;


    }



        ?>

    <?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>

    <?php
    $bodyClass = '';
    if (isset(Yii::$app->params['adminlteSkin'])) {
        $bodyClass .= ' ' . Yii::$app->params['adminlteSkin'];
    }
    ?>

    <body class="<?= $bodyClass; ?>">
    <?php $this->beginBody() ?>

    <div class="wrapper" style="background-color: #ECF0F5">
        <?= $this->render('header', [
                'directoryAsset' => $directoryAsset,
                'userIdentity'   => $userIdentity,
            ]
        ) ?>

        <div class="row-offcanvas row-offcanvas-left">
            <?= $this->render('left', [
                    'directoryAsset' => $directoryAsset,
                    'userIdentity'   => $userIdentity,
                ]
            )
            ?>

            <?= $this->render('content', [
                    'content'        => $content,
                    'directoryAsset' => $directoryAsset,
                    'userIdentity'   => $userIdentity,
                ]
            ) ?>
        </div>
    </div>

    <?php $this->endBody() ?>
    </body>
    </html>
    <?php $this->endPage() ?>

<?php //endif; ?>
