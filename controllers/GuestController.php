<?php

namespace zpearl\users\controllers;

use zpearl\users\models\frontend\ActivationForm;
use zpearl\users\models\frontend\RecoveryConfirmationForm;
use zpearl\users\models\frontend\RecoveryForm;
use zpearl\users\models\frontend\ResendForm;
use zpearl\users\models\frontend\User;
use zpearl\users\models\LoginForm;
use zpearl\users\models\Profile;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;
use yii\widgets\ActiveForm;
use Yii;

/**
 * Controller only for guest users.
 */
class GuestController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['?']
                    ]
                ]
            ]
        ];
    }

    /**
     * Sign Up page.
     * If record will be successful created, user will be redirected to home page.
     */
    public function actionSignup()
    {
        $user = new User(['scenario' => 'signup']);
        $profile = new Profile();

        if ($user->load(Yii::$app->request->post()) && $profile->load(Yii::$app->request->post())) {
            if ($user->validate() && $profile->validate()) {
                $user->populateRelation('profile', $profile);
                if ($user->save(false)) {
                    if ($this->module->requireEmailConfirmation === true) {
                        Yii::$app->session->setFlash(
                            'success',
                            Yii::t(
                                'users',
                                'FRONTEND_FLASH_SUCCESS_SIGNUP_WITHOUT_LOGIN',
                                [
                                    'url' => Url::toRoute('resend')
                                ]
                            )
                        );
                    } else {
                        Yii::$app->user->login($user);
                        Yii::$app->session->setFlash(
                            'success',
                            Yii::t('users', 'FRONTEND_FLASH_SUCCESS_SIGNUP_WITH_LOGIN')
                        );
                    }
                    return $this->goHome();
                } else {
                    Yii::$app->session->setFlash('danger', Yii::t('users', 'FRONTEND_FLASH_FAIL_SIGNUP'));
                    return $this->refresh();
                }
            } elseif (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($user);
            }
        }

        return $this->render(
            'signup',
            [
                'user' => $user,
                'profile' => $profile
            ]
        );
    }

    /**
     * Resend page.
     * Resend email confirmation token.
     */
    public function actionResend()
    {
        $model = new ResendForm();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->resend()) {
                    Yii::$app->session->setFlash('success', Yii::t('users', 'FRONTEND_FLASH_SUCCESS_RESEND'));
                    return $this->goHome();
                } else {
                    Yii::$app->session->setFlash('danger', Yii::t('users', 'FRONTEND_FLASH_FAIL_RESEND'));
                    return $this->refresh();
                }
            } elseif (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
        }

        return $this->render(
            'resend',
            [
                'model' => $model
            ]
        );
    }

    /**
     * Log In page.
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            $this->goHome();
        }

        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->login()) {
                    return $this->goBack();
                }
            } elseif (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
        }

        return $this->render(
            'login',
            [
                'model' => $model
            ]
        );
    }

    /**
     * Activation page.
     * Activate a new user.
     *
     * @param string $key Activation token.
     */
    public function actionActivation($key)
    {
        $model = new ActivationForm(['secure_key' => $key]);

        if ($model->validate() && $model->activation()) {
            Yii::$app->session->setFlash('success', Yii::t('users', 'FRONTEND_FLASH_SUCCESS_ACTIVATION'));
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('users', 'FRONTEND_FLASH_FAIL_ACTIVATION'));
        }

        return $this->goHome();
    }

    /**
     * Recovery page.
     * Request password recovery.
     */
    public function actionRecovery()
    {
        $model = new RecoveryForm();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->recovery()) {
                    Yii::$app->session->setFlash('success', Yii::t('users', 'FRONTEND_FLASH_SUCCESS_RECOVERY'));
                    return $this->goHome();
                } else {
                    Yii::$app->session->setFlash('danger', Yii::t('users', 'FRONTEND_FLASH_FAIL_RECOVERY'));
                    return $this->refresh();
                }
            } elseif (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
        }

        return $this->render(
            'recovery',
            [
                'model' => $model
            ]
        );
    }

    /**
     * Recovery confirmation page.
     * Confirm password recovery request.
     *
     * @param string $key Confirmation token
     */
    public function actionRecoveryConfirmation($key)
    {
        $model = new RecoveryConfirmationForm(['secure_key' => $key]);

        if (!$model->isValidSecureKey()) {
            Yii::$app->session->setFlash(
                'danger',
                Yii::t('users', 'FRONTEND_FLASH_FAIL_RECOVERY_CONFIRMATION_WITH_INVALID_KEY')
            );
            return $this->goHome();
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->recovery()) {
                    Yii::$app->session->setFlash(
                        'success',
                        Yii::t('users', 'FRONTEND_FLASH_SUCCESS_RECOVERY_CONFIRMATION')
                    );
                    return $this->goHome();
                } else {
                    Yii::$app->session->setFlash(
                        'danger',
                        Yii::t('users', 'FRONTEND_FLASH_FAIL_RECOVERY_CONFIRMATION')
                    );
                    return $this->refresh();
                }
            } elseif (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
        }

        return $this->render(
            'recovery-confirmation',
            [
                'model' => $model
            ]
        );

    }
}
