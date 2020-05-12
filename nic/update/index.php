<?php
/**
 * Script for updating dynamic DNS entries on a PowerDNS server
 *
 * This script follows the dyndns2 protocol for receiving dynamic DNS update requests
 * from an update client/router.  Currently it can update a hostname with a single IPv4
 * address.  It does not support mx, backmx, wildcard, and offline parameters at this time.
 * 
 * @author      Jason R. Pitoniak <jasonATpitoniakDOTcom>
 * @copyright   2020 Jason R. Pitoniak
 * @license     https://opensource.org/licenses/MIT The MIT License
 * @link        https://github.com/jpitoniak/diy-ddns/
 */

// require config file - script will fail if not present
require('../../config.php');

// check for user authentication
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="diy-ddns"');
    header('HTTP/1.0 401 Unauthorized');
    header("Content-type: text/plain");
    echo 'Unauthorized';
    exit;
}

// if auth credentials are present, verify they are correct
if($_SERVER['PHP_AUTH_USER'] !== $config['user']['username'] || !password_verify($_SERVER['PHP_AUTH_PW'], $config['user']['password'])) {
    header('HTTP/1.0 403 Forbidden');
    header('Content-type: text/plain');
    echo 'badauth';
    exit;
}

//is an IP address passed in the query sting? if not, use the client's IP
$ip = (isset($_GET['myip'])) ? $_GET['myip'] : $_SERVER['REMOTE_ADDR'];

// verify that the $ip is a valid, routable IPv4 address
$ip = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);

if($ip === false) {
    header('HTTP/1.0 422 Unprocessable Entity');
    header('Content-type: text/plain');
    echo 'badsys';
    exit;
}

// check if hostname is in allowed hosts list
$hostname = (isset($_GET['hostname'])) ? $_GET['hostname'] : false;
$hostname = filter_var($hostname, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
if($hostname === false || !in_array($hostname, $config['allowed_hosts'])) {
    header('HTTP/1.0 422 Unprocessable Entity');
    header('Content-type: text/plain');
    echo 'nohost';
    exit;
}

// set up db connection
try {
    $db = new mysqli($config['db']['host'],
          $config['db']['user'],
          $config['db']['pass'],
          $config['db']['schema'],
          $config['db']['port']
    );
}
catch(Exception $e) {
    header('HTTP/1.0 500 Script Error');
    header('Content-type: text/plain');
    echo 'dnserr';
    exit;
}

// check whether hostname's current ip is same as requested ip
$curIP = null;
$stCheck = $db->prepare("SELECT content FROM records WHERE name=? AND TYPE='A' LIMIT 1");
if($stCheck !== false) {
    $stCheck->bind_param('s', $hostname);
    $stCheck->execute();
    $stCheck->bind_result($curIP);
    $stCheck->fetch();
    $stCheck->close();
}

if($ip == $curIP) {
    // ip addressess match
    $db->close();
    header('Content-type: text/plain');
    echo 'nochg ' . $ip;
    exit;
}

// update the ip
$stChg = $db->prepare("UPDATE records SET content=? WHERE name=? AND type='A'");
if($stChg !== false) {
    if($stChg->bind_param('ss', $ip, $hostname)) {
        if($stChg->execute()) {
            $stChg->close();
            $db->close();
            header('Content-type: text/plain');
            echo 'good ' . $ip;
            exit;
        }
    }
}

header('HTTP/1.0 500 Script Error');
header('Content-type: text/plain');
echo 'dnserr';
