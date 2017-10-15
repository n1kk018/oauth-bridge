<?php

namespace Preferans\Oauth\Server\CodeChallengeVerifiers;

/**
 * Preferans\Oauth\Server\CodeChallengeVerifiers\CodeChallengeVerifierInterface
 *
 * @package Preferans\Oauth\Server\CodeChallengeVerifiers
 */
interface CodeChallengeVerifierInterface
{
    /**
     * Return code challenge method.
     *
     * @return string
     */
    public function getMethod(): string;

    /**
     * Verify the code challenge.
     *
     * @param string $codeVerifier
     * @param string $codeChallenge
     *
     * @return bool
     */
    public function verifyCodeChallenge(string $codeVerifier, string $codeChallenge): bool;
}
