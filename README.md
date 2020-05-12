# diy-ddns
Do-It-Yourself Dynamic DNS for PowerDNS servers

After years of dealing with dynamic DNS providers that were unreliable, or that started charging outraeous fees, or that made me jump through crazy hoops to keep my hostnames active, I finally had an epiphany: I run my own DNS servers, so why not run my own dynamic DNS, too.  There's noting special about dynamic DNS hostnames, they're just A records with short times-to-live.

The hardest part about running my own dynamic DNS? Getting updates to the DNS seervers any time my IP address changed.  I'm running [PowerDNS](https://www.powerdns.com/) with a MySQL back end, so updating the IP is as simple as a SQL query, but how do I notify the server?  I wanted to do it in a way that my router would understand so that I could continue using the built-in update client.  The led me to the source code of [ddclient](https://sourceforge.net/projects/ddclient/), from which I was able to reverse engineer the dyndns2 protocol.  Dyndns2 is a simple, HTTP-based updater protocol originally developed by DynDNS (now Dyn), but also used by many other dynamic DNS providers.  Since it's natively supported by nearly every router on the market, it seemed like a no-brainer to implement dyndns2 for my server.

This project is specifically designed to work with a PowerDNS DNS server with a MySQL backend, but it could be easily updated to work with other DNS servers and/or providers that offer a public API for DNS updates.

## Requirements

This package requires a web server capable of running PHP and a recent version of PHP that has mysqli support.  You'll also need a PowerDNS server that is the primary host for any domains you'll be creating dynamic records on and that server needs to be using a MySQL backend for those domains.

## Installation

You should choose your dynamic host name(s) and add them to the DNS zone through whatever means you normally use to manage your domains.  To ensure that IP changes are picked up quickly, set the time-to-live (TTL) value to a short interval, such as 60 to 300 seconds (1 to 5 minutes).

I recommend using one dynamic hostname per site.  If you want to have multiple hostnames point at one dynamic IP address, I recommend using CNAME records to point the additional hostnames to the one dynamic host.  CNAME records should use a longer TTL (43200 seconds, or 12 hours, is common).

These instructions reflect a basic Apache configuration, with the updater being installed in /var/www/html.  You'll need to adjust to fit your server, as appropriate.  I recommend that your use https, if possible, when calling the updater, so that passwords are not transmitted in the clear.  Setting up https is not part of this tutorial.

1. Switch to your website's root directory.
```cd /var/www/html```
2. Clone this repository and place it in a web-accessible location on your server.
```git clone https://github.com/jpitoniak/diy-ddns.git```
3. Copy/rename the config-sample.php file to config.php
```cd diy-ddns
cp config-spample.php config.php```
4. Edit config.php following the comments in the file
```vi config.php```
5. Configure your router or update client to notify this application when an IP change occurs.  You can call the script directly with the URL `http://{username}:{password}@{your-site.com}/diy-ddns/nic/update?hostname={your-dynamic-host.com}&myip={your.new.ip.address}` (i.e. `https://me:monkey123@example.com/diy-ddns/nic/update?hostname=home.example.com&myip=123.45.67.89`)

### Notes

* Refer to the update client's documentation for configuration instructions.  Most clients will allow you to speficy a full URL path, rather than just a domain name, if you are unable to install this script at the root leavel of your web server.
* Many update clients will automatically add `/nic/update` to the end of the URL you specify, but some will not.  You may need to experiment with the URL you use to get things working.
* It is not necessary to specify an IP address in the update request.  If you do not, the IP of the calling client will be used.  This can be helpful if your update client runs from inside the local network and is unable to detect the external IP of the router.
* The script only supports setting hostnames currently.  Other features supported by Dyn, such as `mx`, `backmx`, `wildcard`, and `offline` are not supported.  The script will only support a single IP address in the `hostname` paramerer, as well. 


## To Do

* Switch from using SQL to the PowerDNS API
* Add support for multiple users
* Allow per-domain shared secrets instead of using the account password for updates
* Develop a plugin system to allow for other DNS backends
