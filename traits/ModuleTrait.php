<?php

namespace zpearl\users\traits;

use Yii;

/**
 * Class ModuleTrait
 * @package zpearl\users\traits
 * Implements `getModule` method, to receive current module instance.
 */
trait ModuleTrait
{
    /**
     * @var \zpearl\users\Module|null Module instance
     */
    private $_module;

    /**
     * @return \zpearl\users\Module|null Module instance
     */
    public function getModule()
    {
        if ($this->_module === null) {
            $this->_module = Yii::$app->getModule('users');
        }
        return $this->_module;
    }
}
