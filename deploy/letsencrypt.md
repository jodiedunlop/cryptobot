```bash
sudo apt-get update -y
sudo apt-get install software-properties-common -y
sudo add-apt-repository ppa:certbot/certbot -y
sudo apt-get update -y
sudo apt-get install python-certbot-nginx -y 

sudo certbot certonly --nginx --webroot --webroot-path=/var/www/cryptobot -d cryptobot.meow.com.au
```