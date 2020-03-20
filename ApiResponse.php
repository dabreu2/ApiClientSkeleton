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
    private $data;

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
    public function getError(): ?array
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
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $data
     * @return ApiResponse
     */
    public static function fromString(string $data){
        $response = json_decode($data, true);
        $ret = new self();
        $ret->statusCode = isset($response['statusCode']) ? intval($response['statusCode']) : 0;
        $ret->data = isset($response['data']) ? $response['data'] : null;
        if (isset($response['error'])) {
            $ret->error = $response['error'];
        }
        if (isset($response['debug'])) {
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
            'data' => $this->getData()
        ];

        if (!empty($this->getError())){
            $ret['error'] = $this->getError();
        }

        if (Api::getInstance()->isDebug()){
            $ret['debug'] = $this->getDebug();
        }
        return $ret;
    }
}