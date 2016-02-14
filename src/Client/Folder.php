<?php

namespace JacekBarecki\FlysystemOneDrive\Client;

/**
 * OneDrive Folder item.
 *
 * @link https://dev.onedrive.com/items/create.htm
 */
class Folder implements \JsonSerializable
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var \StdClass
     */
    public $folder;

    /**
     * @var string rename|replace|fail
     */
    public $conflictBehavior = 'fail';

    public function __construct()
    {
        $this->folder = new \StdClass();
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'folder' => $this->folder,
            '@name.conflictBehavior' => $this->conflictBehavior,
        ];
    }
}
