<?php
/**
 * Created by PhpStorm.
 * User: dabreu
 * Date: 7/23/21
 * Time: 16:38
 */

namespace CSApi\OpenTracing;

/**
 * Class OpenTracing
 * @package CSApi\OpenTracing
 */
class OpenTracing implements ITracing
{
    const SPAN_OPTIONS = 'spanOpts';
    const OPERATION_NAME = 'operationName';
    const TAGS = 'tags';
    const FORMAT = 'format';

    /**
     * @var array|null
     */
    private $options;

    private $spanScope;

    private $hasOptions;

    /**
     * OpenTracing constructor.
     * @param array $options
     * @throws \Exception
     */
    public function __construct(array $options)
    {
        $this->options = $options;
        $this->hasOptions = is_array($options) && count($options) > 0;

        if ($this->hasOptions){
            if (empty($this->options[self::OPERATION_NAME])){
                throw new \Exception('OpenTracing initialization error: missing ' . self::OPERATION_NAME . ' parameter');
            }
        }
    }

    private function allowTracing(): bool
    {
        return class_exists('OpenTracing\GlobalTracer') && $this->hasOptions;
    }

    /**
     * @return $this
     */
    public function start(): ITracing
    {
        if ($this->allowTracing()){
            $this->spanScope = \OpenTracing\GlobalTracer::get()->startActiveSpan(
                $this->options[self::OPERATION_NAME],
                $this->options[self::SPAN_OPTIONS] ?? []);
        }
        return $this;
    }

    /**
     * @param string $key
     * @param string|bool|int|float $value
     * @return $this
     */
    public function setTag(string $key, $value): ITracing
    {
        if ($this->allowTracing()) {
            $this->spanScope->getSpan()->setTag($key, $value);
        }
        return $this;
    }

    /**
     * @param array $data
     * @param null $timestamp
     * @return $this
     */
    public function log(array $data = [], $timestamp = null): ITracing
    {
        if ($this->allowTracing()) {
            $this->spanScope->getSpan()->log($data, $timestamp);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function close(): ITracing
    {
        if ($this->allowTracing()) {
            // add defined tags in options if have
            $tags = $this->options[self::TAGS] ?? [];
            if (is_array($tags) && count($tags) > 0){
                foreach ($tags as $key => $value) {
                    $this->setTag($key, $value);
                }
            }

            // close scope
            $this->spanScope->close();
        }
        return $this;
    }

    /**
     * @param array $carrier
     * @return bool
     */
    public function injectTrace(array &$carrier): bool
    {
        if ($this->allowTracing()) {
            \OpenTracing\GlobalTracer::get()->inject(
                $this->spanScope->getSpan()->getContext(),
                $this->options[self::FORMAT] ?? 'http_headers',
                $carrier
            );
            return true;
        }
        return false;
    }
}