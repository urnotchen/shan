<?php

namespace app\controllers;


use app\models\Donation;
use app\models\Project;
use app\models\Team;
use app\models\User;
use app\common\helpers\Curl;
use Yii;
use yii\db\Exception;
use yii\imagine\Image;
use yii\web\Controller;
use yii\db\Connection;
use Da\QrCode\QrCode;


class YunController extends Controller
{
    const SCOPE = 'snsapi_userinfo';
    const REDIRECT_URI = 'http://47.99.46.80/wx/get-code';
    const APP_ID = 'wx8d771bff3c8c1eaf';
    const APP_SECRET = '0336ad17025337ad17193f079d6da8e8';


    public function actionTest(){
    if(array_key_exists("HTTP_REFERER",$_SERVER)){
        if(strpos($_SERVER["HTTP_REFERER"],'indexx')||(strpos($_SERVER["HTTP_REFERER"],'noval'))){
            header("Location:https://mp.weixin.qq.com/s/6l6BbNqeK0rosGjuo_C8Ew");
        }
    }
    echo 1;
    return $this->redirect('indexx');

}

    public function actionIndex(){
        header("Location:https://mp.weixin.qq.com/s/6l6BbNqeK0rosGjuo_C8Ew");

    }
    public function actionNovel(){
        header("Location:https://mp.weixin.qq.com/s/6l6BbNqeK0rosGjuo_C8Ew");
        exit();
    }




//    public function generateCertificate(){
    public function actionIndexx(){



        $textOpt = ['color'=>'000','size'=>'25'];
        $fontFile = Yii::getAlias('@webroot/font/GB2312.ttf');



        $img = Yii::getAlias('@webroot/img/bg.jpg');

        $goods = ['鼓鼓的钱包','美满的爱情','健康的身体','有趣的生活','洒脱人生','事业高升','家人团聚','完美身材','智商upup'];
        $bads =  ['夜宵','脂肪','脱发的烦恼','不开心的事','多愁善感','不值得的人','渣男渣女','勾心斗角','生病'];
        for($i = 0;$i < 6;$i++){
            $j = rand(0,8);
            $img = Image::text($img, $goods[$j], $fontFile, [100, 540 + $i * 50], $textOpt);
        }

        for($i = 0;$i < 6;$i++){
            $j = rand(0,8);
            $img = Image::text($img, $bads[$j], $fontFile, [350, 540 + $i * 50], $textOpt);
        }
        $img->save(Yii::getAlias('@webroot/img/test'.'.jpg'), ['quality' => 100]);
        $this->layout = 'main1';
        return $this->render('show_certificate',[
//            'share_title' => "2020新年新运势，快来测测吧！！",
////            'share_img' => ,
            'src' => '/img/test'.'.jpg',
//            'url' =>Yii::$app->request->hostInfo."/index/show-certificate?tradeno={$tradeno}",
//
//
//            'token' => $token,
//            'app_id' => self::APP_ID,
//            'timestamp' => $timestamp,
//            'nonceStr' => $noncestr,
//            'signature' => $str_sha1,
//            'jsapi_ticket' => $jsapi_ticket,
        ]);
    }



    public function actionShowCertificate(){


//        $user = User::findByOpenId($token);
        $donation = Donation::findByTradeno($tradeno);
        $project = Project::findById($donation->product_id);
        $user = User::findById($donation->user_id);

        if(!file_exists('@webroot/img/jiangzhuang/'.$tradeno)){
            $this->generateCertificate($user->open_id,$donation->id);
        }

//        if(!$token){
//            $this->redirect('/wx/premit-wx');
//        }
        //获取基本access_token签名
        $access_token = Curl::httpGet("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".self::APP_ID."&secret=".self::APP_SECRET,true);
        $access_token = json_decode($access_token,true);
        $res = Curl::httpGet("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$access_token['access_token']}&type=jsapi",true);
        $ticket = json_decode($res,true);
        $noncestr = 'Wm3WZYTPz0wzccnW';
        $jsapi_ticket = $ticket['ticket'];
        $timestamp = time();
        $url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
        $str = "jsapi_ticket={$jsapi_ticket}&noncestr={$noncestr}&timestamp={$timestamp}&url={$url}";
        $str_sha1 = sha1($str);

        $this->layout = 'main1';
        return $this->render('show_certificate',[
            'share_title' => "【{$project->title}】感谢{$user->nickname}的捐赠,献出一份爱心,托起一份希望",
            'share_img' => $project->img_url,
            'src' => '/img/jiangzhuang/'.$tradeno.'.jpg',
            'url' =>Yii::$app->request->hostInfo."/index/show-certificate?tradeno={$tradeno}",


            'token' => $token,
            'app_id' => self::APP_ID,
            'timestamp' => $timestamp,
            'nonceStr' => $noncestr,
            'signature' => $str_sha1,
            'jsapi_ticket' => $jsapi_ticket,
        ]);
    }

    public function actionGetDonations($donation_id,$project_id){
        Yii::$app->response->format = 'json';
        $donations =  Donation::getDonationsById($project_id,$donation_id);
        $res = [];
        foreach ($donations as $donation){
            $temp['id'] = $donation['id'];
            $temp['img_url'] = $donation->user->img_url;
            $temp['nickname'] = $donation->user->nickname;
            $temp['money'] = $donation['money'];
            $temp['created_at'] = \Yii::$app->timeFormatter->humanReadable3($donation->created_at);
            $res[] = $temp;
       }
        return $res;
    }


    public function actionDetails($token,$project_id){

$this->layout = 'main1';
$project = Project::findById($project_id);
return $this->render('details',[
'content' => $project->content
]);
}

}
