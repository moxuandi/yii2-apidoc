<?php

namespace moxuandi\apidoc\controllers;

use moxuandi\apidoc\models\LoginForm;
use phpDocumentor\Reflection\DocBlock;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\web\Application;
use yii\web\Controller;

/**
 * Class DefaultController
 *
 * @author zhangmoxuan <1104984259@qq.com>
 * @link http://www.zhangmoxuan.com
 * @QQ 1104984259
 * @Date 2020-11-14
 */
class DefaultController extends Controller
{
    /* @var \moxuandi\apidoc\Module */
    public $module;

    /**
     * 登录
     * @return string
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        $model->password = '123456';
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            $this->redirect(['index']);
        }
        return $this->renderPartial('login', [
            'name' => $this->module->name,
        ]);
    }

    /**
     * 主页
     * @return string
     * @throws InvalidConfigException
     * @throws ReflectionException
     */
    public function actionIndex()
    {
        $apiData = $this->getModuleList($this->module->appName);
        //return $this->asJson(['apiData' => $apiData]);
        return $this->renderPartial('index', [
            'baseUrl' => $this->module->baseUrl,
            'name' => $this->module->name,
            'apiData' => Json::encode($apiData),
        ]);
    }

    /**
     * 获取模块`module`列表.
     * @param string|null $appName
     * @return mixed
     * @throws InvalidConfigException
     * @throws ReflectionException
     */
    public function getModuleList(?string $appName)
    {
        $app = $this->initApp($appName);
        $modules = $app->getModules();
        $result[] = [
            'moduleId' => '',
            'controllers' => $this->getControllerList($app)
        ];
        foreach ($modules as $moduleName => $moduleConfig) {
            // 过滤调忽略的模块
            if (in_array($moduleName, $this->module->ignoreModules)) {
                continue;
            }
            $module = $app->getModule($moduleName);
            $result[] = [
                'moduleId' => $moduleName,
                'controllers' => $this->getControllerList($module)
            ];
        }
        return $result;
    }

    /**
     * 获取控制器`controller`列表.
     * @param Module $app
     * @return array
     * @throws ReflectionException
     */
    public function getControllerList(Module $app)
    {
        $controllerPaths = FileHelper::findFiles($app->controllerPath);
        $controllers = [];
        foreach ($controllerPaths as $filePath) {
            // 判断文件名是否包含`Controller`
            if (strpos($filePath, 'Controller.php') === false) {  // 不包含`Controller`的应该不是控制器类, 直接跳过
                continue;
            }

            // 控制器类的基本信息
            $fileName = str_replace($app->controllerPath . '\\', '', $filePath);  // eg: SiteController.php or shop\GoodsController.php
            $controllerName = str_replace(strrchr($fileName, '.'), '', $fileName);  // eg: SiteController or shop\GoodsController
            $controllerId = $this->getControllerId($controllerName);  // eg: site or shop/goods
            $controllerNamespace = $app->controllerNamespace . '\\' . $controllerName;

            $rc = new ReflectionClass($controllerNamespace);

            // 如果不是控制器基类的子类, 直接跳过, 过滤调文件命名是控制器格式, 但实际上并不是控制器子类
            if (!$rc->isSubclassOf('yii\web\Controller')) {
                continue;
            }

            $docBlock = new DocBlock($rc->getDocComment());

            // 跳过标记忽略的控制器
            if ($docBlock->getTagsByName('apiIgnore')) {
                continue;
            }

            $controllers[] = [
                'controllerId' => $controllerId,
                'controllerName' => $controllerName,
                'title' => $this->getTagByName($docBlock, 'apiTitle'),
                'actions' => $this->getActionList($rc),
            ];
        }
        return $controllers;
    }

    /**
     * 获取控制器的所有方法`action`列表.
     * @param ReflectionClass $rc
     * @return array
     * @throws ReflectionException
     */
    public function getActionList(ReflectionClass $rc)
    {
        // 所有定义为`public`的方法列表
        $methods = $rc->getMethods(ReflectionMethod::IS_PUBLIC);
        $actions = [];
        foreach ($methods as $method) {
            $actionName = $method->getName();  // 方法名, eg: `actionIndex`, `behaviors`, `actions`
            // 过滤掉`action`, `actions`, 前6个字符不是`action`的
            if (in_array($actionName, ['action', 'actions']) || strncasecmp($actionName, 'action', 6) !== 0) {
                continue;
            }

            $rm = new ReflectionMethod($rc->name, $actionName);
            $docBlock = new DocBlock($rm->getDocComment());

            // 跳过标记忽略的控制器
            if ($docBlock->getTagsByName('apiIgnore')) {
                continue;
            }

            $actions[] = $this->getActionInfo($docBlock, $actionName);
        }
        return $actions;
    }

    /**
     * 获取方法`action`的详细信息.
     * @param DocBlock $docBlock
     * @param string $actionName
     * @return array
     */
    public function getActionInfo(DocBlock $docBlock, string $actionName)
    {
        $headers = $this->getTagByName($docBlock, 'apiHeader', 'json') ?? [];
        $params = $this->getTagByName($docBlock, 'apiParam', 'json') ?? [];
        $reqBody = $this->getTagByName($docBlock, 'apiBody', 'list') ?? [];
        $response = $this->getTagByName($docBlock, 'apiResponse', 'list') ?? [];
        return [
            'actionId' => $this->getActionId($actionName),
            'actionName' => $actionName,
            'title' => $this->getTagByName($docBlock, 'apiTitle') ?? '',
            'desc' => $this->getTagByName($docBlock, 'apiDesc') ?? '',
            'route' => $this->getTagByName($docBlock, 'apiRoute') ?? '',
            'method' => $this->getTagByName($docBlock, 'apiMethod') ?? 'GET',
            'headers' => $this->formatParams($headers),
            'params' => $this->formatParams($params),
            'reqBody' => $this->formatParams($reqBody),
            'response' => $this->formatParams($response),
        ];
    }

    /**
     * 格式化参数列表.
     * @param array $inArr
     * @return array
     */
    public function formatParams(array $inArr = [])
    {
        // 参数的基础结构
        $baseFormat = [
            'name' => '',  // 参数名
            'type' => 'string',  // 参数类型: string, integer, float, boolean, array, list
            'desc' => '',  // 参数描述
            'default' => null,  // 默认值
            'required' => false,  // 是否必填
            //'each' => 'string',  // 数组中的参数类型, array必填
            //'children' => [],  // 列表中的参数, list必填
        ];
        $result = [];
        foreach ($inArr as $item) {
            // 如果参数名为空, 忽略
            if (!isset($item['name']) || empty($item['name'])) {
                continue;
            }
            $param = array_merge($baseFormat, $item);
            switch ($param['type']) {
                case 'array':  // 数组
                    if (!isset($param['each']) || empty($param['each'])) {
                        $param['each'] = 'string';
                    }
                    break;
                case 'object':  // 对象
                case 'list':  // 列表/对象数组
                    if (!isset($param['children']) || empty($param['children'])) {
                        $param['children'] = [];
                    }
                    $param['children'] = $this->formatParams($param['children']);
                    break;
                default:
                    break;
            }
            $result[] = $param;
        }
        return $result;
    }

    /**
     * 从注释中读取特定标签的信息.
     * @param DocBlock $docBlock
     * @param string $name
     * @param string $returnType 返回数据的类型
     * @return mixed
     */
    public function getTagByName(DocBlock $docBlock, string $name, string $returnType = 'string')
    {
        $tags = $docBlock->getTagsByName($name);
        if (empty($tags)) {
            return null;
        }
        switch ($returnType) {
            case 'json':
                $content = [];
                foreach ($tags as $tag) {
                    $content[] = $this->extJsonDecode($tag->getContent(), true);
                }
                break;
            case 'list':
                $content = [];
                foreach ($tags as $tag) {
                    $content = ArrayHelper::merge($content, $this->extJsonDecode($tag->getContent(), true));
                }
                break;
            case 'string':
            default:
                $content = $tags[0]->getContent();
                break;
        }
        return $content;
    }

    /**
     * 解析key没有双引号的JSON字符串.
     * eg: {name="recordId", type="integer", desc="记录ID", required=true}
     * eg: {name:"recordId", type:"integer", desc:"记录ID", required:true}
     * @param string $str key没有双引号的JSON字符串
     * @param false $mode true:Array, false:Object
     * @return array|object
     */
    public function extJsonDecode(string $str, bool $mode = false)
    {
        if (preg_match('/\w[=:]/', $str)) {
            $str = preg_replace('/(\w+)[=:]/is', '"$1":', $str);
        }
        return json_decode($str, $mode);
    }

    /**
     * 格式化控制器ID.
     * @param string $controllerName
     * @return string
     */
    public function getControllerId(string $controllerName)
    {
        $names = explode('\\', $controllerName);
        $result = [];
        foreach ($names as $name) {
            $result[] = Inflector::camel2id(str_replace('Controller', '', $name));
        }
        return implode('/', $result);
    }

    /**
     * 格式化动作ID.
     * @param string $actionName
     * @return string
     */
    public function getActionId(string $actionName)
    {
        return Inflector::camel2id(str_replace('action', '', $actionName));
    }

    /**
     * 初始化应用.
     * @param string|null $appName 应用名称
     * @return Application
     * @throws InvalidConfigException
     */
    public function initApp(?string $appName)
    {
        if ($appName) {
            $config = ArrayHelper::merge(
                require Yii::getAlias("@common/config/main.php"),
                require Yii::getAlias("@common/config/main-local.php"),
                require Yii::getAlias("@{$appName}/config/main.php"),
                require Yii::getAlias("@{$appName}/config/main-local.php")
            );
            return new Application($config);
        } else {
            return Yii::$app;
        }
    }
}
