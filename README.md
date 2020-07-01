# laravel-model-ts-type
Generate TypeScript types based on your models.

## Usage
### Installation

Install the package using composer:
```
composer require scrumble-nl/laravel-model-ts-type
```
### Generating types
```
php artisan types:generate {--modelDir=} {--outputDir=}
```
Additional options:

| Option         | Default value                                                                                   | Description                         |
|--------------|:----------------------------------------------------------------------------------------:|:-------------------------------------|
| `modelDir`      | `app/Models`                    | The root directory where the package can find all Laravel models          |
| `outputDir`       | `resources/js/models`                  | The root directory for outputting the `.d.ts` files             |

If you want to change the default values for `modelDir` and `outputDir` you can publish the config file and change it to you liking.

NOTE: Do not forget to add the directory to your typeroots in `tsconfig.json`

## Roadmap

- [ ] Generate types for packagized models
- [ ] Create command to generate type for 1 model
- [ ] Implement unqualified name for relation doc blocks

## Contributing
If you would like to see additions/changes to this package you are always welcome to add some code or improve it.

## Scrumble
This product has been originally developed by [Scrumble](https://www.scrumble.nl) for internal use. As we have been using lots of open source packages we wanted to give back to the community. We hope this helps you getting forward as much as other people helped us!
