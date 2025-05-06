<?php

namespace App\Domain\Model;

abstract class AppModel
{
    protected function getVars(): array
    {
        $vars = [];
        $exceptions = ['id'];
        foreach ((array) $this as $key => $item) {
            if (!is_array($item) && !in_array($key, $exceptions)) {
                $vars[] = $key;
            }
        }
        return $vars;
    }
    abstract public static function getInstance(array $data): self;

    public function toArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        $result = [];
        foreach ($properties as $property) {
            $value = $property->getValue($this);
            if (!is_array($value) && $property->getName() != 'table') {
                $result[$property->getName()] = $value;
            }
        }
        return $result;
    }

    public function getContext(): array
    {
        $context = [];
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties();
        foreach ($properties as $reflection_property) {
            $property_name = $reflection_property->name;
            $context[ $property_name ] = $this->$property_name;
        }
        return $context;
    }

    public function toArrayInsert(): array
    {
        $array = $this->toArray();
        unset($array['id']);
        return $array;
    }

    public function getInsertKey(): string
    {
        return '`' . join('`, `', $this->getVars()) . '`';
    }

    public function getInsertValue(): string
    {
        return ':' . join(', :', $this->getVars());
    }

    public function getUpdateSet(array $exception = []): string
    {
        $fields = $this->getVars();
        if (!empty($exception)) {
            $tmp = [];
            foreach ($this->getVars() as $var) {
                if (!in_array($var, $exception)) {
                    $tmp[] = $var;
                }    
            }
            $fields = $tmp;
        }    
        return join(', ', array_map(function($item) {
            return "`$item` = :$item";
        }, $fields));
    }

}