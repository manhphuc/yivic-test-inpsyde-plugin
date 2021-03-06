#!/bin/sh

source "$PWD/wp-content/plugins/yivic-test-inpsyde-plugin/.env"
#rm -f "$PWD/wp-config.php"
#
#wp config create \
#  --dbname="$DB_NAME" \
#  --dbuser="$DB_USER" \
#  --dbpass="$DB_PASSWORD" \
#  --dbhost="$DB_HOST" \
#  --force

wp core install \
  --url=$PROJECT_BASE_URL \
  --title="$PROJECT_NAME" \
  --admin_user="$WP_ADMIN_USER" \
  --admin_password="$WP_ADMIN_PASSWORD" \
  --admin_email="$WP_ADMIN_EMAIL"\
  --skip-email
wp plugin activate yivic-test-inpsyde-plugin
