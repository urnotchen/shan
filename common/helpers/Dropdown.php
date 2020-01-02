<?php

namespace app\common\helpers;

use yii\helpers\Html;

class Dropdown extends \yii\bootstrap\Dropdown
{
    public function init()
    {/*{{{*/
        parent::init();
        Html::removeCssClass($this->options, 'dropdown-menu');
        Html::addCssClass($this->options, 'treeview-menu');
    }/*}}}*/

}