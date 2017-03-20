# Symlink Handler

[![Build Status](https://travis-ci.org/kporras07/composer-symlinks.svg?branch=master)](https://travis-ci.org/kporras07/composer-symlinks)

Composer script handling creation of symlinks inside your composer project.

*Note:* Symlinks will become files copy when composer is run with `--no-dev`

# Installation
Installation can be done as usually using composer.
`composer require kporras07/composer-symlinks`

# Usage
Add the following in your root `composer.json` file:

```php
"require": {
    "kporras07/composer-symlinks": "dev-master"
},
"scripts": {
    "post-install-cmd": [
        "Kporras07\\ComposerSymlinks\\ScriptHandler::createSymlinks"
    ],
    "post-update-cmd": [
        "Kporras07\\ComposerSymlinks\\ScriptHandler::createSymlinks"
    ]
},
"extra": {
    "symlinks": {
        "components": "web/components"
    }
}
```

After running either `composer install` or `composer update`, components folder will be accessible from your web folder `web/components/`.

But of course, you have to be careful when making symlinks to a folder which is publicly accessible.
