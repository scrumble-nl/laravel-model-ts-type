<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator\Console\Commands;

use Exception;
use ReflectionClass;
use ReflectionException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model;
use Scrumble\TypeGenerator\Facades\FormatNamespace;
use Scrumble\TypeGenerator\Exceptions\InvalidPathException;
use Scrumble\TypeGenerator\Support\Mutators\CastsPropertyMutator;
use Scrumble\TypeGenerator\Support\Mutators\HiddenPropertyMutator;
use Scrumble\TypeGenerator\Support\TypescriptFormatters\EnumFormatter;
use Scrumble\TypeGenerator\Support\Generators\DatabasePropertyGenerator;
use Scrumble\TypeGenerator\Support\Generators\RelationPropertyGenerator;
use Scrumble\TypeGenerator\Support\Generators\AttributePropertyGenerator;

class GenerateTypesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'types:generate {--modelDir=} {--namespace=} {--outputDir=} {--noKebabCase} {--model=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate TypeScript types based on your models';

    /**
     * @var string[]
     */
    private array $modelHits = [];

    /**
     * @var string
     */
    private string $modelDir;

    /**
     * @var string
     */
    private string $outputDir;

    /**
     * @var bool|string
     */
    private string|bool $namespace;

    /**
     * @var bool
     */
    private bool $useKebabCase;

    /**
     * @var null|string
     */
    private string|null $model;

    /**
     * @var DatabasePropertyGenerator
     */
    private DatabasePropertyGenerator $databaseGenerator;

    /**
     * @var RelationPropertyGenerator
     */
    private RelationPropertyGenerator $relationGenerator;

    /**
     * @var AttributePropertyGenerator
     */
    private AttributePropertyGenerator $attributeGenerator;

    /**
     * @var CastsPropertyMutator
     */
    private CastsPropertyMutator $castsPropertyMutator;

    /**
     * @var HiddenPropertyMutator
     */
    private HiddenPropertyMutator $hiddenPropertyMutator;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->databaseGenerator = new DatabasePropertyGenerator();
        $this->relationGenerator = new RelationPropertyGenerator();
        $this->attributeGenerator = new AttributePropertyGenerator();
        $this->castsPropertyMutator = new CastsPropertyMutator($this);
        $this->hiddenPropertyMutator = new HiddenPropertyMutator();
    }

    /**
     * Execute the console command.
     *
     * @throws Exception
     * @return void
     */
    public function handle(): void
    {
        $this->modelDir = $this->option('modelDir') ?? config('laravel-model-ts-type.model_dir');
        $this->namespace = $this->option('namespace') ?? config('laravel-model-ts-type.namespace');
        $this->outputDir = $this->option('outputDir') ?? config('laravel-model-ts-type.output_dir');
        $this->useKebabCase = !($this->option('noKebabCase') ?? config('laravel-model-ts-type.no_kebab_case'));
        // @phpstan-ignore-next-line
        $this->model = $this->option('model') ?? null;

        $this->getModels($this->modelDir);

        foreach ($this->modelHits as $modelPath) {
            /** @var class-string $fullyQualifiedName */
            $fullyQualifiedName = FormatNamespace::get($modelPath);

            if (null !== $this->model && $this->model !== $fullyQualifiedName) {
                continue;
            }

            if (!in_array($fullyQualifiedName, get_declared_classes())) {
                include_once $modelPath;
            }

            $reflectionClass = new ReflectionClass($fullyQualifiedName);

            if ($reflectionClass->isAbstract() || $reflectionClass->isTrait()) {
                continue;
            }

            if ($reflectionClass->isEnum()) {
                $enumFormatter = new EnumFormatter($fullyQualifiedName);
                $tsContent = $enumFormatter->format();

                $this->writeToTsFile($modelPath, $tsContent, $enumFormatter->getFileName());

                continue;
            }

            $actualModel = new $fullyQualifiedName();

            if ($actualModel instanceof Model) {
                $propertyDefinition = $this->createPropertyDefinition($actualModel);
                $this->getClassName($modelPath);
                $tsContent = $this->formatContents($this->getClassName($modelPath), $propertyDefinition, $this->transformNamespace($reflectionClass->getNamespaceName()));
                $this->writeToTsFile($modelPath, $tsContent);
            }
        }
    }

    /**
     * Format the contents for the TypeScript file.
     *
     * @param  string      $className
     * @param  array       $propertyDefinition
     * @param  null|string $namespace
     * @return string
     */
    private function formatContents(string $className, array $propertyDefinition, ?string $namespace): string
    {
        $indent = $this->namespace ? '    ' : '';
        $baseString = '';

        if ($this->namespace) {
            $baseString = 'declare namespace ' . $namespace . ' {' . PHP_EOL;
        }

        $baseString .= $indent . 'type ' . ucfirst(camel_case($className)) . ' = {' . PHP_EOL;

        foreach ($propertyDefinition as $key => $value) {
            $baseString .= $indent . '    ' . $key . $value['operator'] . ' ' . $value['value'] . ';' . PHP_EOL;
        }

        if ($this->namespace) {
            $baseString .= $indent . '}' . PHP_EOL;
        }

        return $baseString . '}' . PHP_EOL;
    }

    /**
     * Recursively get all models from the directory path.
     *
     * @param  string               $directoryPath
     * @throws InvalidPathException
     * @return void
     */
    private function getModels(string $directoryPath): void
    {
        try {
            foreach (new \DirectoryIterator($directoryPath) as $file) {
                if ($file->isDir() && !$file->isDot()) {
                    $this->getModels($file->getPathname());
                } elseif (!$file->isDot()) {
                    $this->modelHits[] = $file->getPathname();
                }
            }
        } catch (\UnexpectedValueException $exception) {
            throw new InvalidPathException('Could not find the given directory');
        }
    }

    /**
     * Create all different property definitions.
     *
     * @param  Model               $model
     * @throws Exception
     * @throws ReflectionException
     * @return array
     */
    private function createPropertyDefinition(Model $model): array
    {
        $propertyDefinition = array_merge(
            $this->databaseGenerator->getPropertyDefinition($model),
            $this->relationGenerator->getPropertyDefinition($model),
            $this->attributeGenerator->getPropertyDefinition($model)
        );

        $this->castsPropertyMutator->mutate($model, $propertyDefinition);
        $this->hiddenPropertyMutator->mutate($model, $propertyDefinition);

        return $propertyDefinition;
    }

    /**
     * @param  string $modelPath
     * @return array
     */
    private function getLocationSegments(string $modelPath): array
    {
        $sanitizedString = str_replace(unify_path($this->modelDir) . '/', '', unify_path($modelPath));

        return explode('/', $sanitizedString);
    }

    /**
     * @param  string $modelPath
     * @return string
     */
    private function getClassName(string $modelPath): string
    {
        $locationSegments = $this->getLocationSegments($modelPath);
        $modelName = str_replace('.php', '', array_pop($locationSegments));

        return $this->useKebabCase ? kebab_case($modelName) : $modelName;
    }

    /**
     * @param  null|string $modelNamespace
     * @return string
     */
    private function transformNamespace(?string $modelNamespace): string
    {
        return str_replace('\\', '.', $modelNamespace ?? '');
    }

    /**
     * Write the given model to a TypeScript file.
     *
     * @param  string      $modelPath
     * @param  string      $content
     * @param  null|string $filename
     * @return void
     */
    private function writeToTsFile(string $modelPath, string $content, string $filename = null): void
    {
        $sanitizedString = str_replace(unify_path($this->modelDir) . '/', '', unify_path($modelPath));
        $locationSegments = explode('/', $sanitizedString);
        $modelName = str_replace('.php', '', array_pop($locationSegments));
        $className = $this->useKebabCase ? kebab_case($modelName) : $modelName;
        $fullPath = $this->outputDir . '/' . implode('/', $locationSegments);
        $filename = $filename ?? $className;

        if (!File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }

        File::put($fullPath . '/' . $filename . '.d.ts', $content);
    }
}
