<?php

declare(strict_types=1);

namespace Creacoon\PhpStanRules;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

/**
 * Enforce parameter naming:
 * - Closure and arrow-function parameters must be snake_case.
 * - Named functions and methods properties must be camelCase.
 *
 * @implements Rule<Param>
 */
class EnforceParameterConvention implements Rule
{
    /**
     * @var array|string[]
     */
    private array $excluded_namespaces;
    private NamingConventionHelper $naming_convention_helper;

    public function __construct(
        array $excludedNamespaces = [
            'App\Support\Dto',
        ]
    )
    {
        $this->excluded_namespaces = $excludedNamespaces;
        $this->naming_convention_helper = new NamingConventionHelper();
    }

    /**
     * @return class-string<Param>
     */
    public function getNodeType(): string
    {
        return Param::class;
    }

    /**
     * @return array<int, string>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (
            ! $node instanceof Param ||
            $this->isExcludedNamespace($scope) ||
            ! $node->var instanceof Variable ||
            ! is_string($node->var->name)
        ) {
            return [];
        }

        $name = $node->var->name;
        $parent = $node->getAttribute('parent');

        if ($parent instanceof Closure) {
            if (! $this->naming_convention_helper->isSnakeCase($name)) {
                return [sprintf('Closure parameter "$%s" should use snake_case naming convention.', $name)];
            }
            return [];
        }
        if ($parent instanceof ArrowFunction) {
            if (! $this->naming_convention_helper->isSnakeCase($name)) {
                return [sprintf('Arrow-function parameter "$%s" should use snake_case naming convention.', $name)];
            }
            return [];
        }
        // For functions/methods: enforce camelCase
        if (! $this->naming_convention_helper->isCamelCase($name)) {
            return [sprintf('Parameter "$%s" should use camelCase naming convention.', $name)];
        }
        return [];
    }

    private function isExcludedNamespace(Scope $scope): bool
    {
        $namespace = $scope->getNamespace();
        if (! is_string($namespace) || $namespace === '') {
            return false;
        }

        foreach ($this->excluded_namespaces as $excluded_namespace) {
            if (str_starts_with($namespace, $excluded_namespace)) {
                return true;
            }
        }

        return false;
    }
}
