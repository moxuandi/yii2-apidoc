### 项目描述

    1、规范API接口的注释。注释即文档，注释结构不对无法渲染出页面，更无法与对接方交流

    2、节省前后端文档定义和书写。文档按照基本格式输出，该有的元素都存在，极大的减少前后端交流成本。

    3、文档注释方便。配合phpstrom的自定制注释输出，不需要花费额外时间背文档特殊定义词汇。

安装:
------------
使用 [composer](http://getcomposer.org/download/) 下载:
```
composer require moxuandi/yii2-apidoc:"~1.0.0"

# 开发版:
composer require moxuandi/yii2-apidoc:"dev-master"
```

使用:
------------

将下方配置添加到入口文件`web/index-dev.php`或配置文件`frontend/config/main-local.php`(*** 建议不要在正式环境引入***）：

```
$config['modules']['docs'] = [
    'class' => 'moxuandi\apidoc\Module',
    'appName' => 'api',
    'name' => '接口调试系统',
    'baseUrl' => 'http://example.com/api',
    'password' => '123456',
];
```

访问:
------------

`http://example.com/docs`

### PhpStorm 注释配置:

打开phpstrom->setting->Editor->File and Code Templates->Includes, 将两个文件内容替换原本的文件内容, 点击apply即可

#### 配置文件:

##### PHP Class Doc Comment (待完善):

```
/**
#if (${NAME.indexOf('action')} != '-1' && ${NAME}!='actions')
@brief 接口名称
@param type $name this is your test name (=name:defaultValue=)
@method POST/GET
@detail 接口描述
@return array
@throws Null
#end
${PARAM_DOC}
#if (${TYPE_HINT} != "void") * @return ${TYPE_HINT}
#end
${THROWS_DOC}
*/
```

##### PHP Function Doc Comment (待完善):

```
```

在SiteController或任意controller文件上，输入`/**`后输入`enter`键，将会输出注释.
