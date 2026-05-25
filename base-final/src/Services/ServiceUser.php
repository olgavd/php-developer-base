<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Service;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;

class ServiceUser extends Service
{
    public function getUser(): array
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $userInfo = [];
        $this->getModel()->transaction(function ($createConnection) use (&$userInfo) {
            $userPHPSESSID = $this->getModelUser()->select($createConnection, array('PHPSESSID' => session_id()));
            if (!empty($userPHPSESSID)) {
                $userInfo [] = $this->getModelUser()->select($createConnection, array('id' => $userPHPSESSID[0]['id']));
                if (!$userInfo) {
                    $this->getResponse()->status(404);
                    throw new Exception('Пользователя не существует');
                }
            } else {
                $this->getResponse()->status(404);
                throw new Exception("Пользователя с таким токеном не существует");
            }
        });
        return $userInfo[0][0];
    }

    public function getListUsers(array $arr = array()): array
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $part = [];
        $this->getModel()->transaction(function ($createConnection) use ($arr, &$part) {
            $userPHPSESSID = $this->getModelUser()->select($createConnection, array('PHPSESSID' => session_id()));
            if (!empty($userPHPSESSID)) {
                $userList = $this->getModelUser()->select($createConnection, $arr);
                if ($userList) {
                    foreach ($userList as $one) {
                        $part [] = array_slice($one, 1, 3);
                    }
                } else {
                    $this->getResponse()->status(404);
                    throw new Exception('Пользователи не существуют');
                }
            } else {
                $this->getResponse()->status(404);
                throw new Exception('Пользователя с таким токеном не существует');
            }
        });
        return $part;
    }

    public function updateUser(array $arr = array()): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->getModel()->transaction(function ($createConnection) use ($arr) {
            $userPHPSESSID = $this->getModelUser()->select($createConnection, array('PHPSESSID' => session_id()));
            if (!empty($userPHPSESSID)) {
                $userId = $this->getModelUser()->select($createConnection, array('id' => $userPHPSESSID[0]['id']));
                if (!empty($userId)) {
                    $this->getModelUser()->update($createConnection, array(
                        'id' => $userPHPSESSID[0]['id'],
                        'password' => password_hash($arr['password'], PASSWORD_DEFAULT)
                    ));
                } else {
                    $this->getResponse()->status(404);
                    throw new Exception("Пользователя не существует");
                }
            } else {
                $this->getResponse()->status(404);
                throw new Exception('Пользователя с таким токеном не существует');
            }
        });
    }

    public function login(array $arr = array()): void
    {
        $this->getModel()->transaction(function ($createConnection) use ($arr) {
            $selArr = $this->getModelUser()->select($createConnection, array('email' => $arr['email']));
            if ($selArr) {
                $column = array_column($selArr, 'password');
                $arrShift = array_shift($column);
                if (
                    $arrShift == "" && $arrShift === $arr['password'] ||
                    password_verify($arr['password'], $arrShift)
                ) {
                    session_start();
                    $this->getModelUser()->update(
                        $createConnection,
                        array(
                            'email' => $arr['email'],
                            'PHPSESSID' => session_id()
                        )
                    );
                } else {
                    $this->getResponse()->status(401);
                    throw new Exception('Неверные логин или пароль');
                }
            } else {
                $this->getResponse()->status(404);
                throw new Exception('Пользователь не найден');
            }
        });
    }

    public function logout(): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->getModel()->transaction(function ($createConnection) {
            $user = $this->getModelUser()->select(
                $createConnection,
                array('PHPSESSID' => session_id())
            );

            if ($user) {
                $id = $user[0]['id'];
                $this->getModelUser()->update(
                    $createConnection,
                    array(
                        'id' => $id,
                        "PHPSESSID" => null
                    )
                );
            } else {
                $this->getResponse()->status(401);
                throw new Exception('Неверный токен пользователя');
            }
        });
        $params = session_get_cookie_params();
        setcookie(session_name(), "", -1, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        session_destroy();
    }

    /**
     * @throws Exception
     */
    public function reset_password(array $arr = array()): void
    {
        if (
            $arr[key($arr)] != "" &&
            filter_var($arr[key($arr)], FILTER_VALIDATE_EMAIL) !== false
        ) {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $this->getModel()->transaction(function ($createConnection) use ($arr) {
                $userPHPSESSID = $this->getModelUser()->select($createConnection, array('PHPSESSID' => session_id()));
                if (!empty($userPHPSESSID)) {
                    $mail = new PHPMailer;
                    try {
                        $mail->SMTPDebug = 2;         /*Оставляем как есть*/
                        $mail->isSMTP();              /*Запускаем настройку SMTP*/
                        $mail->Host = 'smtp.mail.ru'; /*Выбираем сервер SMTP*/
                        $mail->SMTPAuth = true;        /*Активируем авторизацию на своей почте*/
                        $mail->Username = 'o@mail.ru';   /*Имя(логин) от аккаунта почты отправителя */
                        $mail->Password = 'o';
                        /*Пароль от аккаунта почты отправителя */
                        $mail->SMTPSecure = 'ssl';            /*Указываем протокол*/
                        $mail->Port = 465;            /*Указываем порт*/
                        $mail->CharSet = 'UTF-8';/*Выставляем кодировку*/

                        $mail->setFrom(
                            'o@mail.ru',
                            'Ольга'
                        );/*Указываем адрес почты отправителя */ /*Указываем перечень адресов почты куда отсылаем сообщение*/
                        $mail->addAddress($arr['email'], 'Ольга');
                        $mail->isHTML(false);      /*формируем html сообщение*/
                        $mail->Subject = "Заголовок"; /*Заголовок сообщения*/
                        $userArr = $this->getModelUser()->select($createConnection, array('email' => $arr[key($arr)]));
                        if ($userArr) {
                            $this->getModelUser()->update(
                                $createConnection,
                                array('email' => $arr['email'], 'password' => null)
                            );
                        } else {
                            $this->getResponse()->status(404);
                            throw new Exception('Пользователь не найден');
                        }
                        $mail->Body = "http://localhost/php-developer-base/base-final-change";/* Текст сообщения */
                        $mail->AltBody = "Сброс пароля";/*Описание сообщения */
                        $mail->send();

                        if (!$mail->send()) {
                            throw new \PHPMailer\PHPMailer\Exception("не удалось отправить письмо");
                        }
                    } catch (\PHPMailer\PHPMailer\Exception $mail) {
                        $this->getLogger()->error('Ошибка PHPMailer: ' . $mail->getMessage());
                    }
                } else {
                    $this->getResponse()->status(404);
                    throw new Exception('Пользователя с таким токеном не существует');
                }
            });
        }
    }
}
