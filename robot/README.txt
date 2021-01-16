Full-Text RSS
=============

About
-----

See http://fivefilters.org/content-only/ for a description of the code.


Installation
------------

1. Extract the files in this ZIP archive to a folder on your computer.

2. FTP the files up to your server

3. Access index.php through your browser. E.g. http://example.org/full-text-rss/index.php

4. Enter a URL in the form field to test the code

5. If you get an RSS feed with full-text content, all is working well. :)

Configuration (optional)
------------------------

1. Save a copy of config.php as custom_config.php and edit custom_config.php

2. If you decide to enable caching, make sure the cache folder (and its 2 sub folders) is writable.
(You might need to change the permissions of these folders to 777 through your FTP client.)

3. If you want to use the admin area to edit/update your site config files, make sure the
site_config folder (and its 2 sub folders) is writable. (You might need to change the permissions
of these folders to 777 through your FTP client.)

Testing (optional, PHP 7 only)
------------------------------

You can test Full-Text RSS by trying to enter URLs into the form and checking the result.
But if you want to run our test suite, you should follow these steps on a new instance of 
Full-Text RSS, before making any changes to the configuration file. You will also need SSH access
to your server so you can execute commands from a terminal.

1. Download Codeception.phar from https://codeception.com/codecept.phar
and place it inside the root folder of Full-Text RSS (same directory as this README.txt)
> wget https://codeception.com/codecept.phar

2. Edit codeception.yml and change the URL if you've installed Full-Text RSS in a different 
location to the one shown.

3. Execute:
php codecept.phar run --steps

Help
----

Please visit http://help.fivefilters.org