FROM forumone/drupal8:7.4-xdebug AS dev

FROM forumone/drupal8:7.4 AS base

FROM forumone/composer:1.9 AS deps

COPY ["scripts", "scripts"]

RUN mkdir -p web

COPY ["composer.json", "composer.lock", "./"]

RUN set -ex \
  && composer install --no-dev --optimize-autoloader \
  && composer drupal:scaffold

FROM base

COPY --from=deps ["/app/scripts", "scripts"]

COPY --from=deps ["/app/vendor", "vendor"]

COPY --from=deps ["/app/web", "web"]

COPY ["web", "web"]

COPY ["config", "config"]

COPY ["drush", "drush"]

COPY ["load.environment.php", "./"]