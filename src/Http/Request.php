<?php

namespace AlexQiu\Sdkit\Http;

use AlexQiu\Sdkit\Support\Collection;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Psr\Http\Message\RequestInterface;

class Request extends GuzzleRequest
{
    /**
     * @return string
     */
    public function getBodyContents()
    {
        $this->getBody()->rewind();
        $contents = $this->getBody()->getContents();
        $this->getBody()->rewind();

        return $contents;
    }

    /**
     * @param RequestInterface $request
     *
     * @return Request
     */
    public static function buildFromPsrRequest(RequestInterface $request)
    {
        return new static(
            $request->getMethod(),
            $request->getUri(),
            $request->getHeaders(),
            $request->getBody(),
            $request->getProtocolVersion()
        );
    }

    /**
     * Build to json.
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * Build to array.
     *
     * @return array
     */
    public function toArray()
    {
        $content = $this->removeControlCharacters($this->getBodyContents());
        $contentType = $this->getHeaderLine('Content-Type');

        switch (true) {
            case strpos($contentType, 'application/x-www-form-urlencoded') !== false:
                parse_str($content, $array);
                return $array;
            case strpos($contentType, 'application/json') !== false:
                $array = json_decode($content, true, 512, JSON_BIGINT_AS_STRING);
                if (JSON_ERROR_NONE === json_last_error()) {
                    return (array)$array;
                }
                break;
        }

        return [];
    }

    /**
     * Get collection data.
     *
     * @return Collection
     */
    public function toCollection()
    {
        return new Collection($this->toArray());
    }

    /**
     * @return object
     */
    public function toObject()
    {
        return json_decode($this->toJson());
    }

    /**
     * @return bool|string
     */
    public function __toString()
    {
        return $this->getBodyContents();
    }

    /**
     * @param string $content
     *
     * @return string
     */
    protected function removeControlCharacters(string $content)
    {
        return \preg_replace('/[\x00-\x1F\x80-\x9F]/u', '', $content);
    }
}