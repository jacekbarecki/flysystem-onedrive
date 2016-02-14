# Flysystem OneDrive adapter

[![Author](https://img.shields.io/badge/author-%40jacekbarecki-brightgreen.svg)](https://twitter.com/JacekBarecki)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

The OneDrive adapter gives the possibility to use the [Flysystem](https://github.com/thephpleague/flysystem) filesystem abstraction library with OneDrive. 
It uses the Guzzle library to communicate with the API. 

## Installation

```bash
composer require jacekbarecki/flysystem-onedrive
```

## Usage

To communicate with the OneDrive API, you will need an authorization token. For the development purposes, visit 
https://dev.onedrive.com/auth/msa_oauth.htm, click "Get token" and paste the token into your PHP app.   
See the [OneDrive API documentation](https://dev.onedrive.com) for a detailed information about other ways of authorization.

~~~ php
require 'vendor/autoload.php';

//paste a temporary token from https://dev.onedrive.com/auth/msa_oauth.htm
$token = '123456789';

$oneDriveClient = new \JacekBarecki\FlysystemOneDrive\Client\OneDriveClient($token, new \GuzzleHttp\Client());
$oneDriveAdapter = new \JacekBarecki\FlysystemOneDrive\Adapter\OneDriveAdapter($oneDriveClient);
~~~



## Known limitations

The OneDrive adapter has currently some limitations. If you want to contribute to the development of the adapter, feel free to submit 
pull requests that remove these limitations:

1. Saving files is currently supported by the ["Simple upload"](https://dev.onedrive.com/items/upload_put.htm) method of the OneDrive API.
This method only supports files up to 100MB in size and is implemented without stream support. 
To support larger files, a [resumable upload method](https://dev.onedrive.com/items/upload_large_files.htm) needs to be implemented.

2. When listing items and a collection has more than 200 items, only first 200 items will be returned. To support bigger collections, 
the client should make multiple API calls, as described in the [OneDrive API documentation](https://dev.onedrive.com/items/list.htm). 
This is not implemented yet.
 
## See also

Please note that the OneDrive API is [case insensitive](https://dev.onedrive.com/misc/case-sensitivity.htm). Read the OneDrive API
documentation to get to know the details.