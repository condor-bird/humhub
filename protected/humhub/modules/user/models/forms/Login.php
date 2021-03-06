<?php

namespace humhub\modules\user\models\forms;

use Yii;
use yii\base\Model;
use humhub\modules\user\authclient\BaseFormAuth;
use humhub\modules\user\models\User;
use humhub\modules\user\libs\Ldap;
use humhub\models\Setting;
use yii\db\Expression;

/**
 * LoginForm is the model behind the login form.
 */
class Login extends Model
{

    /**
     * @var string user's username or email address
     */
    public $username;

    /**
     * @var string password
     */
    public $password;

    /**
     * @var boolean remember user
     */
    public $rememberMe = false;

    /**
     * @var \yii\authclient\BaseClient auth client used to authenticate
     */
    public $authClient = null;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->rememberMe = Yii::$app->getModule('user')->loginRememberMeDefault;
        
        parent::init();
    }

    /**
     * Validation
     */
    public function afterValidate()
    {
        $user = null;

        // Loop over enabled authclients
        foreach (Yii::$app->authClientCollection->getClients() as $authClient) {
            if ($authClient instanceof BaseFormAuth) {
                $authClient->login = $this;
                if ($authClient->auth()) {
                    $this->authClient = $authClient;

                    // Delete password after successful auth
                    $this->password = "";

                    return;
                }
            }
        }

        if ($user === null) {
            $this->addError('password', 'User or Password incorrect.');
        }

        // Delete current password value
        $this->password = "";

        parent::afterValidate();
    }

}
