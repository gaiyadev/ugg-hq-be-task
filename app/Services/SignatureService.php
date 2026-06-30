<?php

namespace App\Services;

/**
 * SignatureService — Mock Off-Chain Signature Provider
 *
 * Generates a deterministic SHA256 signature for a resource.
 * In production, this would call an external signing service or HSM.
 *
 * The signature is stored on the resource and can be verified
 * later to detect tampering. The Solidity ResourceApproval contract
 * would store this same signature on-chain for immutable verification.
 *
 * Signing payload: title + description + created_by + approved_at
 * This ensures any change to any of these fields invalidates the signature.
 */
class SignatureService
{
    /**
     * Generate a SHA256 signature for a resource payload.
     *
     * @param  array<string, mixed>  $payload
     */
    public function generate(array $payload): string
    {
        // Deterministic canonical string — sorted keys ensure order independence
        ksort($payload);
        $canonical = implode('|', array_map(
            fn($key, $value) => "{$key}:{$value}",
            array_keys($payload),
            array_values($payload)
        ));

        return hash('sha256', $canonical);
    }

    /**
     * Generate the standard resource signature.
     * Called when a resource is approved.
     */
    public function signResource(
        string $resourceId,
        string $title,
        ?string $description,
        string $createdBy,
        string $approvedAt
    ): string {
        return $this->generate([
            'resource_id' => $resourceId,
            'title'       => $title,
            'description' => $description ?? '',
            'created_by'  => $createdBy,
            'approved_at' => $approvedAt,
        ]);
    }

    /**
     * Verify a stored signature against a re-computed one.
     * Returns false if the resource data has been tampered with.
     */
    public function verify(string $storedSignature, array $payload): bool
    {
        return hash_equals($storedSignature, $this->generate($payload));
    }

    /**
     * Verify a resource's stored signature.
     *
     * @param  string  $storedSignature  The signature stored in resources.signature
     */
    public function verifyResource(
        string $storedSignature,
        string $resourceId,
        string $title,
        ?string $description,
        string $createdBy,
        string $approvedAt
    ): bool {
        $expected = $this->signResource(
            $resourceId,
            $title,
            $description,
            $createdBy,
            $approvedAt,
        );

        return hash_equals($storedSignature, $expected);
    }
}
