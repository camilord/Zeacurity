# Zeacurity
Parse the server auth logs and extracts the IP address with bad intentions and block it using `iptables`

## Installation
- download this package
- place it somewhere safe, like root folder as you will be running this as root
- run composer, `php composer.phar install` - to install the packages/vendor
- create a database in MySQL, let's say the name is `zeacurity` then import the `db.sql` file
- copy `db.conf.json.sample` to `db.conf.json` and edit the file to enter the DB credentials
- optional: copy `whitelist.conf.json.sample` to `whitelist.conf.json` and edit the file then define what IP addresses you want to whitelist or never add to blacklist
- locate your firewall script (using iptables or see sample `firewall.sh.sample`)
- add this line: `# {SSH_BLOCK_IPS}` -- means the Zeacurity will insert on that area (see sample `firewall.sh.sample`)
- run the full scan, sample command below
- add it to the cron, sample below

## Usage
```bash
php ./console.php [command-name] [params]
```

### run full scan
```bash
php {path}/console.php auth_blacklist --log-file=/var/log/auth.log --full-scan=y
php {path}/console.php append_blacklister --firewall-file=./firewall.sh
```

### cron configuration
- for the auth.log scanner (`auth_blacklist`), recommend to run frequently like every 10-15 mins
- while for the iptables generation, recommended an every hour or two

```bash
*/15 * * * * php {path}/console.php auth_blacklist --log-file=/var/log/auth.log --lines=1500 > /dev/null 2>&1
0 */2 * * * php {path}/console.php append_blacklister --firewall-file=./firewall.sh > /dev/null 2>&1
```

### Whitelist IPs
You can whitelist IP address to exempt from black listing. see `whitelist.ip.json.sample` file and rename it as `whitelist.ip.json`

### DB credentials
The db credentials where being stored in `db.conf.json` (from `db.conf.json.sample`)

### Tested OS
- Ubuntu 20.x
