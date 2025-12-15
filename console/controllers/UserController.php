<?php

namespace console\controllers;

use common\models\User;
use Yii;
use yii\console\Controller;

class UserController extends Controller
{
    public function actionCreate($username, $email, $password)
    {
        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->setPassword($password);
        $user->generateAuthKey();
        $user->status = User::STATUS_ACTIVE;
        
        if ($user->save()) {
            echo "Пользователь создан успешно!\n";
            echo "Логин: $username\n";
            echo "Пароль: $password\n";
            return 0;
        } else {
            echo "Ошибка при создании пользователя:\n";
            foreach ($user->errors as $errors) {
                foreach ($errors as $error) {
                    echo "- $error\n";
                }
            }
            return 1;
        }
    }
}