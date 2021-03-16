Seman
=====

This is Seman, a simple server manager for SNMP-based services.

## Installing
Getting the website up and running is a simple operation that can be done in a minute or two.

The installation procedure has been tested on Ubuntu 20.04.2 LTS, other distributions and even different versions of Ubuntu itself might not behave in the same way, feel free to look up the dependencies yourself if you feel like these instructions don't work out for you.

 - Create a database called `seman` (if you want to use another name, remember to supply it through the environment variables later), then, execute the provided SQL script (find it at `/resources`).
 - Install the optional dependencies to host and query SNMP servers yourself: `sudo apt install snmp snmp-mibs-downloader snmpd`.
 - Install the required dependencies to query and talk to SNMP servers through PHP: `sudo apt install php-intl php-snmp`.
 - Restart the server: `sudo service apache2 restart` or `sudo systemctl restart apache2`.
 - Change to the root of the project and get the dependencies by executing the following command: `composer install`.
 - Edit your virtual host file and add these values as environment variables. An example for Apache would look like this:


   <div style="text-align:center"><img src="https://i.imgur.com/9wGU1pk.png" /></div>

   **IMPORTANT!** If you want to provide a database password, just define `MYSQL_PASSWORD` too, it'll be detected.
 - Once you're done, save the file, restart the server and you're good to go!

## Features
The following list provides information about the currently supported features:

 - Add, edit and remove servers.
 - Query servers for their processes and sessions count history and last update.

## Usage
Once you're done with the installation, just head to your hostname (usually `localhost`) and try to reach the server.

With the provided options, you should start by clicking `Add server` and following the form instructions.

## Resources
Give it a try! Import the database from the **resources** folder and start playing around.

## License
This project is just a quick real-world implementation of the a server manager, feel free to grab some examples for your own projects.

Licensed under the [MIT license](LICENSE).