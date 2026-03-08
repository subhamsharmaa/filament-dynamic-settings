<?php

namespace Subham\FilamentDynamicSettings\Tests\Unit;

use Subham\FilamentDynamicSettings\Resolvers\ComponentResolver;
use Subham\FilamentDynamicSettings\Tests\TestCase;

class ComponentResolverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Reset static state between tests
        $reflection = new \ReflectionClass(ComponentResolver::class);
        $property = $reflection->getProperty('customResolvers');
        $property->setAccessible(true);
        $property->setValue(null, []);
    }

    // -------------------------------------------------------
    //  getAvailableTypes()
    // -------------------------------------------------------

    public function test_get_available_types_returns_default_types(): void
    {
        $types = ComponentResolver::getAvailableTypes();

        $this->assertIsArray($types);
        $this->assertArrayHasKey('text', $types);
        $this->assertArrayHasKey('boolean', $types);
        $this->assertArrayHasKey('select', $types);
        $this->assertArrayHasKey('json', $types);
        $this->assertArrayHasKey('email', $types);
        $this->assertArrayHasKey('url', $types);
    }

    public function test_get_available_types_includes_registered_resolvers(): void
    {
        ComponentResolver::registerResolver('custom_widget', function ($setting) {
            // dummy resolver
        });

        $types = ComponentResolver::getAvailableTypes();

        $this->assertArrayHasKey('custom_widget', $types);
    }

    // -------------------------------------------------------
    //  registerResolver()
    // -------------------------------------------------------

    public function test_register_resolver_stores_custom_resolver(): void
    {
        $called = false;

        ComponentResolver::registerResolver('my_type', function ($setting) use (&$called) {
            $called = true;
        });

        $types = ComponentResolver::getAvailableTypes();
        $this->assertArrayHasKey('my_type', $types);
    }

    // -------------------------------------------------------
    //  buildValidationRules()
    // -------------------------------------------------------

    public function test_build_validation_rules_with_values(): void
    {
        $reflection = new \ReflectionClass(ComponentResolver::class);
        $method = $reflection->getMethod('buildValidationRules');
        $method->setAccessible(true);

        $rules = $method->invoke(null, [
            'required' => '',
            'max' => '255',
            'min' => '5',
            'email' => '',
        ]);

        $this->assertContains('required', $rules);
        $this->assertContains('max:255', $rules);
        $this->assertContains('min:5', $rules);
        $this->assertContains('email', $rules);
    }

    public function test_build_validation_rules_handles_empty_array(): void
    {
        $reflection = new \ReflectionClass(ComponentResolver::class);
        $method = $reflection->getMethod('buildValidationRules');
        $method->setAccessible(true);

        $rules = $method->invoke(null, []);

        $this->assertIsArray($rules);
        $this->assertEmpty($rules);
    }
}
