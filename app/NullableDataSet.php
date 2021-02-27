<?php

declare(strict_types=1);

namespace App;

use Yiisoft\Validator\DataSetInterface;

class NullableDataSet implements DataSetInterface
{
    public function __construct(private array $data)
    {
    }

    public function getAttributeValue(string $attribute)
    {
        return $this->data[$attribute] ?? null;
    }

    public function hasAttribute(string $attribute): bool
    {
        return array_key_exists($attribute, $this->data);
    }
}
