<?php

namespace App\Traits;

use App\Models\Sequence;

trait HasSequentialCode
{
    /**
     * Boot the trait
     *
     * Generate sequential code when model is created
     */
    public static function bootHasSequentialCode()
    {
        static::creating(function ($model) {
            // Get or create sequence for this entity type
            $sequence = Sequence::getSequence(static::getSequenceEntityType());

            // Generate and assign the sequential code
            $model->{static::getSequenceCodeField()} = $sequence->getNextCode();
        });
    }

    /**
     * Get the entity type for this model
     * Override this method in your model if needed
     *
     * Default: returns lowercase model class name with pluralization hint
     */
    public static function getSequenceEntityType(): string
    {
        $class = class_basename(static::class);

        // Default mapping
        $mapping = [
            'User' => 'user',
            'Cliente' => 'cliente',
            'Fornecedor' => 'fornecedor',
            'Produto' => 'produto',
            'Projeto' => 'projeto',
            'OrdemServico' => 'ordem_servico',
        ];

        return $mapping[$class] ?? strtolower($class);
    }

    /**
     * Get the field name that stores the sequential code
     * Override this method in your model if needed
     *
     * Default: 'codigo'
     */
    public static function getSequenceCodeField(): string
    {
        return 'codigo';
    }
}
