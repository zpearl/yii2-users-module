<?php

namespace zpearl\users\models\frontend;

use zpearl\users\helpers\Security;
use zpearl\users\models\User;
use zpearl\users\traits\ModuleTrait;
use yii\base\Model;
use Yii;

/**
 * Class RecoveryConfirmationForm
 * @package zpearl\users\models
 * RecoveryConfirmationForm is the model behind the recovery confirmation form.
 *
 * @property string $password Password
 * @property string $repassword Repeat password
 * @property string $secure_key Secure key
 */
class RecoveryConfirmationForm extends Model
{
    use ModuleTrait;

    /**
     * @var string $password Password
     */
    public $password;

    /**
     * @var string $repassword Repeat password
     */
    public $repassword;

    /**
     * @var string Confirmation token
     */
    public $secure_key;

    /**
     * @var User User instance
     */
    private $_user;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // Required
            [['password', 'repassword', 'secure_key'], 'required'],
            // Trim
            [['password', 'repassword', 'secure_key'], 'trim'],
            // String
            [['password', 'repassword'], 'string', 'min' => 6, 'max' => 30],
            ['secure_key', 'string', 'max' => 53],
            // Repassword
            ['repassword', 'compare', 'compareAttribute' => 'password'],
            // Secure key
            [
                'secure_key',
                'exist',
                'targetClass' => User::className(),
                'filter' => function ($query) {
                        $query->active();
                    }
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'password' => Yii::t('users', 'ATTR_PASSWORD'),
            'repassword' => Yii::t('users', 'ATTR_REPASSWORD')
        ];
    }

    /**
     * Check if secure key is valid.
     *
     * @return boolean true if secure key is valid
     */
    public function isValidSecureKey()
    {
        if (Security::isValidToken($this->secure_key, $this->module->recoveryWithin) === true) {
            return ($this->_user = User::findBySecureKey($this->secure_key, 'active')) !== null;
        }
        return false;
    }

    /**
     * Recover password.
     *
     * @return boolean true if password was successfully recovered
     */
    public function recovery()
    {
        $model = $this->_user;
        if ($model !== null) {
            return $model->recovery($this->password);
        }
        return false;
    }
}
