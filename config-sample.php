<?php
/*************************I***********************************************
 * Sample configuration file for diy-ddns                                *
 *                                                                       *
 * Copy or rename this file to config.php before editing.                *
 *                                                                       *
 * Follow the instructions in the comments to configure your server.     *
 *************************************************************************/

/*
 * Database credentials
 *
 * Provide the configuraton credentials for the MySQL/MariaDB database
 * holding your DNS records
 */

// Databse host name
$config['db']['host'] = 'localhost';

// Databse port number
$config['db']['port'] = 3306;

// Databse user account
$config['db']['user'] = 'ENTER_MYSQL_USERNAME';

// Databse user password
$config['db']['pass'] = 'ENTER_MYSQL_PASSWORD';

// Database name
$config['db']['schema'] = 'ENTER_POWERDNS_DATABASE_NAME';

/*
 * User credentials
 *
 * Create a username and password that will be used to authenticate
 * the update client when it submits a change
 */
 
// Username
$config['user']['username'] = 'ENTER_USERNAME';

// Password hash
// Use one of these commands to create a hash of your desired password:
//     mkpasswd -m SHA-256
//     mkpasswd -m SHA-512
//     php -r 'echo password_hash(readline("Password: "), PASSWORD_DEFAULT) . "\n";'
//
// Be sure to enclose the hash in single quotes or it won't be interpreted correctly
$config['user']['password'] = 'ENTER_PASSWORD_HASH';

/*
 * Hostnames
 *
 * Enter all of the hostnames you'll allow to be updated using the update client
 *
 * Preexisting A records are required in the DNS server database.  TTLs should be set
 * to a low value (60-300)
 */
$config['allowed_hosts'][] = 'dynanichost.domain.com';
$config['allowed_hosts'][] = 'dynamichost2.domain.com';

/*
 * DO NOT CHANGE ANYTHING BELOW THIS LINE
 */

if ($_SERVER['SCRIPT_FILENAME'] == __FILE__) {
    header('HTTP/1.0 403 Forbidden');
    hrader('Content-type: text/plain');
    echo('This file is not directly accessible.');
}
