<?php

namespace moxuandi\apidoc\assets;

use yii\web\AssetBundle;

/**
 * Class LoginAsset
 *
 * @author zhangmoxuan <1104984259@qq.com>
 * @link http://www.zhangmoxuan.com
 * @QQ 1104984259
 * @Date 2020-11-14
 */
class LoginAsset extends AssetBundle
{
    public $css = [
        'https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/css/bootstrap.min.css',
    ];
    public $js = [
        'https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js',
        'https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js',
        'https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.min.js',
    ];
    public $depends = [];
}
