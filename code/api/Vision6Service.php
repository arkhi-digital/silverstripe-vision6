<?php

/**
 * Api wrapper class.
 *
 * @author Vision6
 * @author Reece Alexander <reece@steadlane.com.au>
 *
 * @note I cleaned this up and applied it to the SilverStripe framework
 */
class Vision6Api extends Object
{
    /** @var bool */
    protected $apiKey = false;

    /** @var string */
    protected $apirUrl = 'http://www.vision6.com.au/api/jsonrpcserver';

    /** @var string */
    protected $apiVersion = '';

    /** @var bool */
    protected $errorCode = false;

    /** @var bool */
    protected $errorMessage = false;

    /** @var int */
    protected $requestId = 1;

    /** @var int */
    protected $timeout = 300;

    /** @var array */
    protected $headers = array();

    /** @var bool */
    protected $debug = false;

    /**
     * Constructor
     *
     * The hostname used to log into the API is the same hostname which appears in your browsers address bar
     * after you log into the system.
     *
     * Please refer to the documentation for instructions on how to generate an API key.
     *
     * @param string $version The version of the API to use for all requests.
     */
    public function __construct($version = '3.0')
    {
        if (!defined('VISION6_API_KEY')) {
            user_error('Vision6 API key has not been defined as constant!', E_USER_ERROR);
        }

        $this->setAPIKey(VISION6_API_KEY);
        $this->setApiVersion($version);

        parent::__construct();
    }

    /**
     * Call an API method.
     *
     * @param  string $methodName Name of the API method to call.
     *
     * @return mixed    Method result.
     */
    public function callMethod($methodName /*, parameters ...*/)
    {
        $parameters = func_get_args();

        return $this->invokeMethod($this->apiKey, array_shift($parameters), $parameters);
    }

    /**
     * Call an API method.
     *
     * @param   string $methodName Name of the API method to call.
     * @param   array $parameters An array of parameters to pass to the method.
     *
     * @return  mixed   Method result.
     */
    public function __call($methodName, $parameters)
    {
        return $this->invokeMethod($this->apiKey, $methodName, $parameters);
    }


    /**
     * Set the API endpoint URL.
     *
     * @param   string $url The endpoint URL. Usually "http://<your_login_hostname>/api/jsonrpcserver".
     *
     * @return  void
     */
    public function setApiUrl($url)
    {
        // Add API version to url
        $parts = @parse_url($url);
        if ($parts) {
            $url = (isset($parts['scheme']) ? $parts['scheme'] : 'http') . '://';
            if (isset($parts['host'])) {
                $url .= $parts['host'];
            }
            if (isset($parts['port'])) {
                $url .= ':' . $parts['port'];
            }
            $url .= (isset($parts['path']) ? $parts['path'] : '/');
            if (isset($parts['query'])) {
                parse_str($parts['query'], $query);
            }
            $query['version'] = $this->apiVersion;
            $url .= '?' . http_build_query($query);
        }

        $this->apirUrl = trim($url);
    }

    /**
     * Get the API endpoint URL.
     *
     * @return  string
     */
    public function getApiUrl()
    {
        return $this->apirUrl;
    }


    /**
     * Set the API key to use when making requests.
     *
     * @param   string $key The API key. Keep this secret.
     *
     * @return  void
     */
    public function setAPIKey($key)
    {
        $this->apiKey = $key;
    }

    /**
     * Get the API key used when making requests.
     *
     * @return  string
     */
    public function getAPIKey()
    {
        return $this->apiKey;
    }


    /**
     * Set the API version to use when making requests.
     *
     * @param   string $versionString The version of the API to use.
     *
     * @return  void
     */
    public function setApiVersion($versionString)
    {
        $this->apiVersion = $versionString;
        $this->setApiUrl($this->apirUrl); // update url
    }

    /**
     * Get the API version used when making requests.
     *
     * @return  string
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }


    /**
     * Set the connection timeout used when making requests.
     *
     * @param   int $seconds Timout in seconds.
     *
     * @return  void
     */
    public function setTimeout($seconds)
    {
        $this->timeout = $seconds;
    }

    /**
     * Get the connection timeout used when making requests.
     *
     * @return  int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }


    /**
     * Set additional HTTP headers to send when making requests.
     *
     * @param   array $headersArray An array of HTTP headers.
     *
     * @return  void
     */
    public function setHeaders($headersArray)
    {
        $this->headers = $headersArray;
    }

    /**
     * Get the array of additional HTTP headers.
     *
     * @return  array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Did the previous request return an error?
     *
     * @return bool
     */
    public function hasError()
    {
        return !($this->errorCode) ? false : true;
    }

    /**
     * Get the error code from the last method call.
     *
     * @return  int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Get the error message from the last method call.
     *
     * @return  string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }


    /**
     * Enable or disable request debugging.
     *
     * @param   bool $enabled Set to true to enable request debug output.
     *
     * @return  void
     */
    public function setDebug($enabled)
    {
        $this->debug = $enabled;
    }

    /**
     * Determine if request debugging is enabled.
     *
     * @return  bool
     */
    public function getDebug()
    {
        return $this->debug;
    }


    /**
     * Perform an API method request.
     *
     * @param   string $apiKey The API key to use for the request.
     * @param   string $methodName The name of the API method.
     * @param   array $parameters An array of parameters to pass to the method.
     *
     * @return  mixed   Returns the method result on success.
     */
    protected function invokeMethod($apiKey, $methodName, $parameters)
    {
        $this->errorCode = false;
        $this->errorMessage = false;

        if ($apiKey) {
            // API key is the first method parameter
            array_unshift($parameters, $apiKey);
        }

        // Encode the request
        $requestId = $this->requestId++;
        $request = array(
            'id' => $requestId,
            'method' => $methodName,
            'params' => $parameters
        );

        // Send the request and read the response
        $encodedResponse = $this->postRequest($methodName, json_encode($request));


        // Decode response
        $response = json_decode($encodedResponse, true);
        if ($response) {
            if (isset($response['result'])) {
                if ($methodName == 'login') { // deprecated, use API keys instead
                    $this->setApiUrl($response['result']);
                }

                return $response['result'];
            } elseif (isset($response['error'])) {
                $errorCode = (isset($response['error']['code']) ? $response['error']['code'] : '?');
                $errorMessage = (isset($response['error']['message']) ? $response['error']['message'] : 'Unknown Error');
                if (is_string($response['error'])) {
                    if (preg_match('/^(\d+)\-(.*)/', $response['error'], $matches)) {
                        $errorCode = intval($matches[1]);
                        $errorMessage = trim($matches[2]);
                    }
                }

                $this->error($methodName, $errorCode, $errorMessage);
                return false;
            }
        }

        $this->error($methodName, 6, 'Invalid server response');

        return false;
    }

    /**
     * Post an API method request.
     *
     * @param   string $methodName The name of the API method.
     * @param   string $postData The serialized method request.
     *
     * @return  mixed   Returns the serialized response on success.
     */
    protected function postRequest($methodName, $postData)
    {
        // Build request headers
        $headers = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData)
        );
        $headers = array_merge($headers, $this->headers);

        $context = stream_context_create(
            array(
                'http' => array(
                    'method' => 'POST',
                    'user_agent' => 'JSON-RPC PHP Wrapper',
                    'header' => $headers,
                    'content' => $postData,
                    'timeout' => $this->getTimeout(),
                )
            )
        );

        // Connect and send the request
        $fp = @fopen($this->getApiUrl(), 'rb', false, $context);
        if (!$fp) {
            $this->error($methodName, 2, 'Unable to connect to ' . $this->getApiUrl());
            return false;
        }

        // Read the response
        $response = stream_get_contents($fp);
        $metaData = stream_get_meta_data($fp);
        fclose($fp);

        if ($metaData['timed_out']) {
            $this->error($methodName, 3, 'Connection timed out');
            return false;
        }

        if ($response === false) {
            $this->error($methodName, 4, 'Error occurred while reading from socket');
        }

        $lastStatus = false;
        foreach ($metaData['wrapper_data'] as $line) {
            if (substr($line, 0, 5) == 'HTTP/') {
                $lastStatus = explode(' ', $line, 3);
            }
        }

        if (!$lastStatus || count($lastStatus) != 3) {
            $this->error($methodName, 5, 'Invalid server response');
            return false;
        } elseif ($lastStatus[1] != 200) {
            $this->error($methodName, 5, $lastStatus[1] . ' ' . substr($lastStatus[2], 0, strpos($lastStatus[2], "\r\n")));

            return false;
        }

        return $response;
    }

    /**
     * Invokes the error handler.
     *
     * @param   string $methodName The name of the API method.
     * @param   int $errorCode The error code.
     * @param   string $errorMessage The error message.
     *
     * @return  void
     */
    protected function error($methodName, $errorCode, $errorMessage)
    {
        $this->errorCode = $errorCode;
        $this->errorMessage = $methodName . " : " . $errorMessage;
    }

}
