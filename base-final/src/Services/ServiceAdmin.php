<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Service;
use Exception;

class ServiceAdmin extends Service
{
    public function getUser(array $arr = array()): array
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $user = [];
        $this->getModel()->transaction(function ($createConnection) use ($arr, &$user) {
            $userPHPSESSID = $this->getModelUser()->select(
                $createConnection,
                array('PHPSESSID' => session_id())
            );
            if (!empty($userPHPSESSID)) {
                $part = $userPHPSESSID[0]['part'];
                if ($part == 'admin') {
                    $user [] = $this->getModelUser()->select($createConnection, $arr);
                    if ($user) {
                        var_dump($user[0]);
                    } else {
                        $this->getResponse()->status(404);
                        throw new Exception("Пользователь не найден");
                    }
                } else {
                    $this->getResponse()->status(403);
                    throw new Exception('Недостаточно прав');
                }
            } else {
                $this->getResponse()->status(404);
                throw new Exception('Пользователя с таким токеном не существует');
            }
        });
        return $user;
    }

    public function getListUsers(array $arr = array()): array
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $oneUser = [];
        $this->getModel()->transaction(function ($createConnection) use ($arr, &$oneUser) {
            $userPHPSESSID = $this->getModelUser()->select(
                $createConnection,
                array('PHPSESSID' => session_id())
            );
            if (!empty($userPHPSESSID)) {
                $part = $userPHPSESSID[0]['part'];
                if ($part == 'admin') {
                    $userList [] = $this->getModelUser()->select($createConnection, $arr);
                    if ($userList) {
                        foreach ($userList as $oneUser) {
                            var_dump($oneUser);
                        }
                    } else {
                        $this->getResponse()->status(404);
                        throw new Exception("Пользователи не найдены");
                    }
                } else {
                    $this->getResponse()->status(403);
                    throw new Exception('Недостаточно прав');
                }
            } else {
                $this->getResponse()->status(404);
                throw new Exception('Пользователя с таким токеном не существует');
            }
        });
        return $oneUser;
    }

    public function updateUser(array $arr = array()): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->getModel()->transaction(function ($createConnection) use ($arr) {
            $userPHPSESSID = $this->getModelUser()->select(
                $createConnection,
                array('PHPSESSID' => session_id())
            );
            if (!empty($userPHPSESSID)) {
                $part = $userPHPSESSID[0]['part'];
                if ($part == 'admin') {
                    $user = $this->getModelUser()->select($createConnection, array('id' => $arr['id']));
                    if ($user) {
                        $this->getModelUser()->update($createConnection, $arr);
                    } else {
                        $this->getResponse()->status(404);
                        throw new Exception("Пользователь не найден");
                    }
                } else {
                    $this->getResponse()->status(403);
                    throw new Exception('Недостаточно прав');
                }
            } else {
                $this->getResponse()->status(404);
                throw new Exception('Пользователя с таким токеном не существует');
            }
        });
    }

    public function deleteUser(array $arr = array()): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $deleteDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR . $arr['id'];
        $this->getModel()->transaction(function ($createConnection) use ($arr, $deleteDir) {
            $userPHPSESSID = $this->getModelUser()->select(
                $createConnection,
                array('PHPSESSID' => session_id())
            );
            if (!empty($userPHPSESSID)) {
                $part = $userPHPSESSID[0]['part'];
                $myId = $userPHPSESSID[0]['id'];
                if ($part == 'admin' && $myId !== $arr['id']) {
                    $user = $this->getModelUser()->select($createConnection, array('id' => $arr['id']));
                    if ($user) {
                        $userDirectories = $this->getModelDirectories()->select(
                            $createConnection,
                            array('user_id' => $arr['id'])
                        );
                        if ($userDirectories) {
                            foreach ($userDirectories as $oneDir) {
                                $serviceFile = new ServiceFile();
                                $serviceFile->deleteDir(array('user_id' => $arr['id'], 'id' => $oneDir['id']));
                            }
                            if (file_exists($deleteDir)) {
                                foreach (scandir($deleteDir) as $deleteFile) {
                                    if (!is_dir($deleteFile)) {
                                        unlink($deleteDir . DIRECTORY_SEPARATOR . $deleteFile);
                                    }
                                }
                                rmdir($deleteDir);
                            }
                        }
                        $this->getModelAccess()->delete($createConnection, array('user_id' => $arr['id']));
                        $this->getModelUser()->delete($createConnection, $arr);
                    } else {
                        $this->getResponse()->status(404);
                        throw new Exception("Пользователь не найден");
                    }
                } else {
                    $this->getResponse()->status(403);
                    throw new Exception('Недостаточно прав');
                }
            } else {
                $this->getResponse()->status(404);
                throw new Exception('Пользователя с таким токеном не существует');
            }
        });
    }

    public function addUser(array $arr = array()): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->getModel()->transaction(function ($createConnection) use ($arr) {
            $userPHPSESSID = $this->getModelUser()->select(
                $createConnection,
                array('PHPSESSID' => session_id())
            );
            if (!empty($userPHPSESSID)) {
                $part = $userPHPSESSID[0]['part'];
                if ($part == 'admin') {
                    if (count($arr) == 6 && filter_var($arr['email'], FILTER_VALIDATE_EMAIL) !== false) {
                        $this->getModelUser()->insert($createConnection, array(
                            'name' => $arr['name'],
                            'age' => $arr['age'],
                            'gender' => $arr['gender'],
                            'email' => $arr['email'],
                            'password' => password_hash($arr['password'], PASSWORD_DEFAULT),
                            'part' => $arr['part']
                        ));
                    } else {
                        $this->getResponse()->status(401);
                        throw new Exception("Недостаточно данных");
                    }
                } else {
                    $this->getResponse()->status(403);
                    throw new Exception('Недостаточно прав');
                }
            } else {
                $this->getResponse()->status(404);
                throw new Exception('Пользователя с таким токеном не существует');
            }
        });
    }

    public function searchUser(array $arr = array()): array
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $user = [];
        $this->getModel()->transaction(function ($createConnection) use ($arr, &$user) {
            $userPHPSESSID = $this->getModelUser()->select(
                $createConnection,
                array('PHPSESSID' => session_id())
            );
            if (!empty($userPHPSESSID)) {
                $part = $userPHPSESSID[0]['part'];
                if ($part == 'admin') {
                    $user [] = $this->getModelUser()->select($createConnection, array('email' => $arr['email']));
                    if ($user) {
                        var_dump($user);
                    } else {
                        $this->getResponse()->status(404);
                        throw new Exception('Пользователь не найден');
                    }
                } else {
                    $this->getResponse()->status(403);
                    throw new Exception('Недостаточно прав');
                }
            } else {
                $this->getResponse()->status(404);
                throw new Exception('Пользователя с таким токеном не существует');
            }
        });
        return $user;
    }
}
