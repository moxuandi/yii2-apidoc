<?php

namespace moxuandi\apidoc\models;

use Yii;
use yii\base\BaseObject;
use yii\web\IdentityInterface;

/**
 * Class User
 *
 * @author zhangmoxuan <1104984259@qq.com>
 * @link http://www.zhangmoxuan.com
 * @QQ 1104984259
 * @Date 2020-11-14
 */
class User extends BaseObject implements IdentityInterface
{
    public $id;
    public $username;
    public $password;
    public static $passwordSetting;
    public $authKey;
    public $accessToken;

    private static $users = [
        '100' => [
            'id' => '100',
            'username' => 'admin',
            'authKey' => 'test100key',
            'accessToken' => '100-token',
        ],
    ];

    /**
     * @param int|string $id
     * @return IdentityInterface|static|null
     */
    public static function findIdentity($id)
    {
        return isset(self::$users[$id]) ? new static(self::$users[$id]) : null;
    }

    /**
     * @param mixed $token
     * @param null $type
     * @return IdentityInterface|static|null
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        foreach (self::$users as $user) {
            if ($user['accessToken'] === $token) {
                return new static(array_merge($user, [
                    'password' => self::$passwordSetting,
                ]));
            }
        }
        return null;
    }

    /**
     * Finds user by username
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        foreach (self::$users as $user) {
            if ($user['username'] === $username) {
                return new static(array_merge($user, [
                    'password' => self::$passwordSetting,
                ]));
            }
        }
        return null;
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @param string $authKey
     * @return bool
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
    }
}
