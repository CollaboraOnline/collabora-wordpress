


## Upload size

To increase the maximum upload size (it is indicated on that page),
you need to increase the value in the PHP configuration.

Usually you can add a file `max_file_size.ini` (the name isn't much
important except its extension should be `.ini`) into
`/etc/php/conf.d/` (the path may be different) and put the following:

```
post_max_size = 30M
upload_max_filesize = 30M
```

These set the limits to a maximum of 30M. You can change as appropriate.
