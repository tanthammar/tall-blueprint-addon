# WIP - DON'T INSTALL, yet!

# TALL-forms Blueprint Addon
#### This package is based on [Blueprint Nova Addon](https://github.com/Naoray/blueprint-nova-addon) by [Krishan König](https://github.com/naoray).

Installing this addon will allow you to generate Tall-forms for all models with the `php artisan blueprint:build` command.

## Requirements
* tall-forms >= v7.8.4
* blueprint >= 1.20


## Installation
* Install Laravel, Livewire and TALL-forms
* Then install this package and **Blueprint** via composer:

```bash
composer require --dev tanthammar/tall-blueprint-addon
```

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
            notify: post.author ReviewPost with:post
            send: ReviewPost to:post.author with:post
            validate: title, content
            save: post
            redirect: post.index
            fire: NewPost with:post
        update:
            dispatch: SyncMedia with:post

        destroy:
            flash: post.title
            send: PostDeleted to:post.author with:post

    Comment:
        resource
```

## Configuration
You may publish the configuration with the following command:

```bash
php artisan vendor:publish --tag=tall-blueprint-config
```

### Timestamp fields
To disable the generation of `timestamp` fields for all forms set this option to `false`.

## Contribution
This is open source, I'll gladly accept every effort to contribute.

## Credits

- [Krishan König](https://github.com/naoray) for [Blueprint Nova Addon](https://github.com/Naoray/blueprint-nova-addon)
- [Jason McCreary](https://github.com/jasonmccreary) for [Blueprint](https://github.com/laravel-shift/blueprint)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
