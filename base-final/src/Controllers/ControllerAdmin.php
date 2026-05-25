<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use Exception;

class ControllerAdmin extends Controller
{
    /**
     * @throws Exception
     */
    public function getUser(int $id): void
    {
        if ($this->getRequest()->isGet()) {
            if ($id > 0) {
                $this->getServiceAdmin()->getUser(array("id" => $id));
            } else {
                $this->getResponse()->status(400);
                throw new Exception('Отсутствуют данные');
            }
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
            $this->getServiceAdmin()->getListUsers(array('1' => 1));
        } else {
            $this->getResponse()->status(405);
            throw new Exception('Необходим метод GET');
        }
    }

    /**
     * @throws Exception
     */
    public function updateUser(int $id): void
    {
        if ($this->getRequest()->isPut()) {
            $part = json_decode(file_get_contents('php://input'), true);
            if ($id > 0 && isset($part['part'])) {
                $this->getServiceAdmin()->updateUser(array('id' => $id, 'part' => $part['part']));
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
    public function deleteUser(int $id): void
    {
        if ($this->getRequest()->isDelete()) {
            if ($id > 0) {
                $this->getServiceAdmin()->deleteUser(array('id' => $id));
            } else {
                $this->getResponse()->status(400);
                throw new Exception('Отсутствуют данные');
            }
        } else {
            $this->getResponse()->status(405);
            throw new Exception('Необходим метод DELETE');
        }
    }

    /**
     * @throws Exception
     */
    public function addUser(): void
    {
        if ($this->getRequest()->isPost()) {
            if (
                !empty($_POST['name']) &&
                !empty($_POST['age']) &&
                !empty($_POST['gender']) &&
                !empty($_POST['email']) &&
                !empty($_POST['password'])
            ) {
                $this->getServiceAdmin()->addUser($_POST);
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
    public function searchUser(string $email): void
    {
        if ($this->getRequest()->isGet()) {
            if (strlen($email) > 0) {
                $this->getServiceAdmin()->searchUser(array('email' => $email));
            } else {
                $this->getResponse()->status(400);
                throw new Exception('Отсутствуют данные');
            }
        } else {
            $this->getResponse()->status(405);
            throw new Exception('Необходим метод GET');
        }
    }
}
