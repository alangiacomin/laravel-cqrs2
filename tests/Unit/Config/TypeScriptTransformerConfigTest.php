<?php

namespace Tests\Unit\Config;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTime;
use DateTimeImmutable;
use Spatie\LaravelData\Support\TypeScriptTransformer\DataTypeScriptTransformer;
use Spatie\LaravelTypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\LaravelTypeScriptTransformer\Transformers\SpatieStateTransformer;
use Spatie\TypeScriptTransformer\Collectors\DefaultCollector;
use Spatie\TypeScriptTransformer\Collectors\EnumCollector;
use Spatie\TypeScriptTransformer\Formatters\PrettierFormatter;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\Transformers\SpatieEnumTransformer;
use Spatie\TypeScriptTransformer\Writers\ModuleWriter;
use Tests\TestCase;

class TypeScriptTransformerConfigTest extends TestCase
{
    public function test_typescript_transformer_config_is_loaded_with_expected_values(): void
    {
        $config = require dirname(__DIR__, 3).'/src/config/typescript-transformer.php';

        $this->assertSame([app_path()], $config['auto_discover_types']);
        $this->assertSame([
            DefaultCollector::class,
            EnumCollector::class,
        ], $config['collectors']);
        $this->assertSame([
            SpatieStateTransformer::class,
            EnumTransformer::class,
            SpatieEnumTransformer::class,
            DataTypeScriptTransformer::class,
            DtoTransformer::class,
        ], $config['transformers']);
        $this->assertSame([
            DateTime::class => 'string',
            DateTimeImmutable::class => 'string',
            CarbonInterface::class => 'string',
            CarbonImmutable::class => 'string',
            Carbon::class => 'string',
        ], $config['default_type_replacements']);
        $this->assertSame(resource_path('js/types/generated/index.ts'), $config['output_file']);
        $this->assertSame(ModuleWriter::class, $config['writer']);
        $this->assertSame(PrettierFormatter::class, $config['formatter']);
        $this->assertTrue($config['transform_to_native_enums']);
        $this->assertFalse($config['transform_null_to_optional']);
    }
}
