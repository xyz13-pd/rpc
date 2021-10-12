<?php

namespace inisire\RPC\Error\Serializer;

use inisire\RPC\Error\ValidationError;
use inisire\DataObject\Error\ErrorInterface;
use inisire\DataObject\Error\PropertyError;

class ValidationErrorSerializer
{
    public static function serializeProperty(ErrorInterface $error)
    {
        return [
            'code' => $error->getCode(),
            'message' => $error->getMessage()
        ];
    }

    /**
     * @param array<ErrorInterface> $errors
     */
    public static function serializeErrors(iterable $errors, string $root = null, array &$result)
    {
        foreach ($errors as $error) {
            if ($error instanceof PropertyError) {
                $path = ($root ? $root . '.' : '') . $error->getProperty();
                self::serializeErrors($error->getErrors(), $path, $result);
            } else {
                $result[] = [
                    'property' => $root,
                    'code' => $error->getCode(),
                    'message' => $error->getMessage()->serialize()
                ];
            }
        }
    }

    public static function serialize(ValidationError $error): array
    {
        $serializedErrors = [];
        self::serializeErrors($error->getErrors(), null, $serializedErrors);

        return [
            'code' => $error->getCode(),
            'message' => $error->getMessage(),
            'violations' => $serializedErrors
        ];
    }
}