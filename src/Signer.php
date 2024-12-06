<?php

namespace AlexQiu\Sdkit;

use AlexQiu\Sdkit\Contracts\SignerInterface;

abstract class Signer implements SignerInterface
{
    /**
     * @var ServiceContainer
     */
    protected $app;

    /**
     * AccessToken constructor.
     *
     * @param ServiceContainer $app
     */
    public function __construct(ServiceContainer $app)
    {
        $this->app = $app;
    }

    /**
     * @param array $params
     *
     * @return array
     */
    protected function filterParams(array $params): array
    {
        return array_filter($params, function ($param) {
            return $param !== null && $param !== '';
        });
    }


    /**
     * @param array $params
     *
     * @return array|string[]
     */
    protected function formatParams(array $params): array
    {
        return array_map(function ($param) {
            if (is_array($param)) {
                return json_encode($param, JSON_UNESCAPED_UNICODE);
            }
            return $param;
        }, $params);
    }

    /**
     * @param array $params
     *
     * @return array
     */
    protected function sortParams(array $params): array
    {
        ksort($params);

        return $params;
    }

    /**
     * @param array $params
     * @param string|null $secretKey
     *
     * @return string
     */
    protected function joinParams(array $params, ?string $secretKey = null): string
    {
        $secretKey = $secretKey ?: '';

        $stringToBeSigned = $secretKey;

        foreach ($params as $k => $v) {
            $stringToBeSigned .= "{$k}{$v}";
        }

        $stringToBeSigned .= $secretKey;

        return $stringToBeSigned;
    }

    /**
     * @param string $stringToBeSigned
     *
     * @return string
     */
    protected function encryptString(string $stringToBeSigned): string
    {
        return strtoupper(md5($stringToBeSigned));
    }

    /**
     * @param array $params
     * @param string|null $secretKey
     *
     * @return string
     */
    public function sign(array $params, ?string $secretKey = null): string
    {
        $params = $this->filterParams($params);

        $params = $this->formatParams($params);

        $params = $this->sortParams($params);

        $stringToBeSigned = $this->joinParams($params, $secretKey);

        return $this->encryptString($stringToBeSigned);
    }

    /**
     * @param string $signature
     * @param array $params
     * @param string|null $secretKey
     *
     * @return bool
     */
    public function verify(string $signature, array $params, ?string $secretKey = null): bool
    {
        return $signature == $this->sign($params, $secretKey);
    }
}