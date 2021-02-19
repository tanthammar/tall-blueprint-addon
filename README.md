# TALL-forms Blueprint Addon
Auto generate [TALL-forms](https://github.com/tanthammar/tall-forms/wiki) for all models with the `php artisan blueprint:build` command.

#### This plugin is based on [Blueprint Nova Addon](https://github.com/Naoray/blueprint-nova-addon) by [Krishan König](https://github.com/naoray).


[![Latest Stable Version](https://poser.pugx.org/tanthammar/tall-blueprint-addon/v)](//packagist.org/packages/tanthammar/tall-blueprint-addon)
[![Total Downloads](https://poser.pugx.org/tanthammar/tall-blueprint-addon/downloads)](//packagist.org/packages/tanthammar/tall-blueprint-addon)
[![Latest Unstable Version](https://poser.pugx.org/tanthammar/tall-blueprint-addon/v/unstable)](//packagist.org/packages/tanthammar/tall-blueprint-addon)

# What you get
* **Code**: Consider the code you get as a mockup/draft. **It won't work as is**. You'll have to review and finalize the field declarations.
  <br><br>
* **Usability**: You will get a single form component for each model. 
  It's up to you to split it in two components if you need separate forms for create/update forms.
  <br><br>
* **Tests**: The Blueprint generated tests matches the controllers, not tall-forms. You will have to update them to Livewire syntax.
  <br><br>
* **Duplicated code:** <br>Any Controller statements in your `draft.yaml` related to `store`, `update` and `destroy`, will be written to both Controllers and the Livewire form components.
  To avoid code duplication you can manually remove the code in Controllers after the build command. Another suggestion is to refactor into `Actions` that you can use in both Controllers, and the Livewire form components.
  <br><br>
  Controller => TallForm, duplicated code position:
  <br>
  * Controller->store() => TallForm->onCreateModel()
  * Controller->update() => TallForm->onUpdateModel()
  * Controller->destroy() => TallForm->onDeleteModel()
    <br><br>
* S**ponsors**: If you are a sponsor, the build command will generate sponsor fields instead of open source versions. Like `DatePicker` instead of `Input->type('datetime-local')`. See the configuration option below.

# Early version!
* Relationship fields are outputted as `Repeaters`, `Selects` or `MultiSelect`. This will change when I create required fields in TALL-forms
* Review generated code, it's not perfect :)

## Requirements
* tall-forms >= v7.8.4
* blueprint >= 1.20


## Installation
* Install Laravel, Livewire and TALL-forms
* Then install this package and **Blueprint** via composer:

```bash
composer require --dev tanthammar/tall-blueprint-addon
```

## Configuration
You may publish the configuration with the following command:

```bash
php artisan vendor:publish --tag=tall-blueprint-config
```

## Sponsors - update config!
If you are a sponsor of tall-forms, publish the config file and set `sponsor` to `true`. 
If not, please sponsor the tall-forms package here: https://github.com/sponsors/tanthammar
```php
//Do you have access to the tall-forms-sponsor repository?
'sponsor' => true,
```

### Timestamp fields
To disable the generation of `timestamp` fields for all forms set this option to `false`.


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

controllers:
    Post:
        index:
            query: all
            render: post.index with:posts
        create:
            render: post.create
        store:
            validate: title, content, author_id
            save: post
            dispatch: SyncMedia with:post
            notify: post.author ReviewPost with:post
            send: ReviewPost to:post.author with:post
            flash: post.title
            fire: NewPost with:post
            redirect: post.index
        update:
            update: post
            dispatch: SyncMedia with:post

        destroy:
            flash: post.title
            send: SupportPostDeleted to:support with:post
            delete: post
            redirect: post.index

    Comment:
        resource

```

## Contribution
This is open source, I'll gladly accept every effort to contribute.

## Credits

- [Krishan König](https://github.com/naoray) for [Blueprint Nova Addon](https://github.com/Naoray/blueprint-nova-addon)
- [Jason McCreary](https://github.com/jasonmccreary) for [Blueprint](https://github.com/laravel-shift/blueprint)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
