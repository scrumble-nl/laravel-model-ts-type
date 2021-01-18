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
| `namespace`       | `false`                  | The namespace of the generated Types. Use `false` if you don't prefer to have one             |

If you want to change the default values you can publish the config file and change it to you liking.

### What does it do?

This package takes (almost) all of Laravel's magic into account. It follows these steps for generating a Type:
1. Retrieve all fields from the database (only MySQL/MariaDB supported) and map them to default types (string, number, etc.)
2. Add relations for the Model, they will point to the related generated Type
3. Add the attribute getters for the Model
4. Check the `casts` attribute
5. Remove all fields that are in the `hidden` attribute

### Example output

```typescript
type Company = {
    id: number;
    name: string;
    created_at: string /* Date */ | null;
    updated_at: string /* Date */ | null;
    slug: string;
    welcome_message: string | null;
    contact_information: string | null;
    main_color: string | null;
    logo_src: string | null;
    user_field: any[];
    language: any[];
    team_site: any[];
    is_api_enabled: boolean;
    kaizen_user_field: string;
    faqs?: Faq[] | null;
    users?: User[] | null;
    team_properties: TeamProperty[] | null;
    editor_images: EditorImage[] | null;
    meta_data?: any[];
};
```

## Roadmap

- [ ] Add tests (in progress)
- [ ] Generate types for packagized models
- [ ] Create command to generate type for 1 model
- [ ] Implement unqualified name for relation doc blocks

## Contributing
If you would like to see additions/changes to this package you are always welcome to add some code or improve it.

## Scrumble
This product has been originally developed by [Scrumble](https://www.scrumble.nl) for internal use. As we have been using lots of open source packages we wanted to give back to the community. We hope this helps you getting forward as much as other people helped us!
