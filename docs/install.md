# Installation

## Install packages

sudo apt install nginx -y
sudo apt install php7.2-fpm php7.2-zip php7.2-mbstring php7.2-xml -y

## Clone or install to app directory

As `ubuntu` user:

```bash
cd /var/www
git clone https://github.com/jodiedunlop/cryptobot.git ./cryptobot
```

## Create cache dir

```bash
sudo mkdir -p /var/www/cryptobot/storage/framework/cache/data
```

## Change file perms

Set ubuntu as owner of the application directory:

```bash
sudo chown -R ubuntu:ubuntu /var/www/cryptobot
```

Allow `www-data` (which nginx runs as) to write to specific directories:

```bash
cd /var/www/cryptobot
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```

## Install dependencies

```bash
cd /var/www/cryptobot
composer install
```


## Configure Laravel

### Copy .env file

```bash
cp .env.example .env
```

Edit the .env file and change any settings

### Generate app key

```bash
php artisan key:generate
```

### Configure and Migrate Database

1. Configure DB settings in `.env`
2. Run migrations

#### Configure Sqlite

When using SQLite database:

```bash
touch database/cryptobot.sqlite
```

Edit `.env`:

```
DB_DRIVER=sqlite
DATABASE=/var/www/cryptobot/database/cryptobot.sqlite
```

#### Run Migrations

```bash
php artisan migrate
```

### Update coin list

```bash
php artisan coins:update
```

## Webserver setup

### Letsencrypt SSL certificate

Install certbot:

```bash
sudo apt-get update -y
sudo apt-get install software-properties-common -y
sudo add-apt-repository ppa:certbot/certbot -y
sudo apt-get update -y
sudo apt-get install python-certbot-nginx -y 
```

Create cert:

```bash
sudo certbot certonly --nginx -d cryptobot.meow.com.au
```