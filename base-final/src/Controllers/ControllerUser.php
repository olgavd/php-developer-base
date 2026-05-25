<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use Exception;

class ControllerUser extends Controller
{
    /**
     * @throws Exception
     */
    public function getUser(): void
    {
        if ($this->getRequest()->isGet()) {
            var_dump($this->getServiceUser()->getUser());
        } else {
            $this->getResponse()->status(405);
            throw new Exception('Необходим метод GET');
        }
    }

    /**
     * @throws Exception
     */
    public function getListUsers(): void
    {
        if ($this->getRequest()->isGet()) {
            var_dump($this->getServiceUser()->getListUsers(array('1' => 1)));
        } else {
            $this->getResponse()->status(405);
            throw new Exception('Необходим метод GET');
        }
    }

    /**
     * @throws Exception
     */
    public function updateUser(): void
    {
        if ($this->getRequest()->isPut()) {
            $arr = json_decode(file_get_contents('php://input'), true);
            if (!empty($arr)) {
                $this->getServiceUser()->updateUser(array('password' => $arr['password']));
            } else {
                $this->getResponse()->status(400);
                throw new Exception('Отсутствуют данные');
            }
        } else {
            $this->getResponse()->status(405);
            throw new Exception('Необходим метод PUT');
        }
    }

    /**
     * @throws Exception
     */
    public function login(): void
    {
        if ($this->getRequest()->isPost()) {
            if (
                !empty($_POST['email']) &&
                !empty($_POST['password'])
            ) {
                $this->getServiceUser()->login(
                    array(
                        'email' => $_POST['email'],
                        'password' => $_POST['password']
                    )
                );
            } else {
                $this->getResponse()->status(400);
                throw new Exception('Отсутствуют данные');
            }
        } else {
            $this->getResponse()->status(405);
            throw new Exception('Необходим метод POST');
        }
    }

    /**
     * @throws Exception
     */
    public function logout(): void
    {
        if ($this->getRequest()->isGet()) {
            $this->getServiceUser()->logout();
        } else {
            $this->getResponse()->status(405);
            throw new Exception('Необходим метод GET');
        }
    }

    /**
     * @throws Exception
     */
    public function resetPassword(): void
    {
        if ($this->getRequest()->isPost()) {
            if (!empty($_POST['email'])) {
                $this->getServiceUser()->reset_password(
                    array(
                        'email' => $_POST['email']
                    )
                );
            } else {
                $this->getResponse()->status(400);
                throw new Exception('Отсутствуют данные');
            }
        } else {
            $this->getResponse()->status(405);
            throw new Exception('Необходим метод POST');
        }
    }
}
