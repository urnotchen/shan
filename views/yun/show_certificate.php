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
    
JS
);