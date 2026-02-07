<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Service;
use Exception;

class ServiceFile extends Service
{
    public function deleteDir(array $arr = array()): void
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
                $userId = (isset($arr['user_id'])) ? $arr['user_id'] : $userPHPSESSID[0]['id'];
                $selectFile = $this->getModelFile()->select(
                    $createConnection,
                    array('user_id' => $userId, 'dir_id' => $arr['id'])
                );
                foreach ($selectFile as $oneFile) {
                    $this->deleteFile(array('id' => $oneFile['id']));
                }
                $selectParentDirId = $this->getModelDirectories()->select(
                    $createConnection,
                    array('parent_id' => $arr['id'])
                );
                if ($selectParentDirId) {
                    foreach ($selectParentDirId as $oneParentDirId) {
                        $this->deleteDir(array('id' => $oneParentDirId['id'], 'user_id' => $userId));
                    }
                }
                $this->getModelDirectories()->delete(
                    $createConnection,
                    array('id' => $arr['id'], 'user_id' => $userId)
                );
            } else {
                $this->getResponse()->status(404);
                throw new Exception("Пользователя с таким токеном не существует");
            }
        });
    }

    public function deleteFile(array $arr = array()): void
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
                $userId = (isset($arr['user_id'])) ? $arr['user_id'] : $userPHPSESSID[0]['id'];
                foreach ($arr as $oneFile) {
                    $file [] = $this->getModelFile()->select(
                        $createConnection,
                        array(
                            'id' => $arr['id'],
                            'user_id' => $userId
                        )
                    );
                    if (isset($file[0][0]['path'])) {
                        $filePath = $file[0][0]['path'];
                        $fileUserId =$userId;
                        $arrIn = array("path" => $filePath, "user_id" => $fileUserId);
                        $selectFile = $this->getModelFile()->select($createConnection, $arrIn);
                        if (count($selectFile) == 1 && file_exists($selectFile[0]['path'])) {
                            unlink($selectFile[0]['path']);
                        }
                    }
                    $this->getModelAccess()->delete($createConnection, array('file_id' => $oneFile));
                    $this->getModelFile()->delete($createConnection, array('id' => $oneFile));
                }
            } else {
                $this->getResponse()->status(404);
                throw new Exception("Пользователя с таким токеном не существует");
            }
        });
    }

    public function renameDir(array $arr = array()): void
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
                $selectDirId = $this->getModelDirectories()->select($createConnection, array(
                    'id' => $arr['id'],
                    'user_id' => $userPHPSESSID[0]['id']
                ));
                if ($selectDirId) {
                    $lastSlashPos = strrpos($selectDirId[0]['dir'], '_');
                    $parentId = substr($selectDirId[0]['dir'], $lastSlashPos);
                    $this->getModelDirectories()->update($createConnection, array(
                        'id' => $arr['id'],
                        'user_id' => $userPHPSESSID[0]['id'],
                        'dir' => $arr['dir'] . $parentId,
                    ));
                } else {
                    $this->getResponse()->status(404);
                    throw new Exception('Директория не найдена');
                }
            } else {
                $this->getResponse()->status(404);
                throw new Exception("Пользователя с таким токеном не существует");
            }
        });
    }

    public function addDir(array $arr = array()): void
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
                $selectUser = $this->getModelUser()->select(
                    $createConnection,
                    array(
                        'id' => $userPHPSESSID[0]['id']
                    )
                );
                $selectDir = $this->getModelDirectories()->select($createConnection, array(
                    "user_id" => $userPHPSESSID[0]['id']
                ));
                if ($selectUser) {
                    $inArr [] = null;
                    foreach ($selectDir as $oneDir) {
                        $inArr [] = $oneDir['id'];
                    }
                    unset($inArr[0]);
                    if (in_array($arr['parent_id'], $inArr)) {
                        $this->getModelDirectories()->insert($createConnection, array(
                            'user_id' => $userPHPSESSID[0]['id'],
                            'dir' => $arr['dir'] . "_" . $arr['parent_id'],
                            'parent_id' => $arr['parent_id']
                        ));
                    } else {
                        $this->getModelDirectories()->insert($createConnection, array(
                            'user_id' => $userPHPSESSID[0]['id'],
                            'dir' => $arr['dir'] . "_" . "NULL",
                            'parent_id' => null
                        ));
                    }
                } else {
                    $this->getResponse()->status(404);
                    throw new Exception('Пользователь не найден');
                }
            } else {
                $this->getResponse()->status(404);
                throw new Exception("Пользователя с таким токеном не существует");
            }
        });
    }

    public function addFile(array $arr = array()): void
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
                $file = $arr['file'];
                $maxSize = 2 * 1024 * 1024 * 1024;
                if ($file['error'] == UPLOAD_ERR_OK) {
                    if ($file['size'] > $maxSize) {
                        $this->getResponse()->status(413);
                        throw new Exception("Ошибка: Файл слишком большой! Максимальный размер: 2Gb");
                    } else {
                        $userIdDir = dirname(
                                __DIR__,
                                3
                            ) . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR . $userPHPSESSID[0]['id'];
                        $fileName = basename($file['name']);
                        $dirIdFileName = pathinfo($fileName, PATHINFO_FILENAME) . "_" . $arr['dir_id'] . "." . pathinfo(
                                $fileName,
                                PATHINFO_EXTENSION
                            );
                        $filePath = $userIdDir . DIRECTORY_SEPARATOR . $dirIdFileName;
                        $file = $this->getModelFile()->select(
                            $createConnection,
                            array(
                                "dir_id" => $arr['dir_id'],
                                "user_id" => $userPHPSESSID[0]['id'],
                                "name" => $dirIdFileName
                            )
                        );
                        if (count($file) == 0) {
                            $dir = $this->getModelDirectories()->select(
                                $createConnection,
                                array("id" => $arr['dir_id'], "user_id" => $userPHPSESSID[0]['id'])
                            );
                            if (count($dir) > 0) {
                                $this->getModelFile()->add(
                                    $createConnection,
                                    array(
                                        'user_id' => $userPHPSESSID[0]['id'],
                                        "dir_id" => $arr['dir_id'],
                                        'name' => $dirIdFileName,
                                        'path' => $filePath
                                    )
                                );
                                if ($createConnection->lastInsertId()) {
                                    if (!file_exists($userIdDir)) {
                                        mkdir($userIdDir, 0777, true);
                                    }
                                    $this->getModelAccess()->insert(
                                        $createConnection,
                                        array(
                                            'user_id' => $userPHPSESSID[0]['id'],
                                            'file_id' => $createConnection->lastInsertId()
                                        )
                                    );
                                    move_uploaded_file(
                                        $_FILES['file']['tmp_name'],
                                        $userIdDir . DIRECTORY_SEPARATOR . $dirIdFileName
                                    );
                                }
                            } else {
                                $this->getResponse()->status(409);
                                throw new Exception("Создайте директорию для добавляемого файла");
                            }
                        } else {
                            $this->getResponse()->status(409);
                            throw new Exception('Файл уже существует');
                        }
                        echo "Файл загружен и имеет допустимый размер";
                    }
                } else {
                    $this->getResponse()->status(400);
                    throw new Exception("Ошибка при загрузке файла, код ошибки: " . $file['error']);
                }
            } else {
                $this->getResponse()->status(404);
                throw new Exception("Пользователя с таким токеном не существует");
            }
        });
    }

    public function getFile(array $arr = array()): array
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $result = [];
        $this->getModel()->transaction(function ($createConnection) use ($arr, &$result) {
            $userPHPSESSID = $this->getModelUser()->select(
                $createConnection,
                array('PHPSESSID' => session_id())
            );
            if (!empty($userPHPSESSID)) {
                $result [] = $this->getModelFile()->select($createConnection, $arr);
                if ($result) {
                    var_dump($result[0]);
                } else {
                    $this->getResponse()->status(404);
                    throw new Exception('Файл не найден');
                }
            } else {
                $this->getResponse()->status(404);
                throw new Exception("Пользователя с таким токеном не существует");
            }
        });
        return $result;
    }

    public function getListFiles(): array
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $result = [];
        $this->getModel()->transaction(function ($createConnection) use (&$result) {
            $userPHPSESSID = $this->getModelUser()->select(
                $createConnection,
                array('PHPSESSID' => session_id())
            );
            if (!empty($userPHPSESSID)) {
                $result [] = $this->getModelFile()->select(
                    $createConnection,
                    array(
                        'user_id' => $userPHPSESSID[0]['id']
                    )
                );
                if ($result) {
                    foreach ($result as $oneRes) {
                        var_dump(($oneRes));
                    }
                } else {
                    $this->getResponse()->status(404);
                    throw new Exception('Файлы не найдены');
                }
            } else {
                $this->getResponse()->status(404);
                throw new Exception("Пользователя с таким токеном не существует");
            }
        });
        return $result;
    }

    public function getDirectoryFiles(array $arr = array()): array
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $selectFile = [];
        $this->getModel()->transaction(function ($createConnection) use ($arr, &$selectFile) {
            $userPHPSESSID = $this->getModelUser()->select(
                $createConnection,
                array('PHPSESSID' => session_id())
            );
            if (!empty($userPHPSESSID)) {
                $selectFile [] = $this->getModelFile()->select(
                    $createConnection,
                    array(
                        'dir_id' => $arr['dir_id'],
                        'user_id' => $userPHPSESSID[0]['id']
                    )
                );
                if ($selectFile) {
                    foreach ($selectFile as $oneFile) {
                        var_dump($oneFile);
                    }
                } else {
                    $this->getResponse()->status(404);
                    throw new Exception('В директории фалы не найдены');
                }
            } else {
                $this->getResponse()->status(404);
                throw new Exception("Пользователя с таким токеном не существует");
            }
        });
        return $selectFile;
    }

    public function renameFile(array $arr = array()): void
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
                $file = $this->getModelFile()->select(
                    $createConnection,
                    array("id" => $arr['id'], "user_id" => $userPHPSESSID[0]['id'], "dir_id" => $arr['dir_id'])
                );
                if (!empty($file)) {
                    $userIdDir = dirname(
                            __DIR__,
                            2
                        ) . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR . $userPHPSESSID[0]['id'] . DIRECTORY_SEPARATOR;
                    $oldFilePath = $file[0]['path'];
                    $extFile = pathinfo($oldFilePath, PATHINFO_EXTENSION);
                    $newFileName = $arr['new_name'] . "_" . $file[0]['dir_id'] . "." . $extFile;
                    $newFilePath = $userIdDir . $newFileName;

                    if (file_exists($oldFilePath) && !file_exists($newFilePath)) {
                        rename($oldFilePath, $newFilePath);
                        $this->getModelFile()->update(
                            $createConnection,
                            array('name' => $newFileName, 'path' => $newFilePath, 'id' => $arr['id'])
                        );
                    } else {
                        $this->getResponse()->status(409);
                        throw new Exception('Файл с таким именем уже существует');
                    }
                } else {
                    $this->getResponse()->status(404);
                    throw new Exception('Файл не найден');
                }
            } else {
                $this->getResponse()->status(404);
                throw new Exception("Пользователя с таким токеном не существует");
            }
        });
    }

    public function getListUsersAccess(array $arr = array()): array
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $usersList = [];
        $this->getModel()->transaction(function ($createConnection) use ($arr, &$usersList) {
            $userPHPSESSID = $this->getModelUser()
                ->select(
                    $createConnection,
                    array('PHPSESSID' => session_id())
                );
            if (!empty($userPHPSESSID)) {
                $listUsers = $this->getModelAccess()->select($createConnection, array('file_id' => $arr['file_id']));
                if ($listUsers) {
                    $usersList [] = array_column($listUsers, 'user_id');
                } else {
                    $this->getResponse()->status(409);
                    throw new Exception('Пользователи, имеющие доступ к файлу, не найдены');
                }
            } else {
                $this->getResponse()->status(404);
                throw new Exception("Пользователя с таким токеном не существует");
            }
        });
        return $usersList;
    }

    public function addAccess(array $arr = array()): void
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
                $duplicate = $this->getModelAccess()->select($createConnection, $arr);
                if (count($duplicate) == 0) {
                    $this->getModelAccess()->insert($createConnection, $arr);
                } else {
                    $this->getResponse()->status(409);
                    throw new Exception('Пользователю файл доступен');
                }
            } else {
                $this->getResponse()->status(404);
                throw new Exception("Пользователя с таким токеном не существует");
            }
        });
    }

    public function deleteAccess(array $arr = array()): void
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
                $this->getModelAccess()->delete($createConnection, $arr);
            } else {
                $this->getResponse()->status(404);
                throw new Exception("Пользователя с таким токеном не существует");
            }
        });
    }
}
