<?php

namespace JacekBarecki\FlysystemOneDrive\Adapter;

/**
 * A helper class holding possible properties that can be returned by a flysystem adapter.
 *
 * @link http://flysystem.thephpleague.com/creating-an-adapter/
 */
class FlysystemMetadata
{
    const TYPE_FILE = 'file';

    const TYPE_DIRECTORY = 'dir';

    const VISIBILITY_PUBLIC = 'public';

    const VISIBILITY_PRIVATE = 'private';

    /**
     * @var string 'file' or 'dir'
     */
    public $type;

    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    public $contents;

    /**
     * @var resource
     */
    public $stream;

    /**
     * @var string 'public' or 'private'
     */
    public $visibility;

    /**
     * @var int
     */
    public $timestamp;

    /**
     * @var string
     */
    public $mimetype;

    /**
     * @var int
     */
    public $size;

    /**
     * @param string $type
     * @param string $path
     */
    public function __construct($type, $path)
    {
        $this->type = $type;
        $this->path = $path;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = get_object_vars($this);
        $result = array_filter($result);

        return $result;
    }
}
