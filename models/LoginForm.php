<?php

namespace zpearl\users\models;

use zpearl\users\traits\ModuleTrait;
use yii\base\Model;
use Yii;

/**
 * Class LoginForm
 * @package zpearl\users\models
 * LoginForm is the model behind the login form.
 *
 * @property string $username Username
 * @property string $password Password
 * @property boolean $rememberMe Remember me
 */
class LoginForm extends Model
{
    use ModuleTrait;

    /**
     * @var string $username Username
     */
    public $username;

    /**
     * @var string $password Password
     */
    public $password;

    /**
     * @var boolean rememberMe Remember me
     */
    public $rememberMe = true;

    /**
     * @var User|null User instance
     */
    private $_user;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // Required
            [['username', 'password'], 'required'],
            // Password
            ['password', 'validatePassword'],
            // Remember Me
            ['rememberMe', 'boolean']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('users', 'ATTR_USERNAME'),
            'password' => Yii::t('users', 'ATTR_PASSWORD'),
            'rememberMe' => Yii::t('users', 'ATTR_REMEMBER_ME')
        ];
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => ['username', 'password', 'rememberMe'],
            'admin' => ['username', 'password', 'rememberMe']
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     */
    public function validatePassword($attribute, $params)
    {
        $user = $this->getUser();
        if (!$user || !$user->validatePassword($this->$attribute)) {
            $this->addError($attribute, Yii::t('users', 'ERROR_MSG_INVALID_USERNAME_OR_PASSWORD'));
        }
    }

    /**
     * Finds user by username.
     *
     * @return User|null User instance
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $scope = $this->scenario === 'admin' ? ['admin', 'active'] : 'active';
            $this->_user = User::findByUsername($this->username, $scope);
        }
        return $this->_user;
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        return Yii::$app->user->login($this->user, $this->rememberMe ? 3600 * 24 * 30 : 0);
    }
}
