# Collabora Online WP

This plugin allow the integration of Collabora Online with WordPress.

What you need is:

- WordPress. (currently 6.6.x is tested)
- Collabora Online. The latter can be run on any server that is accessible and that can access the
  WordPress server.

Collabora Online is an online office suite based on LibreOffice. It is open source, and is meant to
be hosted on premise. This WordPress plugin is designed to use WordPress as the store and
collaboration point to use Collabora Online. It will use the user credential of your WordPress
system to determine access to the documents for collaboration.

For more information see https://collaboraonline.com/

SECURITY NOTE: The plugin hasn't gone through a security review yet. The default mechanism to
upload office files make them world readable like any WordPress media upload.

## Configuration

Once the plugin is installed you can set the configuration using the Wordpress administration pages.

- _Collabora Online server URL_: the URL of the collabora online server. Note that you have to take
  into considerartion containers. If you run Wordpress in one container and Collabora Online in
  another, you can not use `localhost`.
- _WOPI host base URL_: how the Collabora Online server can reach the Wordpress server. Usually it
  is the public URL of this Wordpress server.
- _JWT Private Key_: the secret to create the JWT private key.

You can create a secret using the following shell command:

```shell
head -c 64 /dev/urandom | base64 -w 0
```

- _Disable TLS certificate check for COOL_: If you configure a development server you might have
  self-signed certificate. Checking this is **INSECURE** but allow the Wordpress server to contact
  the Collabora Online server if the certificate doesn't check.
- _Access Token Expiration_: In second the expiration of the token to access the document. Default
  to 86400 seconds (24 hours).

## Upload size

To increase the maximum upload size (it is indicated on that page), you need to increase the value
in the PHP configuration.

Usually you can add a file `max_file_size.ini` (the name isn't much important except its extension
should be `.ini`) into `/etc/php/conf.d/` (the path may be different) and put the following:

```
post_max_size = 30M
upload_max_filesize = 30M
```

These set the limits to a maximum of 30M. You can change as appropriate.

## PHP requirements

This plugin needs SimpleXML support support in PHP, which is usually standard. See
https://www.php.net/manual/en/simplexml.installation.php

## Internals

The plugin creates a new post type `collabora_revision` to store the revisions of files associated
to the attachment. Its parent is set to the attachment post. And there are two pieces of metadata
associated to it: `_wp_attached_file` and `collabora_rev_timestamp`. The former allow the standard
`get_attached_file()` call. The latter is the timestamp of the revision.

## Development

You need PHP `composer` and `npm`.

### Setup

`composer install` will install the project PHP dependencies.

### Building

`composer build` is used to build the JavaScript need for the Gutenberg block.

## License

This plugin is published under the MPL-2.0 license.

## Maintenance

This plugin is maintained by Collabora Productivity.
