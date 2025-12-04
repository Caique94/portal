<?php

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * Processor do Monolog para sanitizar dados sensíveis dos logs
 *
 * Remove automaticamente: senhas, tokens, chaves de API, etc.
 */
class SanitizeLogger implements ProcessorInterface
{
    /**
     * Chaves consideradas sensíveis (case-insensitive)
     */
    private array $sensitiveKeys = [
        'password',
        'senha',
        'token',
        'access_token',
        'refresh_token',
        'api_key',
        'apikey',
        'secret',
        'authorization',
        'bearer',
        'csrf',
        'csrf_token',
        'x-csrf-token',
        '_token',
        'card_number',
        'cvv',
        'credit_card',
        'ssn',
        'social_security',
    ];

    /**
     * Padrões regex para detectar dados sensíveis
     */
    private array $patterns = [
        '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/' => '[EMAIL]',  // Email
        '/\b\d{3}\.?\d{3}\.?\d{3}-?\d{2}\b/' => '[CPF]',  // CPF
        '/\b\d{2}\.?\d{3}\.?\d{3}\/?\d{4}-?\d{2}\b/' => '[CNPJ]',  // CNPJ
        '/\b\d{4}[\s-]?\d{4}[\s-]?\d{4}[\s-]?\d{4}\b/' => '[CARD]',  // Cartão
        '/Bearer\s+[A-Za-z0-9\-._~+\/]+=*/' => 'Bearer [REDACTED]',  // Bearer token
    ];

    /**
     * Processar registro de log
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        $record['context'] = $this->sanitize($record['context']);
        $record['extra'] = $this->sanitize($record['extra']);

        // Sanitizar mensagem também
        if (is_string($record['message'])) {
            $record['message'] = $this->sanitizeString($record['message']);
        }

        return $record;
    }

    /**
     * Sanitizar array ou objeto recursivamente
     */
    private function sanitize(mixed $data): mixed
    {
        if (is_array($data)) {
            return $this->sanitizeArray($data);
        }

        if (is_object($data)) {
            return $this->sanitizeObject($data);
        }

        if (is_string($data)) {
            return $this->sanitizeString($data);
        }

        return $data;
    }

    /**
     * Sanitizar array
     */
    private function sanitizeArray(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if ($this->isSensitiveKey($key)) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } elseif (is_object($value)) {
                $sanitized[$key] = $this->sanitizeObject($value);
            } elseif (is_string($value)) {
                $sanitized[$key] = $this->sanitizeString($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitizar objeto
     */
    private function sanitizeObject(object $data): object
    {
        $sanitized = clone $data;

        foreach (get_object_vars($sanitized) as $key => $value) {
            if ($this->isSensitiveKey($key)) {
                $sanitized->$key = '[REDACTED]';
            } elseif (is_array($value)) {
                $sanitized->$key = $this->sanitizeArray($value);
            } elseif (is_object($value)) {
                $sanitized->$key = $this->sanitizeObject($value);
            } elseif (is_string($value)) {
                $sanitized->$key = $this->sanitizeString($value);
            }
        }

        return $sanitized;
    }

    /**
     * Sanitizar string (aplicar regex patterns)
     */
    private function sanitizeString(string $data): string
    {
        foreach ($this->patterns as $pattern => $replacement) {
            $data = preg_replace($pattern, $replacement, $data);
        }

        return $data;
    }

    /**
     * Verificar se chave é sensível
     */
    private function isSensitiveKey(string|int $key): bool
    {
        if (!is_string($key)) {
            return false;
        }

        $key = strtolower($key);

        foreach ($this->sensitiveKeys as $sensitiveKey) {
            if (str_contains($key, $sensitiveKey)) {
                return true;
            }
        }

        return false;
    }
}
