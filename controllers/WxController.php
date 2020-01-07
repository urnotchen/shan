<?php

namespace app\controllers;

use app\components\QueryParamAuth;
use app\models\User;
use app\common\helpers\Curl;

use yii\web\Controller;
use Yii;

class WxController extends Controller
{

    const SCOPE = 'snsapi_userinfo';
    const REDIRECT_URI = 'http://120.55.112.76/wx/get-code';
    const APP_ID = 'wx8d771bff3c8c1eaf';
    const APP_SECRET = '0336ad17025337ad17193f079d6da8e8';




    /*
     * 微信请求授权
     * */
    public function actionPremitWx($redirect_uri = self::REDIRECT_URI)
    {
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::APP_ID . '&redirect_uri=' . urlencode($redirect_uri) . '&response_type=code&scope=' . self::SCOPE . '&state=STATE#wechat_redirect';
        return $this->redirect($url);
    }

    /*
     * 获取微信code
     *  http://39.108.230.44/index.php?code=021iwtz60a4S5E1xGRx60aS9z60iwtzl&state=123
     * */
    public function actionGetCode($code)
    {

        $output = Curl::httpGet('https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . self::APP_ID . '&secret=' . self::APP_SECRET . '&code=' . $code . '&grant_type=authorization_code', true);
        $json = json_decode($output, true);
        //       var_dump($output);die;
        //拉取下用户信息
        $userinfo = Curl::httpGet("https://api.weixin.qq.com/sns/userinfo?access_token={$json['access_token']}&openid={$json['openid']}&lang=zh_CN", true);
        //保存token
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($userinfo) {
                $userinfo = json_decode($userinfo, true);
                $user = User::updateUser($json['openid'], $json['access_token'], $json['expires_in'], $json['refresh_token'],$userinfo);
            } else {
                $user = User::updateUser($json['openid'], $json['access_token'], $json['expires_in'], $json['refresh_token']);
            }
//            var_dump($user);
//            die;

            $transaction->commit();
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            $transaction->rollback();
//            throw new HttpException(403,'数据库错误',ResponseCode::DATABASE_SAVE_FAILED);
        }
        //返回open_id,直接跳转到首页
        $this->redirect(['yun/indexx?token='.$json['openid']]);
//        $this->redirect(['index/project?id=3&token='.$json['openid']]);
    }

    public function actionIndex()
    {
        //获取基本access_token签名
        $access_token = Curl::httpGet("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . self::APP_ID . "&secret=" . self::APP_SECRET, true);
        $access_token = json_decode($access_token, true);
        $res = Curl::httpGet("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$access_token['access_token']}&type=jsapi", true);
        $ticket = json_decode($res, true);
        $noncestr = 'Wm3WZYTPz0wzccnW';
        $jsapi_ticket = $ticket['ticket'];
        $timestamp = time();
        $url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
        $str = "jsapi_ticket={$jsapi_ticket}&noncestr={$noncestr}&timestamp={$timestamp}&url={$url}";
        $str_sha1 = sha1($str);
        return $this->render('index', [
            'app_id' => self::APP_ID,
            'timestamp' => $timestamp,
            'nonceStr' => $noncestr,
            'signature' => $str_sha1,
            'jsapi_ticket' => $jsapi_ticket,
//            'jsApiList' => json_encode(['onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ','onMenuShareWeibo','onMenuShareQZone']),
        ]);
    }

    public function actionTest()
    {
        var_dump(\Yii::$app->getUser());
    }
}
