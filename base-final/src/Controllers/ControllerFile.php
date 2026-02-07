<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use Exception;

class ControllerFile extends Controller
{
    /**
     * @throws \Exception
     */
    public function addDirectory(): void
    {
        if ($this->getRequest()->isPost()) {
            if ((
                !empty($this->getRequest()->post('parent_id')) &&
                !empty($this->getRequest()->post('dir'))
            )) {
                $this->getServiceFile()->addDir(array(
                    'parent_id' => intval($this->getRequest()->post('parent_id')),
                    'dir' => $this->getRequest()->post('dir')
                ));
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
    public function addFile(): void
    {
        if ($this->getRequest()->isPost()) {
            if (!empty($this->getRequest()->post('dir_id')) &&
                $this->getRequest()->file('file') !== null) {
                $this->getServiceFile()->addFile(array(
                    'dir_id' => intval($this->getRequest()->post('dir_id')),
                    'file' => $this->getRequest()->file('file')
                ));
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
    public function getListFiles(): void
    {
        if ($this->getRequest()->isGet()) {
            $this->getServiceFile()->getListFiles();
        } else {
            $this->getResponse()->status(405);
            throw new Exception('Необходим метод GET');
        }
    }

    /**
     * @throws Exception
     */
    public function getFile(int $fileId): void
    {
        if ($this->getRequest()->isGet()) {
            if ($fileId > 0) {
                $this->getServiceFile()->getFile(array("id" => $fileId));
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
    public function renameFile(int $fileId): void
    {
        if ($this->getRequest()->isPut()) {
            $arr = json_decode(file_get_contents('php://input'), true);
            if (
                $fileId > 0 &&
                isset($arr['dir_id']) &&
                isset($arr['new_name'])
            ) {
                $this->getServiceFile()->renameFile(array(
                    'id' => $fileId,
                    'dir_id' => $arr['dir_id'],
                    'new_name' => $arr['new_name']
                ));
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
    public function deleteFile(int $fileId): void
    {
        if ($this->getRequest()->isDelete()) {
            if ($fileId > 0) {
                $this->getServiceFile()->deleteFile(array('id' => $fileId));
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
    public function renameDirectory(int $dirId): void
    {
        if ($this->getRequest()->isPut()) {
            $arr = json_decode(file_get_contents('php://input'), true);
            if ($dirId > 0 && isset($arr['user_id']) && isset($arr['dir'])) {
                $this->getServiceFile()->renameDir(array(
                    'id' => $dirId,
                    'user_id' => $arr['user_id'],
                    'dir' => $arr['dir']
                ));
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
    public function getDirectoryFiles(int $dirId): void
    {
        if ($this->getRequest()->isGet()) {
            if ($dirId > 0) {
                $this->getServiceFile()->getDirectoryFiles(array('dir_id' => $dirId));
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
    public function deleteDirectory(int $dirId): void
    {
        if ($this->getRequest()->isDelete()) {
            if ($dirId > 0) {
                $this->getServiceFile()->deleteDir(array('id' => $dirId));
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
    public function addAccess(int $fileId, int $userId): void
    {
        if ($this->getRequest()->isPut()) {
            if ($fileId > 0 && $userId > 0) {
                $this->getServiceFile()->addAccess(array('file_id' => $fileId, 'user_id' => $userId));
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
    public function getListUsersAccess(int $fileId): void
    {
        if ($this->getRequest()->isGet()) {
            if ($fileId > 0) {
                var_dump($this->getServiceFile()->getListUsersAccess(array('file_id' => $fileId))[0]);
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
    public function deleteAccess(int $fileId, int $userId): void
    {
        if ($this->getRequest()->isDelete()) {
            if ($fileId > 0 && $userId > 0) {
                $this->getServiceFile()->deleteAccess(array('file_id' => $fileId, 'user_id' => $userId));
            } else {
                $this->getResponse()->status(400);
                throw new Exception('Отсутствуют данные');
            }
        } else {
            $this->getResponse()->status(405);
            throw new Exception('Необходим метод DELETE');
        }
    }
}
