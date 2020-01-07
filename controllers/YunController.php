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
    const REDIRECT_URI = 'http://120.55.112.76/wx/get-code';
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

    function actionImage($avatar,$openid) {

//        $content = file_get_contents($avatar);
//
//        file_put_contents('@webroot/avatar/'.$openid.'.jpg', $content);



        $ch = curl_init();

        curl_setopt($ch, CURLOPT_POST, 0);

        curl_setopt($ch,CURLOPT_URL,$avatar);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $file_content = curl_exec($ch);

        curl_close($ch);

        $downloaded_file = fopen("img/avatar/".$openid.'.jpg', 'w');

        fwrite($downloaded_file, $file_content);

        fclose($downloaded_file);



        $imgpath = Yii::getAlias('@webroot/img/avatar/'.$openid.'.jpg');

        $ext     = pathinfo($imgpath);
        $src_img = null;
        switch ($ext['extension']) {
            case 'jpg':
                $src_img = imagecreatefromjpeg($imgpath);
                break;
            case 'png':
                $src_img = imagecreatefrompng($imgpath);
                break;
        }
        $wh  = getimagesize($imgpath);
        $w   = $wh[0];
        $h   = $wh[1];
        $w   = min($w, $h);
        $h   = $w;
        $img = imagecreatetruecolor($w, $h);
        //这一句一定要有
        imagesavealpha($img, true);
        //拾取一个完全透明的颜色,最后一个参数127为全透明
        $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
        imagefill($img, 0, 0, $bg);
        $r   = $w / 2; //圆半径
        $y_x = $r; //圆心X坐标
        $y_y = $r; //圆心Y坐标
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($src_img, $x, $y);
                if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                    imagesetpixel($img, $x, $y, $rgbColor);
                }
            }
        }

        imagepng($img,"img/avatar/".$openid.'.jpg');
    }


//    public function generateCertificate(){


    public function actionIndexx($token = null,$avatar = null){

        if(!$token){
            return $this->redirect('/wx/premit-wx');

        }
        $this->layout= 'main1';
        $this->actionImage($avatar,$token);
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
        $share_title = "2020新年新运势，快来测测吧";
        $share_img = "http://thirdwx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTIYAEcqeJicLMH67P1Jibu3TKWIqyW6OGNHoicywiaciccLL9roojDVN5wFAd7QBXpntzg0YuAQ4AhoNCg/132";


        $textOpt = ['color'=>'000','size'=>'25'];
        $fontFile = Yii::getAlias('@webroot/font/GB2312.ttf');



        $img = Yii::getAlias('@webroot/img/bg.jpg');
        $img = Image::watermark($img, '@webroot/img/avatar/'.$token.'.jpg', [250,280]);

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

        $img->save(Yii::getAlias('@webroot/img/yunshi/'.$token.'.jpg'), ['quality' => 100]);
        $this->layout = 'main1';
        return $this->render('show_certificate',[

            'src' => '/img/test'.'.jpg',


            'share_title' => $share_title,
            'share_img' => $share_img,
            'token' => $token,
            'app_id' => self::APP_ID,
            'timestamp' => $timestamp,
            'nonceStr' => $noncestr,
            'signature' => $str_sha1,
            'jsapi_ticket' => $jsapi_ticket,
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
