<?php

namespace AlexQiu\Sdkit\Contracts;

/**
 * Interface AuthorizerSigner.
 */
interface SignerInterface
{
    /**
     * @param array $params
     * @param string|null $secretKey
     *
     * @return string
     */
    public function sign(array $params, ?string $secretKey = null): string;

    /**
     * @param string $signature
     * @param array $params
     * @param string|null $secretKey
     *
     * @return bool
     */
    public function verify(string $signature, array $params, ?string $secretKey = null): bool;
}