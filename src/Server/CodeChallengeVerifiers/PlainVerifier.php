<?php

namespace Preferans\Oauth\Server\CodeChallengeVerifiers;

/**
 * Preferans\Oauth\Server\CodeChallengeVerifiers\PlainVerifier
 *
 * @package Preferans\Oauth\Server\CodeChallengeVerifiers
 */
class PlainVerifier implements CodeChallengeVerifierInterface
{
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getMethod(): string
    {
        return 'plain';
    }

    /**
     * {@inheritdoc}
     *
     * @param string $codeVerifier
     * @param string $codeChallenge
     *
     * @return bool
     */
    public function verifyCodeChallenge(string $codeVerifier, string $codeChallenge): bool
    {
        return hash_equals($codeVerifier, $codeChallenge);
    }
}
