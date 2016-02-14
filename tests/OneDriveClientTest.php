<?php

namespace JacekBarecki\FlysystemOneDrive\Tests;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use JacekBarecki\FlysystemOneDrive\Client\OneDriveClient;

class OneDriveClientTest extends \PHPUnit_Framework_TestCase
{
    public function testDownload()
    {
        $client = $this->getMockBuilder('\GuzzleHttp\Client')->getMock();

        $accessToken = '123456789';
        $content = 'downloaded file content';

        $assertRequest = function ($request) use ($accessToken) {
                return ($request instanceof Request)
                && ($request->getMethod() == 'GET')
                && ($request->getUri()->getHost() == 'api.onedrive.com')
                && ($request->getUri()->getPath() == '/v1.0/drive/root:/some%20pdf%20document.pdf')
                && ($request->getHeader('authorization') == ['bearer '.$accessToken]);
            };
        $response = $this->getResponseWithFileMetadata();

        $assertRequestFileDownload = function ($request) use ($accessToken) {
                return ($request instanceof Request)
                && ($request->getMethod() == 'GET')
                && ($request->getUri()->getHost() == 'example.org')
                && ($request->getUri()->getPath() == '/some/download/url')
                && ($request->getHeader('authorization') == ['bearer '.$accessToken]);
            };
        $responseFileDownload = new Response('200', [], $content);

        $client->expects($this->exactly(2))
                ->method('send')
                ->withConsecutive([$this->callback($assertRequest)], [$this->callback($assertRequestFileDownload)])
                ->willReturn($response, $responseFileDownload);

        $oneDriveClient = new OneDriveClient($accessToken, $client);
        $response = $oneDriveClient->download('some pdf document.pdf');
        $this->assertEquals($content, (string) $response->getBody());
    }

    public function testDownloadStream()
    {
        $client = $this->getMockBuilder('\GuzzleHttp\Client')->getMock();

        $accessToken = '123456789';

        $assertRequest = function ($request) use ($accessToken) {
                return ($request instanceof Request)
                && ($request->getMethod() == 'GET')
                && ($request->getUri()->getHost() == 'api.onedrive.com')
                && ($request->getUri()->getPath() == '/v1.0/drive/root:/some%20document.txt')
                && ($request->getHeader('authorization') == ['bearer '.$accessToken]);
            };
        $response = $this->getResponseWithFileMetadata();

        $content = 'test stream content';
        $resource = tmpfile();
        fwrite($resource, $content);
        rewind($resource);

        $oneDriveClient = $this->getMockBuilder('\JacekBarecki\FlysystemOneDrive\Client\OneDriveClient')->setMethods(['getStreamForUrl'])->setConstructorArgs([$accessToken, $client])->getMock();
        $oneDriveClient->method('getStreamForUrl')->willReturn(new Stream($resource));

        $client->expects($this->exactly(1))
                ->method('send')
                ->with($this->callback($assertRequest))
                ->willReturn($response);

            /* @var OneDriveClient $oneDriveClient */
            $response = $oneDriveClient->downloadStream('some document.txt');

        $this->assertInternalType('resource', $response);

        $responseContent = stream_get_contents($response);
        $this->assertEquals($content, $responseContent);
    }

    public function testGetMetadata()
    {
        $client = $this->getMockBuilder('\GuzzleHttp\Client')->getMock();

        $accessToken = '123456789';
        $assertRequest = function ($request) use ($accessToken) {
                return ($request instanceof Request)
                && ($request->getMethod() == 'GET')
                && ($request->getUri()->getHost() == 'api.onedrive.com')
                && ($request->getUri()->getPath() == '/v1.0/drive/root:/some%20pdf%20document.pdf')
                && ($request->getHeader('authorization') == ['bearer '.$accessToken])
                && ((string) $request->getBody() == '');
            };

        $response = new Response(200, [], 'test response');
        $client->expects($this->once())->method('send')->with($this->callback($assertRequest))->willReturn($response);

        $oneDriveClient = new OneDriveClient($accessToken, $client);
        $result = $oneDriveClient->getMetadata('some pdf document.pdf');
        $this->assertEquals($response, $result);
    }

    public function testCreateFile()
    {
        $client = $this->getMockBuilder('\GuzzleHttp\Client')->getMock();

        $accessToken = '123456789';
        $content = 'new file content';
        $assertRequest = function ($request) use ($accessToken, $content) {
                return ($request instanceof Request)
                && ($request->getMethod() == 'PUT')
                && ($request->getUri()->getHost() == 'api.onedrive.com')
                && ($request->getUri()->getPath() == '/v1.0/drive/root:/new%20file.txt:/content')
                && ($request->getUri()->getQuery() == '@name.conflictBehavior=fail')
                && ($request->getHeader('authorization') == ['bearer '.$accessToken])
                && ((string) $request->getBody() == $content);
            };

        $response = new Response(200, [], 'test response');
        $client->expects($this->once())->method('send')->with($this->callback($assertRequest))->willReturn($response);

        $oneDriveClient = new OneDriveClient($accessToken, $client);
        $result = $oneDriveClient->createFile('new file.txt', $content);
        $this->assertEquals($response, $result);
    }

    public function testUpdateFile()
    {
        $client = $this->getMockBuilder('\GuzzleHttp\Client')->getMock();

        $accessToken = '123456789';
        $content = 'updated file content';
        $assertRequest = function ($request) use ($accessToken, $content) {
                return ($request instanceof Request)
                && ($request->getMethod() == 'PUT')
                && ($request->getUri()->getHost() == 'api.onedrive.com')
                && ($request->getUri()->getPath() == '/v1.0/drive/root:/updated%20file.txt:/content')
                && ($request->getUri()->getQuery() == '@name.conflictBehavior=replace')
                && ($request->getHeader('authorization') == ['bearer '.$accessToken])
                && ((string) $request->getBody() == $content);
            };

        $response = new Response(200, [], 'test response');
        $client->expects($this->once())->method('send')->with($this->callback($assertRequest))->willReturn($response);

        $oneDriveClient = new OneDriveClient($accessToken, $client);
        $result = $oneDriveClient->updateFile('updated file.txt', $content);
        $this->assertEquals($response, $result);
    }

    public function testRename()
    {
        $client = $this->getMockBuilder('\GuzzleHttp\Client')->getMock();

        $accessToken = '123456789';
        $content = json_encode(['name' => 'new filename.txt', 'parentReference' => ['path' => '/drive/root:/some folder']]);
        $assertRequest = function ($request) use ($accessToken, $content) {
                return ($request instanceof Request)
                && ($request->getMethod() == 'PATCH')
                && ($request->getUri()->getHost() == 'api.onedrive.com')
                && ($request->getUri()->getPath() == '/v1.0/drive/root:/old%20filename.txt')
                && ($request->getHeader('authorization') == ['bearer '.$accessToken])
                && ($request->getHeader('content-type') == ['application/json'])
                && ((string) $request->getBody() == $content);
            };

        $response = new Response(200, [], 'test response');
        $client->expects($this->once())->method('send')->with($this->callback($assertRequest))->willReturn($response);

        $oneDriveClient = new OneDriveClient($accessToken, $client);
        $result = $oneDriveClient->rename('old filename.txt', 'some folder/new filename.txt');
        $this->assertEquals($response, $result);
    }

        /**
         * @group copy
         */
        public function testCopy()
        {
            $client = $this->getMockBuilder('\GuzzleHttp\Client')->getMock();

            $accessToken = '123456789';

            $assertRequestCopy = function ($request) use ($accessToken) {
                return ($request instanceof Request)
                && ($request->getMethod() == 'POST')
                && ($request->getUri()->getHost() == 'api.onedrive.com')
                && ($request->getUri()->getPath() == '/v1.0/drive/root:/source.pdf:/action.copy')
                && ($request->getHeader('authorization') == ['bearer '.$accessToken]
                && ($request->getHeader('content-type') == ['application/json'])
                && ($request->getHeader('prefer') == ['respond-async'])
                );
            };
            $responseCopy = new Response(202, ['Location' => 'http://example.org/monitor/some_url']);

            $assertRequestMonitor = function ($request) use ($accessToken) {
                return ($request instanceof Request)
                && ($request->getMethod() == 'GET')
                && ($request->getUri()->getHost() == 'example.org')
                && ($request->getUri()->getPath() == '/monitor/some_url')
                && ($request->getHeader('authorization') == ['bearer '.$accessToken]);
            };

            $responseMonitor = new Response(303);

            $assertRequestMetadata = function ($request) use ($accessToken) {
                return ($request instanceof Request)
                && ($request->getMethod() == 'GET')
                && ($request->getUri()->getHost() == 'api.onedrive.com')
                && ($request->getUri()->getPath() == '/v1.0/drive/root:/folder/destination.pdf')
                && ($request->getHeader('authorization') == ['bearer '.$accessToken])
                && ((string) $request->getBody() == '');
            };
            $responseMetadata = $this->getResponseWithFileMetadata();

            $client->expects($this->exactly(3))
                ->method('send')
                ->withConsecutive([$this->callback($assertRequestCopy)], [$this->callback($assertRequestMonitor)], [$this->callback($assertRequestMetadata)])
                ->willReturn($responseCopy, $responseMonitor, $responseMetadata);

            $oneDriveClient = new OneDriveClient($accessToken, $client);
            $response = $oneDriveClient->copy('source.pdf', 'folder/destination.pdf');
            $this->assertEquals($responseMetadata, $response);
        }

    public function testUpdateMetadata()
    {
        $client = $this->getMockBuilder('\GuzzleHttp\Client')->getMock();

        $accessToken = '123456789';
        $content = ['name' => 'changed name.txt'];
        $assertRequest = function ($request) use ($accessToken, $content) {
                return ($request instanceof Request)
                && ($request->getMethod() == 'PATCH')
                && ($request->getUri()->getHost() == 'api.onedrive.com')
                && ($request->getUri()->getPath() == '/v1.0/drive/root:/old%20filename.txt')
                && ($request->getHeader('authorization') == ['bearer '.$accessToken])
                && ($request->getHeader('content-type') == ['application/json'])
                && ((string) $request->getBody() == json_encode($content));
            };

        $response = new Response(200, [], 'test response');
        $client->expects($this->once())->method('send')->with($this->callback($assertRequest))->willReturn($response);

        $oneDriveClient = new OneDriveClient($accessToken, $client);
        $result = $oneDriveClient->updateMetadata('old filename.txt', $content);
        $this->assertEquals($response, $result);
    }

    public function testCreateFolder()
    {
        $client = $this->getMockBuilder('\GuzzleHttp\Client')->getMock();

        $accessToken = '123456789';
        $content = json_encode(['name' => 'pictures', 'folder' => new \StdClass(), '@name.conflictBehavior' => 'fail']);
        $assertRequest = function ($request) use ($accessToken, $content) {
                return ($request instanceof Request)
                && ($request->getMethod() == 'POST')
                && ($request->getUri()->getHost() == 'api.onedrive.com')
                && ($request->getUri()->getPath() == '/v1.0/drive/root:/documents:/children')
                && ($request->getHeader('authorization') == ['bearer '.$accessToken])
                && ($request->getHeader('content-type') == ['application/json'])
                && ((string) $request->getBody() == $content);
            };

        $response = new Response(200, [], 'test response');
        $client->expects($this->once())->method('send')->with($this->callback($assertRequest))->willReturn($response);

        $oneDriveClient = new OneDriveClient($accessToken, $client);
        $result = $oneDriveClient->createFolder('documents/pictures');
        $this->assertEquals($response, $result);
    }

    public function testListChildren()
    {
        $client = $this->getMockBuilder('\GuzzleHttp\Client')->getMock();

        $accessToken = '123456789';
        $assertRequest = function ($request) use ($accessToken) {
                return ($request instanceof Request)
                && ($request->getMethod() == 'GET')
                && ($request->getUri()->getHost() == 'api.onedrive.com')
                && ($request->getUri()->getPath() == '/v1.0/drive/root:/documents/pictures:/children')
                && ($request->getHeader('authorization') == ['bearer '.$accessToken]);
            };

        $response = new Response(200, [], 'test response');
        $client->expects($this->once())->method('send')->with($this->callback($assertRequest))->willReturn($response);

        $oneDriveClient = new OneDriveClient($accessToken, $client);
        $result = $oneDriveClient->listChildren('documents/pictures');
        $this->assertEquals($response, $result);
    }

    public function testDelete()
    {
        $client = $this->getMockBuilder('\GuzzleHttp\Client')->getMock();

        $accessToken = '123456789';
        $assertRequest = function ($request) use ($accessToken) {
                return ($request instanceof Request)
                && ($request->getMethod() == 'DELETE')
                && ($request->getUri()->getHost() == 'api.onedrive.com')
                && ($request->getUri()->getPath() == '/v1.0/drive/root:/documents/pictures/some%20picture.jpg')
                && ($request->getHeader('authorization') == ['bearer '.$accessToken]);
            };

        $response = new Response(204, [], 'successful response');
        $client->expects($this->once())->method('send')->with($this->callback($assertRequest))->willReturn($response);

        $oneDriveClient = new OneDriveClient($accessToken, $client);
        $result = $oneDriveClient->delete('documents/pictures/some picture.jpg');
        $this->assertSame(true, $result);
    }

    public function testDeleteWithUnsuccessfulResponse()
    {
        $client = $this->getMockBuilder('\GuzzleHttp\Client')->getMock();

        $accessToken = '123456789';

        $response = new Response(400);
        $client->expects($this->once())->method('send')->willReturn($response);

        $oneDriveClient = new OneDriveClient($accessToken, $client);
        $result = $oneDriveClient->delete('documents/pictures/some picture.jpg');
        $this->assertSame(false, $result);
    }

    public function testItemExists()
    {
        $client = $this->getMockBuilder('\GuzzleHttp\Client')->getMock();

        $accessToken = '123456789';
        $assertRequest = function ($request) use ($accessToken) {
                return ($request instanceof Request)
                && ($request->getMethod() == 'GET')
                && ($request->getUri()->getHost() == 'api.onedrive.com')
                && ($request->getUri()->getPath() == '/v1.0/drive/root:/some%20file.txt')
                && ($request->getHeader('authorization') == ['bearer '.$accessToken]);
            };

        $response = new Response(200, [], 'test response');
        $client->expects($this->once())->method('send')->with($this->callback($assertRequest))->willReturn($response);

        $oneDriveClient = new OneDriveClient($accessToken, $client);
        $result = $oneDriveClient->itemExists('some file.txt');
        $this->assertSame(true, $result);
    }

    public function testItemExistsWhenItDoesntExist()
    {
        $client = $this->getMockBuilder('\GuzzleHttp\Client')->getMock();

        $accessToken = '123456789';

        $exception = new ClientException('Not found', new Request('GET', 'uri'), new Response(404));
        $client->expects($this->once())->method('send')->willThrowException($exception);

        $oneDriveClient = new OneDriveClient($accessToken, $client);
        $result = $oneDriveClient->itemExists('some file.txt');
        $this->assertSame(false, $result);
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
}
