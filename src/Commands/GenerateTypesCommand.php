<?php

namespace Scrumble\TypeGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model;
use Scrumble\TypeGenerator\Exceptions\InvalidPathException;
use Scrumble\TypeGenerator\Support\Mutators\CastsPropertyMutator;
use Scrumble\TypeGenerator\Support\Mutators\HiddenPropertyMutator;
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
    protected $signature = 'types:generate {--modelDir=} {--namespace=} {--outputDir=} {--noKebabCase}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate TypeScript types based on your models';

    /**
     * @var array
     */
    private $modelHits = [];

    /**
     * @var string
     */
    private $modelDir;

    /**
     * @var string
     */
    private $outputDir;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var boolean
     */
    private $useKebabCase;

    /**
     * @var DatabasePropertyGenerator
     */
    private $databaseGenerator;

    /**
     * @var RelationPropertyGenerator
     */
    private $relationGenerator;

    /**
     * @var AttributePropertyGenerator
     */
    private $attributeGenerator;

    /**
     * @var CastsPropertyMutator
     */
    private $castsPropertyMutator;

    /**
     * @var HiddenPropertyMutator
     */
    private $hiddenPropertyMutator;

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
     * @return void
     * @throws \Exception
     */
    public function handle(): void
    {
        $this->modelDir = $this->option('modelDir') ?? config('laravel-model-ts-type.model_dir');
        $this->namespace = $this->option('namespace') ?? config('laravel-model-ts-type.namespace');
        $this->outputDir = $this->option('outputDir') ?? config('laravel-model-ts-type.output_dir');
        $this->useKebabCase = !($this->option('noKebabCase') ?? config('laravel-model-ts-type.no_kebab_case'));
        $this->getModels($this->modelDir);

        foreach ($this->modelHits as $model) {
            $namespace = format_namespace($model);

            if (!in_array($namespace, get_declared_classes())) {
                include($model);
            }

            $reflectionClass = new \ReflectionClass($namespace);

            if ($reflectionClass->isAbstract() || array_key_exists('GENERATE_TYPE', $reflectionClass->getConstants())) {
                continue;
            }

            $actualModel = new $namespace;

            if ($actualModel instanceof Model) {
                $propertyDefinition = $this->createPropertyDefinition($actualModel);
                $this->writeToTsFile($model, $propertyDefinition);
            }
        }
    }

    /**
     * Recursively get all models from the directory path
     *
     * @param string $directoryPath
     * @return void
     * @throws InvalidPathException
     */
    private function getModels(string $directoryPath): void
    {
        try {
            foreach (new \DirectoryIterator($directoryPath) as $file) {
                if ($file->isDir() && !$file->isDot()) {
                    $this->getModels($file->getPathname());
                } else if (!$file->isDot()) {
                    $this->modelHits[] = $file->getPathname();
                }
            }
        } catch (\UnexpectedValueException $exception) {
            throw new InvalidPathException('Could not find the given directory');
        }
    }

    /**
     * Create all different property definitions
     *
     * @param Model $model
     * @return array
     */
    private function createPropertyDefinition(Model $model): array
    {
        $propertyDefinition = [];

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
     * Write the given model to a TypeScript file
     *
     * @param string $model
     * @param array $propertyDefinition
     * @return void
     */
    private function writeToTsFile(string $model, array $propertyDefinition): void
    {
        $sanitizedString = str_replace(unify_path($this->modelDir) . '/', '', unify_path($model));
        $locationSegments = explode('/', $sanitizedString);
        $modelName = str_replace('.php', '', array_pop($locationSegments));
        $className = $this->useKebabCase ? kebab_case($modelName) : $modelName;
        $fullPath = $this->outputDir . '/' . implode('/', $locationSegments);

        if (!File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }

        $fileContents = $this->formatContents($className, $propertyDefinition);

        File::put($fullPath . '/' . $className . '.d.ts', $fileContents);
    }

    /**
     * Format the contents for the TypeScript file
     *
     * @param string $className
     * @param array $propertyDefinition
     * @return string
     */
    private function formatContents(string $className, array $propertyDefinition)
    {
        $indent = $this->namespace ? "    " : "";
        $baseString = '';

        if ($this->namespace) {
            $baseString = 'declare namespace ' . $this->namespace . ' {' . PHP_EOL;
        }
        $baseString .= $indent . 'type ' . ucfirst(camel_case($className)) . ' = {' . PHP_EOL;

        foreach ($propertyDefinition as $key => $value) {
            $baseString .= $indent . "    " . $key . $value['operator'] . ' ' . $value['value'] . ';' . PHP_EOL;
        }

        if ($this->namespace) {
            $baseString .= $indent . '}' . PHP_EOL;
        }

        return $baseString . '};' . PHP_EOL;
    }
}
