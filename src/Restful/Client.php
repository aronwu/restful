<?php
namespace Restful;

class Client
{
    public $url = '';

    /**
     * which data format posted to restful server
     * json: post json format
     * urlencode: post x-www-form-urlencoded
     * @var string
     */
    protected $format = 'json';

    protected $headers = [];

    protected $debug = false;
    /**
     * restufl server host url config
     *  [
     *  'order' => 'http://xxx.com/v2',
     *  'product' => 'http://xxx.com/v2'
     *
     *  ]
     * @var array
     */
    protected static $hostalias = [];
    protected static $timeout = 50;
    protected static $basicAuth = '';
    public function __construct()
    {

    }

    public function get($data = [])
    {
        return self::invoke('GET', $this->url, $data, $this->format, $this->headers, $this->debug);
    }

    public function post($data = [])
    {
        return self::invoke('POST', $this->url, $data, $this->format, $this->headers, $this->debug);
    }

    public function put($data = [])
    {
        return self::invoke('PUT', $this->url, $data, $this->format, $this->headers, $this->debug);
    }

    public function delete($data = [])
    {
        return self::invoke('DELETE', $this->url, $data, $this->format, $this->headers, $this->debug);
    }

    public function patch($data = [])
    {
        return self::invoke('PATCH', $this->url, $data, $this->format, $this->headers, $this->debug);
    }

    public function header($header)
    {
        $this->headers[] = $header;
        return $this;
    }

    public function path($path)
    {
        $len = strlen($path);
        if ($len == 0) {
            return $this;
        }
        if ($path[0] == '/') {
            $path = substr($path, 1);
            $len--;
        }
        if ($path[$len - 1] == '/') {
            $path = substr($path, 0, $len - 1);
        }
        if (!empty($path)) {
            $this->url .= '/' . $path;
        }

        return $this;
    }

    public function debug($debug)
    {
        $this->debug = $debug ? true : false;
        return $this;
    }

    public function format($postformat)
    {
        if (in_array(strtolower($postformat), ['json', 'urlencode'])) {
            $this->format = $postformat;
        }
        return $this;
    }

    public static function host($host)
    {
        $instance = new self;
        $host = trim($host);
        if (strtolower(substr($host, 0, 7)) == 'http://' ||
            strtolower(substr($host, 0, 8)) == 'https://') {
            $instance->url = $host;
        } else {
            $instance->url = $host . ":";
        }
        return $instance;
    }

    public function __call($method, $arguments)
    {
        $this->url .= '/' . $method;
        if ($arguments) {
            $this->url .= '/' . $arguments[0];
        }
        return $this;
    }

    public static function __callStatic($method, $arguments)
    {
        $instance = new self;
        call_user_func_array([$instance, $method], $arguments);
        return $instance;
    }

    public static function hostalias($hostalias = [])
    {
        self::$hostalias = $hostalias;

    }

    public static function auth($username, $token)
    {
        self::$basicAuth = "Basic " . base64_encode("$username:$token");
    }

    protected static function invoke($method, $uri, $data = [], $format = 'json', $headers = [], $debug = false)
    {
        $method = strtoupper($method);
        //check uri whether existed replaced parameter
        if (strpos($uri, '{')) {
            if (preg_match_all("/{([^\/]+)}/", $uri, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    if (!isset($data[$match[1]])) {
                        throw new Exception("can't find \{$match[1]\} in path");
                    } elseif (!$data[$match[1]]) {
                        throw new Exception(" \{$match[1]\} in path value is empty");
                    }
                    $uri = str_replace('{' . $match[1] . '}', urlencode($data[$match[1]]), $uri);
                    if ($method == 'GET') {
                        unset($data[$match[1]]);
                    }
                }
            }
        }
        @list($host, $path) = explode(":", $uri, 2);
        if (strtoupper($host) != 'HTTP' && strtoupper($host) != 'HTTPS') {
            if (!isset(self::$hostalias[$host])) {
                throw new Exception("can't find url host: $host");
            } else {
                $host = self::$hostalias[$host];
            }
        } else {
            $host .= ":";
        }
        $uri = $host . $path;
        if ($method == 'GET' && !empty($data)) {
            $uri .= (stripos($uri, '?') ? "&" : "?") . http_build_query($data);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        if ($debug) {
            curl_setopt($ch, CURLOPT_VERBOSE, true);
        }
        if ($method != 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POST, 1);
            if ($format == 'json') {
                $content = json_encode($data);
                $headers[] = 'Content-Type: application/json';
            } else {
                $content = http_build_query($data);
                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
            $headers[] = 'Content-Length:' . strlen($content);
        }
        if (self::$basicAuth) {
            $headers[] = 'Authorization: ' . self::$basicAuth;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::$timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($errno > 0) {
            throw new Exception($errmsg, $errno);
        }
        return $result;
    }
}
