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


class IndexController extends Controller
{
    const SCOPE = 'snsapi_userinfo';
    const REDIRECT_URI = 'http://47.99.46.80/wx/get-code';
    const APP_ID = 'wx8d771bff3c8c1eaf';
    const APP_SECRET = '0336ad17025337ad17193f079d6da8e8';

    public function actionIndex($token = null){


    }

    public function actionProject($id,$token = null){

        if(!$token){
            $this->redirect('/wx/premit-wx');
        }
        $this->layout= 'main1';
        $project = Project::findOne($id);
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
        $share_title = "和我一起关注【{$project->title}】公益计划,动动手指,让爱心传遍龙江大地";
        $share_img = "http://thirdwx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTIYAEcqeJicLMH67P1Jibu3TKWIqyW6OGNHoicywiaciccLL9roojDVN5wFAd7QBXpntzg0YuAQ4AhoNCg/132";
        return $this->render('project',[
            'now_money' => $project->now_money,
            'title' => $project->title,
            'sub_title' => $project->sub_title,
            'content' => $project->content,
            'donations' => Donation::getDonationsById($id),
            'count' => count(Donation::getDonationsByProject($id)),

            'share_title' => $share_title,
            'share_img' => $share_img,
            'token' => $token,
            'app_id' => self::APP_ID,
            'timestamp' => $timestamp,
            'nonceStr' => $noncestr,
            'signature' => $str_sha1,
            'jsapi_ticket' => $jsapi_ticket,
//            'jsApiList' => json_encode(['onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ','onMenuShareWeibo','onMenuShareQZone']),
        ]);
    }
    /*
     * 捐款
     * */
    public function donate($access_token,$money,$product_id,$comment = ''){


        try{

            //根据token获取用户
            $user = User::findIdentityByAccessToken($access_token);
            //已经确认过金额后,写入捐款表
            Donation::add($user->id,$product_id,$team,$comment,$money);
            //累加到个人表
            User::addMoney($user,$money);


        }catch (Exception $e) {

        }

    }

    public function actionQuickDonate($token = null,$project_id = null){

            $model = new Donation();
            if(\Yii::$app->request->isPost){
                $transaction = Yii::$app->db->beginTransaction();
                try{
                    if ($model->load(\Yii::$app->request->post())&&$model->save()) {
                        User::addMoney($model->user_id,$model->money);
                        Project::addMoney($project_id,$model->money);
                        $transaction->commit();
                        $this->generateCertificate($token,$model->id);
                        return $this->redirect(['donate-success','token' => $token,'donation_id' => $model->id]);

                        #如果是数据库保存失败 后期要加一个保存失败的跳转界面提示

                    }
                }catch (Exception $e) {
                    $transaction->rollBack();
                }

                return $this->redirect(Yii::$app->request->referrer);

            }

            return $this->renderAjax('quick_donate',[
                'model'=>$model,
                'project_id' => $project_id,
                'token' => $token,
                'user_id' => User::findByOpenId($token)->id,
            ]);
    }

    public function actionDonateSuccess($token,$donation_id){

        $donation = Donation::findByDonationId($donation_id);
        $user = User::findById($donation->user_id);
        $project = Project::findById($donation->product_id);
        $this->layout = 'main1';

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

        return $this->render('donate_success',[
            'tradeno' => $donation->tradeno,
            'token' => $token,
            'img_url' => $user->img_url,
            'nickname' => $user->nickname,
            'project_name' => $project->title,
            'money' => $donation->money,

            'share_title' => "【{$project->title}】感谢{$user->nickname}的捐赠,献出一份爱心,托起一份希望",
            'share_img' => $project->img_url,
            'url' =>Yii::$app->request->hostInfo."/index/show-certificate?tradeno={$donation->tradeno}",
            'token' => $token,
            'app_id' => self::APP_ID,
            'timestamp' => $timestamp,
            'nonceStr' => $noncestr,
            'signature' => $str_sha1,
            'jsapi_ticket' => $jsapi_ticket,
        ]);
    }

    public function generateCertificate($token,$donation_id){

        $donation = Donation::findByDonationId($donation_id);
        $project = Project::findById($donation->product_id);
        $user = User::findByOpenId($token);
        $text1 = '你为['.$project->title.']成功捐助了['.$donation->money.']元。';
        $count = ceil(mb_strlen($text1)/14);

        $textOpt = ['color'=>'000','size'=>'30'];
        $fontFile = Yii::getAlias('@webroot/font/GB2312.ttf');

        $qrCode = (new QrCode(Yii::$app->request->hostInfo."/index/project?id={$project->id}"))
            ->setSize(120)
            ->setMargin(5)
            ->useForegroundColor(0,0,0);

        $qrCode->writeFile(Yii::getAlias('@webroot/img/jiangzhuang/'. 'code.png')); // writer defaults to PNG when none is specified

        $img = Image::watermark(Yii::getAlias('@webroot/img/jiangzhuang.jpg'),Yii::getAlias('@webroot/img/jiangzhuang/'. 'code.png'),[20,1060]);


        $img = Image::text($img, $donation->tradeno, $fontFile, [384, 256], $textOpt);
        $img = Image::text($img, $user->nickname, $fontFile, [314, 348], $textOpt);
//        $img = Image::text($img, $text1, $fontFile, [100, 427], $textOpt);

        for($i = 1;$i < $count + 1;$i++){
            $img = Image::text($img, mb_substr($text1,($i - 1)*14,14*$i), $fontFile, [100, 427 + 60*($i -1)], $textOpt);
        }
        $img = Image::text($img, "感谢您的付出,积水成川,以小", $fontFile, [100, 627], $textOpt);
        $img = Image::text($img, "小善汇成大善帮助更多的人", $fontFile, [100, 687], $textOpt);
        $img = Image::text($img, "齐齐哈尔市慈善总会", $fontFile, [350, 837], $textOpt);


       $img = Image::text($img, date("Y年n月d日"), $fontFile, [420, 887], $textOpt)->save(Yii::getAlias('@webroot/img/jiangzhuang/'.$donation->tradeno.'.jpg'), ['quality' => 100]);
    }



    public function actionShowCertificate($token=null,$tradeno){


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
