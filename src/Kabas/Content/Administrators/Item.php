<?php

namespace Kabas\Content\Administrators;

use Kabas\App;
use Kabas\Utils\File;
use Kabas\Utils\Session;
use Kabas\Content\BaseItem;

class Item extends BaseItem
{
    public $directory = 'administrators';

    public function __construct($data, $encrypt)
    {
        $this->checkDirectoryIntegrity();
        if($encrypt) $data->password = password_hash($data->password, PASSWORD_BCRYPT);
        return parent::__construct($data);
    }

    protected function checkDirectoryIntegrity()
    {
        if(!is_dir(STORAGE_PATH . DS . 'administrators')) mkdir(STORAGE_PATH . DS . 'administrators', 0777, true);
    }

    public function setData($data)
    {
        $this->set($data);
        $this->data = $data;
    }

    protected function loadStructure()
    {
        $this->structure = (object) [
            'fields' => [
                "username" => [
                    "type" => "text",
                    "label" => "Username"
                ],
                "password" => [
                    "type" => "text",
                    "label" => "Password"
                ]
            ]
        ];
    }

    public function persist()
    {  
        $username = $this->data->username;
        unset($this->data->username);
        return File::writeJson($this->data, $this->getPath($username));
    }

    public function login($password)
    {
        if($matches = password_verify($password, (string) $this->password)) {
            Session::set('_kabas.authenticated', true);
        }
        return $matches;
    }

    protected function getPath($username)
    {
        return STORAGE_PATH . DS . $this->directory . DS . $username;
    }
}
