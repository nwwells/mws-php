Setup
=====
The following describes how to setup this project on various platforms:

Debian
------

CentOS (tested on 6.2):
------
> *Note:* This is currently broken. I am getting a "System error (in pam_authenticate)"

1. Install yum packages

		yum install httpd php php-pear php-devel gcc pam-devel

2. Install the PAM php module from the PECL repository

		pecl install pam

3. Create a PAM configuration for PHP:

		# this copies the standard login configuration
		cp /etc/pam.d/login /etc/pam.d/php

4. Configure PHP to load the PAM module

		yum install httpd php php-pear php-devel gcc pam-devel

5. Restart Apache

		service httpd restart
