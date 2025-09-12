# Content Security Policy Presentation
Presentation on Content Security Policy (https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP),
with example Drupal site implementation.

This repository is a demonstration of how content security policy works, and how
you can configure it in a Drupal website. Warning, this is not actively maintained,
and some of the packages and modules may be out of date. Even when this was posted
to this repository, the f1 cli dependency was deprecated.


# Requirements

To set up the local environment, do the following:

1. Install [`docker`](https://docs.docker.com/install/) and [`docker-compose`](https://docs.docker.com/compose/install/), if not already installed. It is also recommended to update to the latest version.

2. Install [`DDEV`](https://ddev.readthedocs.io/en/stable/#installation)


# Set up the project

Start ddev, install packages, and import the provided database.

`ddev start \
ddev composer install ;\
zcat database.sql.gz | ddev drush sqlc
`

Set up database:
in services/drupal/web/sites/default
Copy default.settings.php to settings.php and paste the following at the bottom:

```
$settings['config_sync_directory'] = '../config/sync';


$config['attachinline.settings']['csp-allow-method'] = 'nonce';
```

Once complete, if set up was successful, you should be able to visit https://csp.ddev.site/
and see a Drupal website. A revealjs presentation is bundled under /pres.

To log into the site, run f1 drush uli
