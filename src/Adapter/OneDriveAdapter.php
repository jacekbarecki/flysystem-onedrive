<?php

namespace JacekBarecki\FlysystemOneDrive\Adapter;

use JacekBarecki\FlysystemOneDrive\Client\OneDriveClient;
use League\Flysystem\Adapter\Polyfill\NotSupportingVisibilityTrait;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;

class OneDriveAdapter implements AdapterInterface
{
    use NotSupportingVisibilityTrait;

    /**
     * @var OneDriveClient
     */
    private $client;

    /**
     * @param OneDriveClient $client
     */
    public function __construct(OneDriveClient $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, Config $config)
    {
        $response = $this->client->createFile($path, $contents);
        $responseContent = json_decode((string) $response->getBody());

        $result = new FlysystemMetadata(FlysystemMetadata::TYPE_FILE, $path);
        $this->updateFlysystemMetadataFromResponseContent($result, $responseContent);

        return $result->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, Config $config)
    {
        return $this->write($path, stream_get_contents($resource), $config);
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, Config $config)
    {
        $response = $this->client->updateFile($path, $contents);
        $responseContent = json_decode((string) $response->getBody());

        $result = new FlysystemMetadata(FlysystemMetadata::TYPE_FILE, $path);
        $this->updateFlysystemMetadataFromResponseContent($result, $responseContent);

        return $result->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, Config $config)
    {
        return $this->update($path, stream_get_contents($resource), $config);
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newpath)
    {
        $response = $this->client->rename($path, $newpath);
        $responseContent = json_decode((string) $response->getBody());

        $result = new FlysystemMetadata(FlysystemMetadata::TYPE_FILE, $newpath);
        $this->updateFlysystemMetadataFromResponseContent($result, $responseContent);

        return $result->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function copy($path, $newpath)
    {
        $response = $this->client->copy($path, $newpath);
        $responseContent = json_decode((string) $response->getBody());

        $flysystemMetadata = new FlysystemMetadata(FlysystemMetadata::TYPE_FILE, $newpath);
        $this->updateFlysystemMetadataFromResponseContent($flysystemMetadata, $responseContent);

        return $flysystemMetadata->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        return $this->client->delete($path);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname)
    {
        return $this->client->delete($dirname);
    }

    /**
     * {@inheritdoc}
     */
    public function createDir($dirname, Config $config)
    {
        $response = $this->client->createFolder($dirname);
        $responseContent = json_decode((string) $response->getBody());

        $result = new FlysystemMetadata(FlysystemMetadata::TYPE_DIRECTORY, $dirname);
        $this->updateFlysystemMetadataFromResponseContent($result, $responseContent);

        return $result->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        return $this->client->itemExists($path);
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        $response = $this->client->download($path);

        $result = new FlysystemMetadata(FlysystemMetadata::TYPE_FILE, $path);
        $result->contents = (string) $response->getBody();

        return $result->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        $response = $this->client->downloadStream($path);

        $result = new FlysystemMetadata(FlysystemMetadata::TYPE_FILE, $path);
        $result->stream = $response;

        return $result->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function listContents($directory = '', $recursive = false)
    {
        $response = $this->client->listChildren($directory);
        $responseContent = json_decode((string) $response->getBody());

        $items = $responseContent->value;

        $result = [];
        foreach ($items as $item) {
            $isFile = property_exists($item, 'file');
            $type = $isFile ? FlysystemMetadata::TYPE_FILE : FlysystemMetadata::TYPE_DIRECTORY;
            $path = $directory.'/'.$item->name;

            $flysystemMetadata = new FlysystemMetadata($type, $path);
            $this->updateFlysystemMetadataFromResponseContent($flysystemMetadata, $item);

            $result[] = $flysystemMetadata->toArray();

            if ($recursive && !$isFile) {
                $result = array_merge($result, $this->listContents($path, true));
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($path)
    {
        $response = $this->client->getMetadata($path);
        $responseContent = json_decode((string) $response->getBody());

        $flysystemMetadata = new FlysystemMetadata(FlysystemMetadata::TYPE_FILE, $path);
        $this->updateFlysystemMetadataFromResponseContent($flysystemMetadata, $responseContent);

        return $flysystemMetadata->toArray();
    }

    /**
     * @param FlysystemMetadata $flysystemMetadata
     * @param \StdClass         $responseContent
     *
     * @throws OneDriveAdapterException
     */
    private function updateFlysystemMetadataFromResponseContent(FlysystemMetadata $flysystemMetadata, \StdClass $responseContent)
    {
        $isFile = property_exists($responseContent, 'file');

        $flysystemMetadata->timestamp = $this->getLastModifiedTimestampFromResponse($responseContent);
        $flysystemMetadata->mimetype = $isFile ? $responseContent->file->mimeType : null;
        $flysystemMetadata->size = $isFile ? $responseContent->size : null;
    }

    /**
     * @param \StdClass $response
     *
     * @return null|int
     *
     * @throws OneDriveAdapterException
     */
    private function getLastModifiedTimestampFromResponse(\StdClass $response)
    {
        if (!property_exists($response, 'lastModifiedDateTime')) {
            return;
        }

        //date can be given with or without microseconds, try to parse from both formats
        $date = \DateTime::createFromFormat('Y-m-d\TH:i:s.uO', $response->lastModifiedDateTime, new \DateTimeZone('UTC'));
        if (!$date) {
            $date = \DateTime::createFromFormat('Y-m-d\TH:i:sO', $response->lastModifiedDateTime, new \DateTimeZone('UTC'));
        }

        if (!$date) {
            throw new OneDriveAdapterException('Incorrect last modified date returned from the API.');
        }

        return $date->getTimestamp();
    }
}
