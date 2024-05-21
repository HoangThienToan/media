<?php

namespace Edu2work\Media;

// use edu2work\media\Exceptions\CacheException;
// use edu2work\media\Exceptions\NotFoundException;
// use edu2work\media\Exceptions\ValidationException;
use Edu2work\Media\Http\Client as HttpClient;
use Edu2work\Media\Config;
use Illuminate\Cache\CacheManager;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;

class Client
{
    protected $key;

    /**
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    protected $apiUrl = '';


    protected $client;

    

    /**
     * Seconds to remeber authorization
     * @var int
     */
    public $authorizationCacheTime = 60;

    /**
     * Client constructor. Accepts the account ID, application key and an optional array of options.
     * @param string $key
     * @param array $options
     * @throws CacheException
     */
    public function __construct(string $key,  array $options = [])
    {   
        $config = new Config;
        $this->key = $config->EduKey();
        
        if ($key) {
            throw new \Exception('Please provide "key"');
        }


        if (isset($options['client'])) {
            $this->client = $options['client'];
        } else {
            $this->client = new HttpClient(['exceptions' => false]);
        }

        // initialize cache
        // $this->createCacheContainer();
    }



    private function createCacheContainer()
    {
        $container = new Container();
        $container['config'] = [
            'cache.default' => 'file',
            'cache.stores.file' => [
                'driver' => 'file',
                'path' => __DIR__ . '/Cache',
            ],
        ];
        $container['files'] = new Filesystem;

        try {
            $cacheManager = new CacheManager($container);
            $this->cache = $cacheManager->store();
        } catch (\Exception $e) {
            throw new CacheException(
                $e->getMessage()
            );
        }
    }

    public function postFile ($filename, $url ,$info) {
        $action = "post_media_function";
        $request_args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'action' => $action,
                'domain' => $_SERVER['HTTP_HOST'],
                'filename' => $filename,
                'url' => $url,
                'info' => $info
            )),
        );
        return $this->request('POST', $request_args);
    }
    /**
     * Add img tag with other attributes.
     * @param string $url
     * @param array $other
     * @param bool $lazy
     * @param bool $del
     * @throws CacheException
     */
    public static function img($url, array $other, $lazy = false, $del = false)
    {
        $attributes = '';
        foreach ($other as $key => $value) {
            $attributes .= $key . '="' . $value . '" ';
        }

        if ($lazy) {
            $attributes .= 'loading="lazy" ';
        }

        $imgTag = '<img src="' . $url . '" ' . $attributes . '/>';

        return $imgTag;
    }

    /**
     * Add video tag with other attributes.
     * @param string $url
     * @param array $other
     * @param bool $autoplay
     * @param bool $controls
     * @param bool $del
     * @throws CacheException
     */
    public static function video($url, array $other, $autoplay = false, $controls =true, $del = false)
    {
        $attributes = '';
        foreach ($other as $key => $value) {
            $attributes .= $key . '="' . $value . '" ';
        }

        if ($autoplay) {
            $attributes .= 'autoplay ';
        }

        if ($controls) {
            $attributes .= 'controls ';
        }

        $videoTag = '<video src="' . $url . '" ' . $attributes . '></video>';

        return $videoTag;
    }

    /**
     * Add video tag with other attributes.
     * @param string $url
     * @param array $other
     * @param bool $autoplay
     * @param bool $controls
     * @param bool $del
     * @throws CacheException
     */
    public static function audio($url, array $other, $autoplay =false, $controls =true, $del = true)
    {
        $attributes = '';
        foreach ($other as $key => $value) {
            $attributes .= $key . '="' . $value . '" ';
        }

        if ($autoplay) {
            $attributes .= 'autoplay ';
        }

        if ($controls) {
            $attributes .= 'controls ';
        }

        $audioTag = '<audio src="' . $url . '" ' . $attributes . '></audio>';

        return $audioTag;
    }

    // public static function iframe($url, array $other, $width = '100%', $height = '100%')
    // {
    //     $attributes = '';
    //     foreach ($other as $key => $value) {
    //         $attributes .= $key . '="' . $value . '" ';
    //     }

    //     $iframeTag = '<iframe src="' . $url . '" width="' . $width . '" height="' . $height . '" ' . $attributes . '></iframe>';

    //     return $iframeTag;
    // }
    /**
     * Wrapper for $this->client->request
     * @param string $method
     * @param string $uri
     * @param array $options
     * @param bool $asJson
     * @param bool $wantsGetContents
     * @return mixed|string
     */
    protected function request($method, array $options = [], $asJson = true)
    {
        $headers = [];
        $uri = "api.edu2work.com";
        // Add Authorization key if defined
        if ($this->key) {
            $headers['key'] = $this->key;
        }

        $options = array_replace_recursive([
            'headers' => $headers,
        ], $options);

        $fullUri = $uri;

        if (substr($uri, 0, 8) !== 'https://') {
            $fullUri = $this->apiUrl . $uri;
        }

        $response = $this->client->request($method, $fullUri, $options);
        if ($asJson) {
            return json_decode($response->getBody(), true);
        }

        return $response->getBody();
    }
}
