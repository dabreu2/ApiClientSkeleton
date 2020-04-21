<?php
/**
 * Created by PhpStorm.
 * User: dabreu
 * Date: 2/21/20
 * Time: 5:56 p. m.
 */

namespace CSApi;


class ApiResponse implements \JsonSerializable
{
    /** @var integer */
    private $statusCode;

    /** @var mixed */
    private $error;

    /** @var mixed */
    private $debug;

    /** @var mixed */
    private $content;

    /** @var bool */
    private $isDebug;

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return mixed
     */
    public function getDebug(): ?array
    {
        return $this->debug;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $data
     * @param bool $debug
     * @return ApiResponse
     */
    public static function fromString(string $data, $debug = false){
        $response = json_decode($data, true);
        $ret = new self();
        $ret->isDebug = $debug;

        $ret->statusCode = 0;
        if (array_key_exists('statusCode', $response)){
            $ret->statusCode = (int) $response['statusCode'];
        }

        $ret->content = null;
        if (array_key_exists('response', $response)){
            $ret->content = json_decode($response['response'], true);
        }

        if (array_key_exists('error', $response)) {
            $ret->error = $response['error'];
        }

        if (array_key_exists('debug', $response)) {
            $ret->debug = $response['debug'];
        }
        return $ret;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this);
    }

    /**
     * @return bool
     */
    private function isDebug(){
        return $this->isDebug;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @throws \Exception
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $ret = [
            'statusCode' => $this->getStatusCode(),
            'content' => $this->getContent()
        ];

        if (!empty($this->getError())){
            $ret['error'] = $this->getError();
        }

        if ($this->isDebug()) {
            $ret['debug'] = $this->getDebug();
        }
        return $ret;
    }
}