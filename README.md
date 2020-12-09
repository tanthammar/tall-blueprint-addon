# WIP - DON'T INSTALL, yet!

# TALL-forms Blueprint Addon
#### This package is based on [Blueprint Nova Addon](https://github.com/Naoray/blueprint-nova-addon) by [Krishan König](https://github.com/naoray).

Installing this addon will allow you to generate Tall-forms for all models with the `php artisan blueprint:build` command.

## Installation
* Install Laravel, Livewire and TALL-forms
* Then install this package and **Blueprint** via composer:

```bash
composer require --dev tanthammar/tall-blueprint-addon
```

> :warning: You need to have [tall-forms](https://github.com/tanthammar/tall-forms/) installed!

## Usage
Refer to [Blueprint's Basic Usage](https://github.com/laravel-shift/blueprint#basic-usage) 
to get started. Afterwards you can run the `blueprint:build` command to 
generate Tall-forms automatically. Try this example `draft.yaml` file.

```yaml
# draft.yaml
models:
  Post:
    author_id: id foreign:users
    title: string:400
    content: longtext
    published_at: nullable timestamp
    relationships:
      HasMany: Comment

  Comment:
    post_id: id foreign
    content: longtext
    published_at: nullable timestamp
```

## Configuration
You may publish the configuration with the following command:

```bash
php artisan vendor:publish --tag=tall-blueprint-config
```

### Timestamp fields
To disable the generation of `timestamp` fields for all Nova resources set this option to `false`.

## Credits

- [Krishan König](https://github.com/naoray) for [Blueprint Nova Addon](https://github.com/Naoray/blueprint-nova-addon)
- [Jason McCreary](https://github.com/jasonmccreary) for [Blueprint](https://github.com/laravel-shift/blueprint)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
