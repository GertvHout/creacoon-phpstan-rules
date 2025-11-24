<?php

namespace Creacoon\PhpStanRules;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

/**
 * Enforce Class naming:
 * - Class/Interface/Trait names must be PascalCase.
 *
 * @implements Rule<ClassLike>
 */
class EnforceClassNameConvention  implements Rule
{

    private NamingConventionHelper $naming_convention_helper;

    public function __construct()
    {
        $this->naming_convention_helper = new NamingConventionHelper();
    }

    public function getNodeType(): string
    {
        return ClassLike::class;
    }

    /**
     * @param ClassLike $node
     * @return array<int, string>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        // Skip anonymous classes
        if (! isset($node->name)) {
            return [];
        }

        $class_name = $node->name->toString();
        $errors = [];

        // Enforce PascalCase (Starts with Uppercase, no underscores)
        if (! $this->naming_convention_helper->isPascalCase($class_name)) {
            return [sprintf('Class/Interface/Trait "%s" must use PascalCase naming convention.', $class_name)];
        }

        return [];
    }
}