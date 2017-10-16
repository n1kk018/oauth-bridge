<?php

namespace Preferans\Oauth\Server\CodeChallengeVerifiers;

/**
 * Preferans\Oauth\Server\CodeChallengeVerifiers\S256Verifier
 *
 * @package Preferans\Oauth\Server\CodeChallengeVerifiers
 */
class S256Verifier implements CodeChallengeVerifierInterface
{
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getMethod(): string
    {
        return 'S256';
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
        return hash_equals(
            strtr(rtrim(base64_encode(hash('sha256', $codeVerifier)), '='), '+/', '-_'),
            $codeChallenge
        );
    }
}
