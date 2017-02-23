# Symlink Handler

[![Build Status](https://travis-ci.org/vaniocz/symlink-handler.svg?branch=master)](https://travis-ci.org/vaniocz/symlink-handler)
[![Coverage Status](https://coveralls.io/repos/github/vaniocz/symlink-handler/badge.svg?branch=master)](https://coveralls.io/github/vaniocz/symlink-handler?branch=master)
![PHP7](https://img.shields.io/badge/php-7-6B7EB9.svg)
[![License](https://poser.pugx.org/vanio/symlink-handler/license)](https://github.com/vaniocz/symlink-handler/blob/master/LICENSE)

Composer script handling creation of symlinks from your selected installed dependencies to location of your choice.

# Installation
Installation can be done as usually using composer.
`composer require vanio/symlink-handler`

# Usage
Add the following in your root `composer.json` file:

```php
"require": {
    "vanio/symlink-handler": "dev-master",
    "components/jquery": "2.2.1"
},
"scripts": {
    "post-install-cmd": [
        "Vanio\\SymlinkHandler\\ScriptHandler::createSymlinks"
    ],
    "post-update-cmd": [
        "Vanio\\SymlinkHandler\\ScriptHandler::createSymlinks"
    ]
},
"extra": {
    "symlinks": {
        "components": "web/vendor/components"
    }
}
```

After running either `composer install` or `composer update`, jquery will be accessible from your web folder `web/vendor/components/jquery/jquery.min.js`.

But of course, you have to be careful when making symlinks to a folder which is publicly accessible.
