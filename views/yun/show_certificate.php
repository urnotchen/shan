<style type="text/css">
    .wrap{
        height:100%;
        display:table;
        padding: 0;
    }
    .container{
        display: table-cell;
        vertical-align: middle;
    }
</style>

<img src=<?php echo $src;?> style='display: block; height: auto;max-width: 100%;'>

<?php
$this->registerJs(<<<JS
    history.pushState(null,null,'indexx');
    window.addEventListener("popstate", function(e) { 
    window.location.href = 'https://mp.weixin.qq.com/s/6l6BbNqeK0rosGjuo_C8Ew';
}, false);
    
    var app_id = $("#app_id").val();
    var signature = $("#signature").val();
    var timestamp = $("#timestamp").val();
    var nonceStr = $("#nonceStr").val();

    wx.config({
        debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
        appId: app_id, // 必填，公众号的唯一标识
        timestamp: timestamp, // 必填，生成签名的时间戳
        nonceStr: nonceStr, // 必填，生成签名的随机串
        signature : signature,
        jsApiList: ['updateTimelineShareData','updateAppMessageShareData','onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ','onMenuShareWeibo','onMenuShareQZone'] // 必填，需要使用的JS接口列表
        });
    wx.ready(function () {      //需在用户可能点击分享按钮前就先调用
        var share_img = $("#share_img").val();
        var share_title = $("#share_title").val();
        var url = $("#url").val();

        wx.updateTimelineShareData({
        title: share_title, // 分享标题
        link: url, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
        imgUrl: share_img, // 分享图标
        success: function () {
   
        }
        });
    
    });

$("#share").on('click',share);
function share(){

}
JS
);