# Zeacurity
Parse the server logs and extracts the IP address with bad intentions and block it using iptables

## Installation
- download this package
- place it somewhere safe
- import the `db.sql` file
- run the full scan, sample command below
- add it to the cron, sample below

## Usage
```bash
php ./console.php [command-name] [params]
```

### run full scan
```bash
php {path}/console.php auth_blacklist --log-file=/var/log/auth.log --full-scan=y
```

### run every 2 hours, but I recommend every hour
```bash
0 */2 * * * php {path}/console.php auth_blacklist --log-file=/var/log/auth.log --lines=1500 > /dev/null 2>&1 
```

### Whitelist IPs
You can whitelist IP address to exempt from black listing. see `whitelist.ip.json.sample` file and rename it as `whitelist.ip.json`

### DB credentials
The db credentials are stored in `db.conf.json`