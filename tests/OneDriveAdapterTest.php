<?php

namespace JacekBarecki\FlysystemOneDrive\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use JacekBarecki\FlysystemOneDrive\Adapter\OneDriveAdapter;
use League\Flysystem\Config;

class OneDriveAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function getOneDriveClient()
    {
        $mock = $this->getMockBuilder('\JacekBarecki\FlysystemOneDrive\Client\OneDriveClient')->setConstructorArgs(['123456789', new Client()])->getMock();
        $adapter = new OneDriveAdapter($mock);

        return [[$adapter, $mock]];
    }

    /**
     * @dataProvider getOneDriveClient
     *
     * @param OneDriveAdapter                          $adapter
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     */
    public function testWrite(OneDriveAdapter $adapter, \PHPUnit_Framework_MockObject_MockObject $mock)
    {
        $mock->method('createFile')->willReturn($this->getResponseWithFileMetadata());

        $result = $adapter->write('/some pdf document.pdf', '', new Config());
        $this->assertEquals('file', $result['type']);
        $this->assertEquals('/some pdf document.pdf', $result['path']);
        $this->assertEquals('application/pdf', $result['mimetype']);
        $this->assertEquals('526022', $result['size']);

        $expectedTimestamp = \DateTime::createFromFormat('Y-m-d\TH:i:s.uO', '2015-02-22T02:46:55.573Z',  new \DateTimeZone('UTC'));
        $this->assertEquals($expectedTimestamp->getTimestamp(), $result['timestamp']);
    }

    /**
     * @dataProvider getOneDriveClient
     *
     * @param OneDriveAdapter                          $adapter
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     */
    public function testWriteStream(OneDriveAdapter $adapter, \PHPUnit_Framework_MockObject_MockObject $mock)
    {
        $mock->method('createFile')->willReturn($this->getResponseWithFileMetadata());

        $result = $adapter->writeStream('/some pdf document.pdf', tmpfile(), new Config());
        $this->assertEquals('file', $result['type']);
        $this->assertEquals('/some pdf document.pdf', $result['path']);
        $this->assertEquals('application/pdf', $result['mimetype']);
        $this->assertEquals('526022', $result['size']);

        $expectedTimestamp = \DateTime::createFromFormat('Y-m-d\TH:i:s.uO', '2015-02-22T02:46:55.573Z',  new \DateTimeZone('UTC'));
        $this->assertEquals($expectedTimestamp->getTimestamp(), $result['timestamp']);
    }

    /**
     * @dataProvider getOneDriveClient
     *
     * @param OneDriveAdapter                          $adapter
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     */
    public function testUpdate(OneDriveAdapter $adapter, \PHPUnit_Framework_MockObject_MockObject $mock)
    {
        $mock->method('updateFile')->willReturn($this->getResponseWithFileMetadata());

        $result = $adapter->update('/some pdf document.pdf', tmpfile(), new Config());
        $this->assertEquals('file', $result['type']);
        $this->assertEquals('/some pdf document.pdf', $result['path']);
        $this->assertEquals('application/pdf', $result['mimetype']);
        $this->assertEquals('526022', $result['size']);

        $expectedTimestamp = \DateTime::createFromFormat('Y-m-d\TH:i:s.uO', '2015-02-22T02:46:55.573Z',  new \DateTimeZone('UTC'));
        $this->assertEquals($expectedTimestamp->getTimestamp(), $result['timestamp']);
    }

    /**
     * @dataProvider getOneDriveClient
     *
     * @param OneDriveAdapter                          $adapter
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     */
    public function testUpdateStream(OneDriveAdapter $adapter, \PHPUnit_Framework_MockObject_MockObject $mock)
    {
        $mock->method('updateFile')->willReturn($this->getResponseWithFileMetadata());

        $result = $adapter->update('/some pdf document.pdf', tmpfile(), new Config());
        $this->assertEquals('file', $result['type']);
        $this->assertEquals('/some pdf document.pdf', $result['path']);
        $this->assertEquals('application/pdf', $result['mimetype']);
        $this->assertEquals('526022', $result['size']);

        $expectedTimestamp = \DateTime::createFromFormat('Y-m-d\TH:i:s.uO', '2015-02-22T02:46:55.573Z',  new \DateTimeZone('UTC'));
        $this->assertEquals($expectedTimestamp->getTimestamp(), $result['timestamp']);
    }

    /**
     * @dataProvider getOneDriveClient
     *
     * @param OneDriveAdapter                          $adapter
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     */
    public function testRename(OneDriveAdapter $adapter, \PHPUnit_Framework_MockObject_MockObject $mock)
    {
        $mock->method('rename')->willReturn($this->getResponseWithFileMetadata());

        $result = $adapter->rename('/old path.pdf', '/some pdf document.pdf');
        $this->assertEquals('file', $result['type']);
        $this->assertEquals('/some pdf document.pdf', $result['path']);
        $this->assertEquals('application/pdf', $result['mimetype']);
        $this->assertEquals('526022', $result['size']);

        $expectedTimestamp = \DateTime::createFromFormat('Y-m-d\TH:i:s.uO', '2015-02-22T02:46:55.573Z',  new \DateTimeZone('UTC'));
        $this->assertEquals($expectedTimestamp->getTimestamp(), $result['timestamp']);
    }

    /**
     * @dataProvider getOneDriveClient
     *
     * @param OneDriveAdapter                          $adapter
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     */
    public function testCopy(OneDriveAdapter $adapter, \PHPUnit_Framework_MockObject_MockObject $mock)
    {
        $mock->method('copy')->willReturn($this->getResponseWithFileMetadata());

        $result = $adapter->copy('/old path.pdf', '/some pdf document.pdf');
        $this->assertEquals('file', $result['type']);
        $this->assertEquals('/some pdf document.pdf', $result['path']);
        $this->assertEquals('application/pdf', $result['mimetype']);
        $this->assertEquals('526022', $result['size']);

        $expectedTimestamp = \DateTime::createFromFormat('Y-m-d\TH:i:s.uO', '2015-02-22T02:46:55.573Z',  new \DateTimeZone('UTC'));
        $this->assertEquals($expectedTimestamp->getTimestamp(), $result['timestamp']);
    }

    /**
     * @dataProvider getOneDriveClient
     *
     * @param OneDriveAdapter                          $adapter
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     */
    public function testDelete(OneDriveAdapter $adapter, \PHPUnit_Framework_MockObject_MockObject $mock)
    {
        $mock->method('delete')->willReturn(true);

        $result = $adapter->delete('/some pdf document.pdf');
        $this->assertTrue($result);
    }

    /**
     * @dataProvider getOneDriveClient
     *
     * @param OneDriveAdapter                          $adapter
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     */
    public function testDeleteDir(OneDriveAdapter $adapter, \PHPUnit_Framework_MockObject_MockObject $mock)
    {
        $mock->method('delete')->willReturn(true);

        $result = $adapter->deleteDir('some dir');
        $this->assertTrue($result);
    }

    /**
     * @dataProvider getOneDriveClient
     *
     * @param OneDriveAdapter                          $adapter
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     */
    public function testCreateDir(OneDriveAdapter $adapter, \PHPUnit_Framework_MockObject_MockObject $mock)
    {
        $mock->method('createFolder')->willReturn($this->getResponseWithDirectoryMetadata());

        $result = $adapter->createDir('test-dir', new Config());
        $this->assertEquals('dir', $result['type']);
        $this->assertEquals('test-dir', $result['path']);

        $expectedTimestamp = \DateTime::createFromFormat('Y-m-d\TH:i:s.uO', '2016-02-14T15:23:28.15Z',  new \DateTimeZone('UTC'));
        $this->assertEquals($expectedTimestamp->getTimestamp(), $result['timestamp']);
    }

    /**
     * @dataProvider getOneDriveClient
     *
     * @param OneDriveAdapter                          $adapter
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     */
    public function testHas(OneDriveAdapter $adapter, \PHPUnit_Framework_MockObject_MockObject $mock)
    {
        $mock->method('itemExists')->willReturn(true);

        $result = $adapter->has('/some pdf document.pdf');
        $this->assertTrue($result);
    }

    /**
     * @dataProvider getOneDriveClient
     *
     * @param OneDriveAdapter                          $adapter
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     */
    public function testRead(OneDriveAdapter $adapter, \PHPUnit_Framework_MockObject_MockObject $mock)
    {
        $content = 'file content';
        $mock->method('download')->willReturn(new Response(200, [], $content));

        $result = $adapter->read('some file.txt');
        $this->assertEquals('file', $result['type']);
        $this->assertEquals('some file.txt', $result['path']);
        $this->assertEquals($content, $result['contents']);
    }

    /**
     * @dataProvider getOneDriveClient
     *
     * @param OneDriveAdapter                          $adapter
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     */
    public function testReadStream(OneDriveAdapter $adapter, \PHPUnit_Framework_MockObject_MockObject $mock)
    {
        $content = 'test stream content';
        $resource = tmpfile();
        fwrite($resource, $content);
        rewind($resource);

        $mock->method('downloadStream')->willReturn($resource);
        $result = $adapter->readStream('some file.txt');
        $this->assertEquals('file', $result['type']);
        $this->assertEquals('some file.txt', $result['path']);
        $this->assertInternalType('resource', $result['stream']);

        $this->assertEquals($content, stream_get_contents($result['stream']));
    }

    /**
     * @dataProvider getOneDriveClient
     *
     * @param OneDriveAdapter                          $adapter
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     */
    public function testListContents(OneDriveAdapter $adapter, \PHPUnit_Framework_MockObject_MockObject $mock)
    {
        $mock->method('listChildren')->willReturn($this->getResponseWithListChildren());

        $result = $adapter->listContents('/test-dir');
        $this->assertCount(3, $result);

        $this->assertEquals('file', $result[0]['type']);
        $this->assertEquals('/test-dir/file1.pdf', $result[0]['path']);
        $this->assertEquals('application/pdf', $result[0]['mimetype']);
        $this->assertEquals('1173148', $result[0]['size']);
        $expectedTimestamp = \DateTime::createFromFormat('Y-m-d\TH:i:s.uO', '2014-12-20T15:48:37.177Z',  new \DateTimeZone('UTC'));
        $this->assertEquals($expectedTimestamp->getTimestamp(), $result[0]['timestamp']);

        $this->assertEquals('file', $result[1]['type']);
        $this->assertEquals('/test-dir/file2.jpg', $result[1]['path']);
        $this->assertEquals('image/jpeg', $result[1]['mimetype']);
        $this->assertEquals('663552', $result[1]['size']);
        $expectedTimestamp = \DateTime::createFromFormat('Y-m-d\TH:i:s.uO', '2014-12-20T15:47:56.09Z',  new \DateTimeZone('UTC'));
        $this->assertEquals($expectedTimestamp->getTimestamp(), $result[1]['timestamp']);

        $this->assertEquals('file', $result[2]['type']);
        $this->assertEquals('/test-dir/file3.jpg', $result[2]['path']);
        $this->assertEquals('image/jpeg', $result[2]['mimetype']);
        $this->assertEquals('63170', $result[2]['size']);
        $expectedTimestamp = \DateTime::createFromFormat('Y-m-d\TH:i:s.uO', '2014-12-20T14:55:46.547Z',  new \DateTimeZone('UTC'));
        $this->assertEquals($expectedTimestamp->getTimestamp(), $result[2]['timestamp']);
    }

    /**
     * @dataProvider getOneDriveClient
     *
     * @param OneDriveAdapter                          $adapter
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     */
    public function testGetSize(OneDriveAdapter $adapter, \PHPUnit_Framework_MockObject_MockObject $mock)
    {
        $mock->method('getMetadata')->willReturn($this->getResponseWithFileMetadata());

        $result = $adapter->getSize('/some pdf document.pdf');
        $this->assertEquals('file', $result['type']);
        $this->assertEquals('/some pdf document.pdf', $result['path']);
        $this->assertEquals('application/pdf', $result['mimetype']);
        $this->assertEquals('526022', $result['size']);

        $expectedTimestamp = \DateTime::createFromFormat('Y-m-d\TH:i:s.uO', '2015-02-22T02:46:55.573Z',  new \DateTimeZone('UTC'));
        $this->assertEquals($expectedTimestamp->getTimestamp(), $result['timestamp']);
    }

    /**
     * @dataProvider getOneDriveClient
     *
     * @param OneDriveAdapter                          $adapter
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     */
    public function testGetMimetype(OneDriveAdapter $adapter, \PHPUnit_Framework_MockObject_MockObject $mock)
    {
        $mock->method('getMetadata')->willReturn($this->getResponseWithFileMetadata());

        $result = $adapter->getMimetype('/some pdf document.pdf');
        $this->assertEquals('file', $result['type']);
        $this->assertEquals('/some pdf document.pdf', $result['path']);
        $this->assertEquals('application/pdf', $result['mimetype']);
        $this->assertEquals('526022', $result['size']);

        $expectedTimestamp = \DateTime::createFromFormat('Y-m-d\TH:i:s.uO', '2015-02-22T02:46:55.573Z',  new \DateTimeZone('UTC'));
        $this->assertEquals($expectedTimestamp->getTimestamp(), $result['timestamp']);
    }

    /**
     * @dataProvider getOneDriveClient
     *
     * @param OneDriveAdapter                          $adapter
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     */
    public function testGetTimestamp(OneDriveAdapter $adapter, \PHPUnit_Framework_MockObject_MockObject $mock)
    {
        $mock->method('getMetadata')->willReturn($this->getResponseWithFileMetadata());

        $result = $adapter->getTimestamp('/some pdf document.pdf');
        $this->assertEquals('file', $result['type']);
        $this->assertEquals('/some pdf document.pdf', $result['path']);
        $this->assertEquals('application/pdf', $result['mimetype']);
        $this->assertEquals('526022', $result['size']);

        $expectedTimestamp = \DateTime::createFromFormat('Y-m-d\TH:i:s.uO', '2015-02-22T02:46:55.573Z',  new \DateTimeZone('UTC'));
        $this->assertEquals($expectedTimestamp->getTimestamp(), $result['timestamp']);
    }

    /**
     * @dataProvider getOneDriveClient
     *
     * @param OneDriveAdapter                          $adapter
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     */
    public function testGetMetadata(OneDriveAdapter $adapter, \PHPUnit_Framework_MockObject_MockObject $mock)
    {
        $mock->method('getMetadata')->willReturn($this->getResponseWithFileMetadata());

        $result = $adapter->getMetadata('/some pdf document.pdf');
        $this->assertEquals('file', $result['type']);
        $this->assertEquals('/some pdf document.pdf', $result['path']);
        $this->assertEquals('application/pdf', $result['mimetype']);
        $this->assertEquals('526022', $result['size']);

        $expectedTimestamp = \DateTime::createFromFormat('Y-m-d\TH:i:s.uO', '2015-02-22T02:46:55.573Z',  new \DateTimeZone('UTC'));
        $this->assertEquals($expectedTimestamp->getTimestamp(), $result['timestamp']);
    }

    /**
     * @return Response
     */
    private function getResponseWithFileMetadata()
    {
        $body = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'fileMetadata.json');
        $response = new Response(200, [], $body);

        return $response;
    }

    /**
     * @return Response
     */
    private function getResponseWithDirectoryMetadata()
    {
        $body = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'directoryMetadata.json');
        $response = new Response(200, [], $body);

        return $response;
    }

    /**
     * @return Response
     */
    private function getResponseWithListChildren()
    {
        $body = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'listChildren.json');
        $response = new Response(200, [], $body);

        return $response;
    }
}
