<?php
/* @var $this yii\web\View */
/* @var $baseUrl string */
/* @var $name string */

/* @var $apiData array */

use moxuandi\apidoc\assets\AppAsset;
use yii\helpers\Html;
use yii\widgets\Spaceless;

AppAsset::register($this);

$this->title = $name;
$this->registerJs("var baseUrl='{$baseUrl}';var apiData={$apiData};", $this::POS_END);
$this->registerJs($this->render('index.min.js'), $this::POS_END);
?>
<?php $this->beginPage(); Spaceless::begin(); ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
<meta charset="<?= Yii::$app->charset ?>">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
<title><?= Html::encode($this->title) ?></title>
<?php $this->head() ?>
<?php $this->registerCsrfMetaTags() ?>
<style>
    .wrap-body{margin-top:4.5rem;padding-bottom:2rem}
    .wrap-body .card .card-header{padding:0}
    .wrap-body .card .list-group-item{padding:.375rem .75rem}
    .wrap-body p{font-size:.875rem;margin-bottom:.625rem}
    .wrap-body pre{padding:.5rem;font-size:.8125rem;word-break:break-all;word-wrap:break-word;background-color:#f5f5f5;border:.0625rem solid #ccc;border-radius:.25rem}
    .wrap-body .tab-content .card + .card{margin-top:1rem}
    .wrap-body .tab-content .card .card-body{padding:.9375rem}
    .wrap-body .tab-content .card .table{margin:-.0625rem;font-size:.8125rem}
    .wrap-body .tab-content .card .card-body .form-group{margin-bottom:0}
    .wrap-body .tab-content .card .card-body .form-group + .form-group{margin-top:.625rem}
    .wrap-body .tab-content .card .card-body textarea.form-control,.wrap-body .tab-content .card .card-body pre{height:100%;min-height:2rem;margin-bottom:0;font-size:.8125rem}
    .wrap-body .tab-content .card .card-body .col-6:first-child{padding-right:.46875rem}
    .wrap-body .tab-content .card .card-body .col-6:last-child{padding-left:.46875rem}
</style>
</head>

<body>
<?php $this->beginBody() ?>
<div class="container-fluid wrap" id="app">
    <nav class="navbar navbar-expand-lg navbar-light bg-success fixed-top">
        <a class="navbar-brand" href="#"><?= $name ?></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown">
                        {{ activeModule.moduleId ? (activeModule.moduleId + ' 模块') : '默认模块' }}
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="javascript:void(0)" v-for="(item, index) in apiData" @click="selectModule(index)">
                            {{ item.moduleId ? (item.moduleId + ' 模块') : '默认模块' }}
                        </a>
                    </div>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link disabled" href="javascript:void(0)">用户登录</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="row wrap-body">
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-4">
            <div class="accordion" id="methodList">
                <div class="card" v-for="(item, index) in activeModule.controllers">
                    <div class="card-header" :id="'heading' + item.controllerId">
                        <button type="button" class="btn btn-link btn-block text-left collapsed" data-toggle="collapse" :data-target="'#collapse_' + item.controllerId">
                            {{ item.controllerName }} <small>{{ item.title }}</small>
                        </button>
                    </div>
                    <div :id="'collapse_' + item.controllerId" class="list-group list-group-flush collapse" data-parent="#methodList">
                        <button type="button" v-for="(action, key) in item.actions" :class="'list-group-item list-group-item-action' + (action.active ? ' active' : '')" @click="selectAction(index, key)">
                            <code>{{ action.route }}</code>
                            <small>{{ action.title }}</small>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div :class="'col-xl-10 col-lg-9 col-md-5 col-sm-8' + (activeAction.actionId ? ' d-none' : '')">
            <h4>接口注释规范</h4>
            <p><code>@apiTitle</code>: 表示接口名称，不注释则文档不显示该接口</p>
            <p><code>@apiDesc</code>: 表示接口简介/用途等，可空</p>
            <p><code>@apiMethod</code>: 表示请求方式，不注释默认为<code>GET</code></p>
            <p><code>@apiRoute</code>: 表示请求路由地址</p>
            <p><code>@apiHeader</code>: 表示请求的头参数，可多个，JSON结构(key可不加引号)</p>
            <p><code>@apiParam</code>: 表示请求的GET参数，可多个，JSON结构(key可不加引号)</p>
            <p><code>@apiBody</code>: 表示请求的正文参数，JSON结构(key可不加引号)</p>
            <p><code>@apiResponse</code>: 返回内容结构，JSON结构(key可不加引号)</p>
            <pre>
/**
 * 更新用户信息
 * @return array
 *
 * @apiTitle    更新用户信息
 * @apiDesc  更新用户信息-描述
 * @apiMethod   PUT
 * @apiRoute    /user/update/{id}
 * @apiHeader   {name="Authorization", type="string", required=true, desc="Token"}
 * @apiParam    {name="id", type="integer", required=true, desc="用户ID"}
 * @apiBody     [{name="username", type="string", desc="手机号", required=true},
 *               {name="email", type="string", desc="邮箱", required=true},
 *               {name="nickname", type="string", desc="昵称", required=true},
 *               {name="avatar", type="string", desc="头像"},
 *               {name="gender", type="integer", desc="性别(0未知;1男;2女)", required=true},
 *               {name="qq", type="string", desc="QQ"},
 *               {name="birthday", type="string", desc="生日"}]
 * @apiResponse [{name="id", type="integer", desc="ID"},
 *               {name="username", type="string", desc="手机号"},
 *               {name="email", type="string", desc="邮箱"},
 *               {name="nickname", type="string", desc="昵称"},
 *               {name="avatar", type="string", desc="头像"},
 *               {name="gender", type="integer", desc="性别(0未知;1男;2女)"},
 *               {name="qq", type="string", desc="QQ"},
 *               {name="birthday", type="string", desc="生日"}]
 */</pre>
        </div>

        <div :class="'col-xl-10 col-lg-9 col-md-5 col-sm-8' + (activeAction.actionId ? ' ' : ' d-none')">
            <h4>{{ activeAction.title }}</h4>
            <pre>
调用地址: {{ activeAction.route }}
请求方式: {{ activeAction.method }}
功能详述: {{ activeAction.desc }}
</pre>
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" data-toggle="tab" href="#descPanel" role="tab">请求和返回</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-toggle="tab" href="#outputPanel" role="tab">在线调试</a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="descPanel" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <button type="button" class="btn btn-link btn-block text-left collapsed" data-toggle="collapse" data-target="#actionHeaders">
                                Header参数 <small>将附加到请求头上</small>
                            </button>
                        </div>
                        <div id="actionHeaders" class="collapse show">
                            <table class="table table-striped table-sm table-bordered table-hover" v-if="activeAction.headers.length">
                                <tr>
                                    <th width="12%">参数名</th>
                                    <th width="8%">类型</th>
                                    <th width="15%">默认值</th>
                                    <th width="5%" class="text-center">必填</th>
                                    <th>描述</th>
                                </tr>
                                <tr v-for="(param, index) in activeAction.headers" :title="param.required ? '必填项' : ''">
                                    <td><code>{{ param.name }}</code></td>
                                    <td><code>{{ param.type }}</code></td>
                                    <td><code>{{ param.default }}</code></td>
                                    <td class="text-center">{{ param.required ? '✔' : '' }}</td>
                                    <td>{{ param.desc }}</td>
                                </tr>
                            </table>
                            <div class="card-body" v-else>
                                无
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <button type="button" class="btn btn-link btn-block text-left collapsed" data-toggle="collapse" data-target="#actionParams">
                                GET参数 <small>将附加到请求地址上</small>
                            </button>
                        </div>
                        <div id="actionParams" class="collapse show">
                            <table class="table table-striped table-sm table-bordered table-hover" v-if="activeAction.params.length">
                                <tr>
                                    <th width="12%">参数名</th>
                                    <th width="8%">类型</th>
                                    <th width="15%">默认值</th>
                                    <th width="5%" class="text-center">必填</th>
                                    <th>描述</th>
                                </tr>
                                <tr v-for="(param, index) in activeAction.params" :title="param.required ? '必填项' : ''">
                                    <td><code>{{ param.name }}</code></td>
                                    <td><code>{{ param.type }}</code></td>
                                    <td><code>{{ param.default }}</code></td>
                                    <td class="text-center">{{ param.required ? '✔' : '' }}</td>
                                    <td>{{ param.desc }}</td>
                                </tr>
                            </table>
                            <div class="card-body" v-else>
                                无
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <button type="button" class="btn btn-link btn-block text-left collapsed" data-toggle="collapse" data-target="#actionPostData">
                                POST参数 <small>将作为请求正文发出</small>
                            </button>
                        </div>
                        <div id="actionPostData" class="collapse show">
                            <table class="table table-striped table-sm table-bordered table-hover" v-if="activeAction.reqBody.length">
                                <tr>
                                    <th width="12%">参数名</th>
                                    <th width="8%">类型</th>
                                    <th width="15%">默认值</th>
                                    <th width="5%" class="text-center">必填</th>
                                    <th>描述</th>
                                </tr>
                                <tr v-for="(param, index) in activeAction.reqBody"
                                    :title="param.required ? '必填项' : ''">
                                    <td><code>{{ param.name }}</code></td>
                                    <td><code>{{ param.type }}</code></td>
                                    <td><code>{{ param.default }}</code></td>
                                    <td class="text-center">{{ param.required ? '✔' : '' }}</td>
                                    <td>{{ param.desc }}</td>
                                </tr>
                            </table>
                            <div class="card-body" v-else>
                                无
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <button type="button" class="btn btn-link btn-block text-left collapsed" data-toggle="collapse" data-target="#actionResponse">
                                返回值说明
                            </button>
                        </div>
                        <div id="actionResponse" class="collapse show">
                            <table class="table table-striped table-sm table-bordered table-hover" v-if="activeAction.response.length">
                                <tr>
                                    <th width="12%">参数名</th>
                                    <th width="8%">类型</th>
                                    <th>描述</th>
                                </tr>
                                <tr v-for="(param, index) in activeAction.response">
                                    <td><code>{{ param.name }}</code></td>
                                    <td><code>{{ param.type }}</code></td>
                                    <td class="small">{{ param.desc }}</td>
                                </tr>
                            </table>
                            <div class="card-body" v-else>
                                无
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <button type="button" class="btn btn-link btn-block text-left collapsed" data-toggle="collapse" data-target="#actionException">
                                异常说明
                            </button>
                        </div>
                        <div id="actionException" class="collapse show">
                            <div class="card-body text-danger">
                                未填写
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="outputPanel" role="tabpanel">
                    <div :class="'card' + (activeAction.headers.length ? '' : ' d-none')">
                        <div class="card-header">
                            <button type="button" class="btn btn-link btn-block text-left collapsed" data-toggle="collapse" data-target="#reqHeaders">
                                Header参数 <small>将附加到请求头上</small>
                            </button>
                        </div>
                        <div id="reqHeaders" class="collapse show">
                            <div class="card-body">
                                <div class="form-group" v-for="(item, index) in activeAction.headers">
                                    <div class="input-group input-group-sm" :title="item.desc">
                                        <div class="input-group-prepend">
                                            <label class="input-group-text" :for="'header_' + item.name">{{ item.name }}</label>
                                        </div>
                                        <input type="text" class="form-control" :id="'header_' + item.name" :value="item.default" :placeholder="item.desc" @change="setResHeader(index, item.name, $event)" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div :class="'card' + (activeAction.params.length ? '' : ' d-none')">
                        <div class="card-header">
                            <button type="button" class="btn btn-link btn-block text-left collapsed" data-toggle="collapse" data-target="#reqparams">
                                GET参数 <small>将附加到请求地址上</small>
                            </button>
                        </div>
                        <div id="reqparams" class="collapse show">
                            <div class="card-body">
                                <div class="form-group" v-for="(item, index) in activeAction.params">
                                    <div class="input-group input-group-sm" :title="item.desc">
                                        <div class="input-group-prepend">
                                            <label class="input-group-text" :for="'param_' + item.name">{{ item.name }}</label>
                                        </div>
                                        <input type="text" class="form-control" :id="'param_' + item.name" :value="item.default" :placeholder="item.desc" @change="setResParam(index, item.name, $event)" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div :class="'card' + (activeAction.reqBody.length ? '' : ' d-none')">
                        <div class="card-header">
                            <button type="button" class="btn btn-link btn-block text-left collapsed" data-toggle="collapse" data-target="#resBody">
                                POST参数 <small>将作为请求正文发出</small>
                            </button>
                        </div>
                        <div id="resBody" class="collapse show">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <textarea class="form-control" @change="setResBody($event)">{{ requestForm.data2 }}</textarea>
                                    </div>
                                    <div class="col-6">
                                        <pre id="requestDataView"></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card d-block border-0 text-center">
                        <button type="button" class="btn btn-primary" @click="sendRequest">调用接口</button>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <button type="button" class="btn btn-link btn-block text-left collapsed" data-toggle="collapse" data-target="#responseData">
                                输出结构
                            </button>
                        </div>
                        <div id="responseData" class="collapse show">
                            <div class="card-body">
                                <pre id="responseBodyView" :class="responseBody ? '' : 'd-none'"></pre>
                                <div :class="'text-center' + (responseBody ? ' d-none' : '')">
                                    <div>未调用或调用失败</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php Spaceless::end(); $this->endPage(); ?>
