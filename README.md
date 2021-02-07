# Zeacurity
Parse the server auth logs and extracts the IP address with bad intentions and block it using `iptables`

## Installation
- download this package
- place it somewhere safe, like root folder as you will be running this as root
- import the `db.sql` file
- find your firewall script (using iptables)
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
The db credentials where being stored in `db.conf.json`