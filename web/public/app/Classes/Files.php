<?php


namespace app\Classes;


class Files {

    private $name;
    private $tmpName;
    private $extension;

    private $allowedFileExtension = array('gif', 'jpg', 'png');
    private $filesStorageDir  = 'files';

    public function __construct(array $userfile) {
        $this->name = $userfile['name'];
        $this->tmpName = $userfile['tmp_name'];
        $this->extension = $this->_getFileExtension();
    }

    public function load(): bool {
        if (!$this->_isAllowedFileExtension()){
            throw new \Exception('not allowed file extension');
        }
        if (!$this->_isFileExist()){
            throw new \Exception('file not exist');
        }

        $newFileName = $this->_createUniqueFileName();
        $newFile = $_SERVER['DOCUMENT_ROOT'].'/'.$this->filesStorageDir.'/'.$newFileName;
        $this->name = $newFileName;
        return move_uploaded_file($this->tmpName, $newFile);
    }

    public function getName(): string {
        return $this->name;
    }

    private function _isFileExist(): bool {
        return file_exists($this->tmpName);
    }

    private function _isAllowedFileExtension(): bool {
        return in_array($this->extension, $this->allowedFileExtension);
    }

    private function _createUniqueFileName(): string {
        return md5(microtime().$this->name).'.'.$this->extension;
    }

    private function _getFileExtension(): string {
        $fileParams = explode('.', $this->name);
        return array_pop($fileParams);
    }

}