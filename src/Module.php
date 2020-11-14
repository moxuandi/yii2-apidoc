<?php

namespace moxuandi\apidoc;

use moxuandi\apidoc\models\User;
use Yii;
use yii\base\Action;
use yii\web\ForbiddenHttpException;

/**
 * Class Module
 *
 * @author zhangmoxuan <1104984259@qq.com>
 * @link http://www.zhangmoxuan.com
 * @QQ 1104984259
 * @Date 2020-11-14
 */
class Module extends \yii\base\Module
{
    /**
     * @var string|null API接口注释获取的应用名称.
     * 如果是`yii2-app-basic`基础模板, 不需配置此项(使用`null`), 将直接使用`Yii::$app`;
     * 如果是`yii2-app-advanced`高级模板, eg: `api`, `backend`, `frontend`. 将加载以下配置文件:
     *   - `@common/config/main.php`
     *   - `@common/config/main-local.php`
     *   - `@{$appName}/config/main.php`
     *   - `@{$appName}/config/main-local.php`
     */
    public $appName = null;
    /**
     * @var string 调试器名称, 显示在左上角.
     */
    public $name = '接口调试系统';
    /**
     * @var string 接口请求地址. eg: `http://example.com`.
     */
    public $baseUrl;
    /**
     * @var string 接口文档的访问密码.
     */
    public $password;
    /**
     * @var array 忽略的模块名.
     */
    public $ignoreModules = ['debug', 'gii'];
    /**
     * @var array 允许访问此模块的IP列表.
     * Each array element represents a single IP filter which can be either an IP address or an address with wildcard (e.g. 192.168.0.*) to represent a network segment.
     * The default value is `['127.0.0.1', '::1']`, which means the module can only be accessed by localhost.
     */
    public $allowedIPs = ['127.0.0.1', '::1'];
    /**
     * @var array 允许访问此模块的主机列表.
     */
    public $allowedHosts = [];


    public function init()
    {
        parent::init();

        // 将当前模块添加到忽略列表
        $this->ignoreModules[] = $this->id;

        // 注册本模块专用用户组件
        User::$passwordSetting = $this->password;
        Yii::$app->setComponents([
            'user' => [
                'class' => 'yii\web\User',
                'identityClass' => 'moxuandi\apidoc\models\User',
                'loginUrl' => [$this->id . '/default/login'],
            ],
        ]);
    }

    /**
     * @param Action $action
     * @return bool
     * @throws ForbiddenHttpException
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        // 判断IP和域名是否允许访问此模块
        if (!$this->checkAccess()) {
            throw new ForbiddenHttpException('You are not allowed to access this page.');
        }

        // 校验密码与页面权限
        $route = Yii::$app->controller->id . '/' . $action->id;
        $publicPages = ['default/login', 'default/error'];
        if ($this->password !== false && Yii::$app->user->isGuest && !in_array($route, $publicPages)) {
            // 设置了密码, 当前是访客, 不在公开路径里
            Yii::$app->user->loginRequired();
        }

        return true;
    }

    /**
     * 检查是否允许当前用户访问此模块.
     * @return bool
     */
    protected function checkAccess()
    {
        $allowed = false;
        $ip = Yii::$app->request->getUserIP();
        foreach ($this->allowedIPs as $filter) {
            if ($filter === '*' || $filter === $ip || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos))) {
                $allowed = true;
                break;
            }
        }
        if ($allowed === false) {
            foreach ($this->allowedHosts as $hostname) {
                $filter = gethostbyname($hostname);
                if ($filter === $ip) {
                    $allowed = true;
                    break;
                }
            }
        }
        return $allowed;
    }
}
