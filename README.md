# Mako Framework

[![Build Status](https://api.travis-ci.org/marcomontalbano/mako-framework.svg?branch=master)](https://travis-ci.org/marcomontalbano/mako-framework)
[![Latest Stable Version](https://img.shields.io/github/release/marcomontalbano/mako-framework.svg)](https://github.com/marcomontalbano/mako-framework/releases)

This fork adds support for **PHP 7** to [Mako Framework](https://github.com/mako-framework/framework) 3.x.

If you are currently use this old version, you can now update to Mako Framework 3.7 and use PHP 7.

## How to update

As suggested by [@freost](https://github.com/mako-framework/framework/pull/221#issuecomment-310623621) (i asked for an official release) you just need to update your `composer.json`.

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/marcomontalbano/mako-framework"
        }
    ],
    "require": {
        "mako/framework": "3.7.*",
    },
}
```

That's it! Run `composer update` and start to play with PHP 7.
