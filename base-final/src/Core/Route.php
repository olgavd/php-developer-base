<?php

declare(strict_types=1);

namespace App\Core;

use App\Controllers\ControllerAdmin;
use App\Controllers\ControllerFile;
use App\Controllers\ControllerUser;
use Exception;

//header('Content-Type:src/json');

class Route
{
    private array $urlList =
        [
            '/users/list' => ['GET' => 'getListUsers'],

            '/users/get' => ['GET' => 'getUser'],

            '/users/update' => ['PUT' => 'updateUser'],

            '/users/login' => ['POST' => 'login'],

            '/users/logout' => ['GET' => 'logout'],

            '/users/reset_password' => ['POST' => 'resetPassword'],

            '/admin/users/add' => ['POST' => 'addUser'],

            '/admin/users/list' => ['GET' => 'getListUsers'],

            '/admin/users/get/{id}' => ['GET' => 'getUser'],

            '/admin/users/delete/{id}' => ['DELETE' => 'deleteUser'],

            '/admin/users/update/{id}' => ['PUT' => 'updateUser'],

            '/files/list' => ['GET' => 'getListFiles'],

            '/files/get/{id}' => ['GET' => 'getFile'],

            '/files/add' => ['POST' => 'addFile'],

            '/files/rename/{id}' => ['PUT' => 'renameFile'],

            '/files/remove/{id}' => ['DELETE' => 'deleteFile'],

            '/directories/add' => ['POST' => 'addDirectory'],

            '/directories/rename/{id}' => ['PUT' => 'renameDirectory'],

            '/directories/get/{id}' => ['GET' => 'getDirectoryFiles'],

            '/directories/delete/{id}' => ['DELETE' => 'deleteDirectory'],

            '/files/share/{id}' => ['GET' => 'getListUsersAccess'],

            '/files/share/add/{id}/{user_id}' => ['PUT' => 'addAccess'],

            '/files/share/delete/{id}/{user_id}' => ['DELETE' => 'deleteAccess'],

            '/user/search/{email}' => ['GET' => 'searchUser'],
        ];

    /**
     * @throws Exception
     */
    public function start(): void
    {
        $projectNameDir = '/base-final';
        $request = new Request();
        $response = new Response();
        $idStr = str_replace($projectNameDir, "", $request->server("REQUEST_URI"));
        $partsIdStr = explode('/', $idStr);

        if (
            str_contains($idStr, "users") &&
            !str_contains($idStr, "admin")
        ) {
            $objName = new ControllerUser();
        } elseif (
            str_contains($idStr, "users") &&
            str_contains($idStr, "admin") ||
            str_contains($idStr, "search")
        ) {
            $objName = new ControllerAdmin();
        } elseif (
            str_contains($idStr, "files") ||
            str_contains($idStr, "directories")
        ) {
            $objName = new ControllerFile();
        } else {
            $response->status(404);
            throw new Exception("Страница не найдена");
        }

        if (str_contains($request->server("REQUEST_URI"), $projectNameDir)) {
            if (
                ctype_alpha(end($partsIdStr)) ||
                str_contains(end($partsIdStr), '_') &&
                array_key_exists($idStr, $this->urlList)
            ) {
                call_user_func(
                    array(
                        $objName,
                        $this->urlList[$idStr][$request->server("REQUEST_METHOD")]
                    )
                );
            } elseif (
                ctype_digit(end($partsIdStr)) &&
                ctype_alpha($partsIdStr[count($partsIdStr) - 2])
            ) {
                $endpoint = str_replace(end($partsIdStr), '{id}', $idStr);
                if (array_key_exists($endpoint, $this->urlList)) {
                    call_user_func(
                        array(
                            $objName,
                            $this->urlList[$endpoint][$request->server("REQUEST_METHOD")]
                        ),
                        intval(
                            end($partsIdStr)
                        )
                    );
                }
            } elseif (
                ctype_digit(end($partsIdStr)) &&
                ctype_digit($partsIdStr[count($partsIdStr) - 2])
            ) {
                $prevIndex = count($partsIdStr) - 2;
                $lastIndex = count($partsIdStr) - 1;
                $prev = intval($partsIdStr[$prevIndex]);
                $last = intval($partsIdStr[$lastIndex]);
                $partsIdStr[$prevIndex] = "{id}";
                $partsIdStr[$lastIndex] = "{user_id}";
                $endpoint = implode('/', $partsIdStr);
                if (array_key_exists($endpoint, $this->urlList)) {
                    call_user_func(
                        array(
                            $objName,
                            $this->urlList[$endpoint][$request->server("REQUEST_METHOD")]
                        ),
                        $prev,
                        $last
                    );
                } else {
                    $response->status(404);
                    throw new Exception("Страница не найдена");
                }
            } elseif (filter_var(end($partsIdStr), FILTER_VALIDATE_EMAIL)) {
                $endpoint = str_replace(end($partsIdStr), '{email}', $idStr);
                if (array_key_exists($endpoint, $this->urlList)) {
                    call_user_func(
                        array(
                            $objName,
                            $this->urlList[$endpoint][$request->server("REQUEST_METHOD")]
                        ),
                        end($partsIdStr)
                    );
                } else {
                    $response->status(404);
                    throw new Exception("Страница не найдена");
                }
            } else {
                $response->status(422);
                throw new Exception("Страница не найдена");
            }
        } else {
            $response->status(404);
            throw new Exception("Страница не найдена");
        }
    }
}
