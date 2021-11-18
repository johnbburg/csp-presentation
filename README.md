# csp-presentation
Presentation on Content Security Policy, with example Drupal site implementation. 

This repository is a demonstration of how content security policy works, and how 
you can configure it in a Drupal website. Warning, this is not actively maintained, 
and some of the packages and modules may be out of date. Even when this was posted 
to this repository, the f1 cli dependency was deprecated.


# Requirements:

Docker

The f1 cli https://github.com/forumone/forumone-cli

# Set up the project 

Build docker
`f1 build ; f1 up`

Composer install in `services/drupal` directory.

Set up database:
in services/drupal/web/sites/default
Copy default.settings.php to settings.php and paste the following at the bottom:

```
$settings['config_sync_directory'] = '../config/sync';


$config['attachinline.settings']['csp-allow-method'] = 'nonce';

$databases['default']['default'] = array (
  'database' => 'web',
  'username' => 'web',
  'password' => 'web',
  'prefix' => '',
  'host' => 'mysql',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
```

Import the database file committed to the root of this repository:

`zcat database.sql.gz | f1 drush sqlc`

Once complete, if set up was successful, you should be able to visit http://localhost:8080 
and see a Drupal website. A revealjs presentation is bundled under /pres.

To log into the site, run f1 drush uli
