<?php

namespace Creacoon\PhpStanRules;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

class EnforceScopedVariableConvention implements Rule
{

    private array $excluded_namespaces;
    private NamingConventionHelper $naming_convention_helper;

    public function __construct(array $excluded_namespaces = [])
    {
        $this->excluded_namespaces = $excluded_namespaces;
        $this->naming_convention_helper = new NamingConventionHelper();
    }

    /**
     * Returns the node type this rule is checking.
     * We want to check variable assignments (initialization).
     */
    public function getNodeType(): string
    {
        return Assign::class;
    }

    /**
     * Process a node and return errors found.
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Assign) {
            return [];
        }

        if ($this->isExcludedNamespace($scope)) {
            return [];
        }
        // Check for variable assignments
        if ($node->var instanceof Variable) {
            return $this->checkVariable($node->var);
        }
        // Check for property assignments ($this->property = ...)
        if ($node->var instanceof PropertyFetch) {
            return $this->checkPropertyFetch($node->var);
        }
        return [];
    }

    /**
     * Check if a variable follows naming conventions
     */
    private function checkVariable(Variable $variable): array
    {
        // Skip checking if not a simple string variable name (like when it's a dynamic variable ${$foo})
        if (!is_string($variable->name)) {
            return [];
        }

        // Check if variable name follows snake_case pattern
        if (!$this->naming_convention_helper->isSnakeCase($variable->name)) {
            return ['Variable "' . $variable->name . '" should be initialized using snake_case naming convention.'];
        }
        return [];
    }

    /**
     * Check if a property fetch follows naming conventions
     */
    private function checkPropertyFetch(PropertyFetch $propertyFetch): array
    {
        // We're only interested in assignments to $this->property
        if (!$propertyFetch->var instanceof Variable || !is_string($propertyFetch->var->name) || $propertyFetch->var->name !== 'this') {
            return [];
        }

        // Skip dynamic properties
        if (!is_string($propertyFetch->name->name)) {
            return [];
        }

        // Check if property name follows snake_case pattern
        $property_name = $propertyFetch->name->name;
        if (!$this->naming_convention_helper->isSnakeCase($property_name)) {
            return [sprintf('Property "$this->%s" should use snake_case naming convention.', $property_name)];
        }

        return [];
    }

    /**
     * Check if a string follows snake_case naming convention.
     */
    private function isSnakeCase(string $name): bool
    {
        return preg_match('/^[a-z][a-z0-9]*(_[a-z0-9]+)*$/', $name) === 1;
    }

    private function isExcludedNamespace(Scope $scope): bool
    {
        $namespace = $scope->getNamespace();
        if (!is_string($namespace) || $namespace === '') {
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