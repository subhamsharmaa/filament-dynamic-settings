<?php

namespace Subham\FilamentDynamicSettings\Tests\Unit;

use Carbon\Carbon;
use Subham\FilamentDynamicSettings\Models\Setting;
use Subham\FilamentDynamicSettings\Tests\TestCase;

class HasSettingsValueTest extends TestCase
{
    // -------------------------------------------------------
    //  getFormattedValue()
    // -------------------------------------------------------

    public function test_formatted_value_returns_null_for_null(): void
    {
        $setting = $this->createSetting(['value' => null, 'type' => 'text']);

        $this->assertNull($setting->getFormattedValue());
    }

    public function test_formatted_value_returns_null_for_empty_string(): void
    {
        $setting = $this->createSetting(['value' => '', 'type' => 'text']);

        $this->assertNull($setting->getFormattedValue());
    }

    public function test_formatted_value_casts_boolean_true(): void
    {
        $setting = $this->createSetting(['value' => '1', 'type' => 'boolean']);

        $result = $setting->getFormattedValue();

        $this->assertTrue($result);
        $this->assertIsBool($result);
    }

    public function test_formatted_value_casts_boolean_false(): void
    {
        $setting = $this->createSetting(['value' => '0', 'type' => 'boolean']);

        $result = $setting->getFormattedValue();

        $this->assertFalse($result);
        $this->assertIsBool($result);
    }

    public function test_formatted_value_casts_boolean_string_true(): void
    {
        $setting = $this->createSetting(['value' => 'true', 'type' => 'boolean']);

        $this->assertTrue($setting->getFormattedValue());
    }

    public function test_formatted_value_casts_boolean_string_false(): void
    {
        $setting = $this->createSetting(['value' => 'false', 'type' => 'boolean']);

        $this->assertFalse($setting->getFormattedValue());
    }

    public function test_formatted_value_casts_number(): void
    {
        $setting = $this->createSetting(['value' => '42', 'type' => 'number']);

        $result = $setting->getFormattedValue();

        $this->assertSame(42, $result);
        $this->assertIsInt($result);
    }

    public function test_formatted_value_casts_integer(): void
    {
        $setting = $this->createSetting(['value' => '100', 'type' => 'integer']);

        $result = $setting->getFormattedValue();

        $this->assertSame(100, $result);
        $this->assertIsInt($result);
    }

    public function test_formatted_value_casts_numeric_as_float(): void
    {
        $setting = $this->createSetting(['value' => '3.14', 'type' => 'numeric']);

        $result = $setting->getFormattedValue();

        $this->assertSame(3.14, $result);
        $this->assertIsFloat($result);
    }

    public function test_formatted_value_returns_non_numeric_value_as_is(): void
    {
        $setting = $this->createSetting(['value' => 'not-a-number', 'type' => 'number']);

        $this->assertSame('not-a-number', $setting->getFormattedValue());
    }

    public function test_formatted_value_parses_json(): void
    {
        $data = ['foo' => 'bar', 'baz' => [1, 2, 3]];
        $setting = $this->createSetting([
            'value' => json_encode($data),
            'type' => 'json',
        ]);

        $result = $setting->getFormattedValue();

        $this->assertIsArray($result);
        $this->assertEquals($data, $result);
    }

    public function test_formatted_value_returns_raw_on_invalid_json(): void
    {
        $setting = $this->createSetting(['value' => '{invalid json', 'type' => 'json']);

        $result = $setting->getFormattedValue();

        // json_decode returns null on invalid JSON, so parseJson falls back to value
        $this->assertSame('{invalid json', $result);
    }

    public function test_formatted_value_parses_date(): void
    {
        $setting = $this->createSetting(['value' => '2025-06-15', 'type' => 'date']);

        $result = $setting->getFormattedValue();

        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('2025-06-15', $result->format('Y-m-d'));
    }

    public function test_formatted_value_parses_date_time(): void
    {
        $setting = $this->createSetting([
            'value' => '2025-06-15 14:30:00',
            'type' => 'date_time',
        ]);

        $result = $setting->getFormattedValue();

        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('2025-06-15 14:30:00', $result->format('Y-m-d H:i:s'));
    }

    public function test_formatted_value_returns_url_for_file_type(): void
    {
        $setting = $this->createSetting([
            'value' => 'https://example.com/image.png',
            'type' => 'file',
        ]);

        $result = $setting->getFormattedValue();

        $this->assertSame('https://example.com/image.png', $result);
    }

    public function test_formatted_value_returns_text_as_is(): void
    {
        $setting = $this->createSetting(['value' => 'hello world', 'type' => 'text']);

        $this->assertSame('hello world', $setting->getFormattedValue());
    }

    public function test_formatted_value_returns_default_type_as_string(): void
    {
        $setting = $this->createSetting(['value' => 'some value', 'type' => 'unknown_type']);

        $this->assertSame('some value', $setting->getFormattedValue());
    }

    // -------------------------------------------------------
    //  getRawValue()
    // -------------------------------------------------------

    public function test_raw_value_returns_raw_attribute(): void
    {
        $setting = $this->createSetting(['value' => '42', 'type' => 'number']);

        $raw = $setting->getRawValue();

        // Should return the raw string, not cast to int
        $this->assertSame('42', $raw);
    }

    public function test_raw_value_returns_null_when_not_set(): void
    {
        $setting = $this->createSetting(['value' => null, 'type' => 'text']);

        $this->assertNull($setting->getRawValue());
    }

    // -------------------------------------------------------
    //  No double-casting regression
    // -------------------------------------------------------

    public function test_json_is_not_double_decoded(): void
    {
        $data = ['nested' => ['key' => 'value']];
        $setting = $this->createSetting([
            'value' => json_encode($data),
            'type' => 'json',
        ]);

        $result = $setting->getFormattedValue();

        // If double-decoding occurred, this would fail (first decode → array, second decode → null)
        $this->assertIsArray($result);
        $this->assertEquals($data, $result);
    }

    public function test_boolean_is_not_double_cast(): void
    {
        $setting = $this->createSetting(['value' => '1', 'type' => 'boolean']);

        // Call multiple times to ensure idempotent
        $first = $setting->getFormattedValue();
        $second = $setting->getFormattedValue();

        $this->assertTrue($first);
        $this->assertTrue($second);
    }
}
