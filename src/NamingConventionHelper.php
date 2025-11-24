<?php

namespace Creacoon\PhpStanRules;

class NamingConventionHelper
{
    public function isSnakeCase(string $name): bool
    {
        return preg_match('/^[a-z][a-z0-9]*(_[a-z0-9]+)*$/', $name) === 1;
    }

    public function isCamelCase(string $name): bool
    {
        return preg_match('/^[a-z][a-zA-Z0-9]*$/', $name) === 1;
    }

    public function isPascalCase(string $name): bool
    {
        return preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name) === 1;
    }
}