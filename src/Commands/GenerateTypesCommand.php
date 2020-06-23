<?php

namespace Scrumble\TypeGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Scrumble\TypeGenerator\Exceptions\InvalidPathException;
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
    protected $signature = 'types:generate {--modelDir=} {--outputDir=}';

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
        $this->outputDir = $this->option('outputDir') ??  config('laravel-model-ts-type.output_dir');
        $this->getModels($this->modelDir);

        foreach ($this->modelHits as $model) {
            $namespace = str_replace('.php', '', preg_replace('/\//', '\\', str_replace(base_path() . '/a', 'A', $model)));
            if (!in_array($namespace, get_declared_classes())) {
                include($model);
            }

            $actualModel = new $namespace;
            $propertyDefinition = $this->createPropertyDefinition($actualModel);
            $this->writeToTsFile($model, $propertyDefinition);
        }
    }

    /**
     * Recursively get all models from the directory path
     *
     * @param  string $directoryPath
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
                    // TODO: check if it is an actual model (check if it extends Model)

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
     * @param  $model
     * @return array
     */
    private function createPropertyDefinition($model): array
    {
        $propertyDefinition = [];

        $propertyDefinition = array_merge($propertyDefinition, $this->databaseGenerator->getPropertyDefinition($model));
        $propertyDefinition = array_merge($propertyDefinition, $this->relationGenerator->getPropertyDefinition($model));
        $propertyDefinition = array_merge($propertyDefinition, $this->attributeGenerator->getPropertyDefinition($model));
        $this->removeHiddenFieldsFromPropertyDefinition($model, $propertyDefinition);

        return $propertyDefinition;
    }

    /**
     * @param $model
     * @param array $propertyDefinition
     * @throws \ReflectionException
     */
    private function removeHiddenFieldsFromPropertyDefinition($model, array &$propertyDefinition)
    {
        $reflectionClass = new \ReflectionClass($model);
        $hiddenProperty = $reflectionClass->getProperty('hidden');
        $hiddenProperty->setAccessible(true);
        $hiddenFields = $hiddenProperty->getValue($model);

        foreach ($hiddenFields as $hiddenField) {
            unset($propertyDefinition[$hiddenField]);
        }
    }

    /**
     * Write the given model to a TypeScript file
     *
     * @param $model
     * @param array $propertyDefinition
     * @return void
     */
    private function writeToTsFile($model, array $propertyDefinition): void
    {
        $sanitizedString = preg_replace('/\/-/', '/', kebab_case(str_replace($this->modelDir . '/', '', $model)));
        $locationSegments = explode('/', $sanitizedString);
        $className = str_replace('.php', '', array_pop($locationSegments));
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
     * @param  string $className
     * @param  array $propertyDefinition
     * @return string
     */
    private function formatContents(string $className, array $propertyDefinition)
    {
        $baseString = 'type ' . ucfirst(camel_case($className)) . ' = {' . PHP_EOL;

        foreach ($propertyDefinition as $key => $value) {
            $baseString .= "\t" . $key . $value['operator'] . ' ' . $value['value'] . PHP_EOL;
        }

        return $baseString . '};';
    }
}
