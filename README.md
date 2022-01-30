# Installing LAMP stack CRUD app on EC2

## Creating Parameter Store 
We want to start off by placing some database secrets in aws Paramater Store. 
This way we're not using them directly in the bash scripts so they aren't saved to the bash history. 

Create 2 paramaters with the following names (and random values)

```
projectshare_db_root_user_pass
projectshare_db_user_pass
```

## Create IAM Role For EC2 to access resources 
In order for the ec2 to access the paramater store and inject the values into the bash scripts, it needs to have permission to access that aws resource. 

Create a new IAM Role called `projectshare`, which will later attached to the ec2. (we create the policy in the next step).

## Create IAM policy (for the role) allowing access to Parameter Store Values 
Create a brand new policy for this above role, having only this policy attached to it, to limit the access of the ec2 to other aws resources.

Call the policy `projectshare`, and give it the following JSON policy. Replace the 
`YOUR-ACCOUNT-ID` with your aws account id. (You can find your account ID by clicking on the "My" AWS Account dropdown in the top right of your aws. Copy without the dashes)

```
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "VisualEditor0",
            "Effect": "Allow",
            "Action": [
                "ssm:ListTagsForResource",
                "ssm:GetParametersByPath",
                "ssm:GetParameters",
                "ssm:GetParameter"
            ],
            "Resource": "arn:aws:ssm:us-west-2:YOUR-ACCOUNT-ID-WITHOUT-DASHES:parameter/projectshare_*"
        }
    ]
}
```

## Create EC2
1. Add “User Data Script” below to the user data section on ec2 creation
2. Create security group on ec2 with port 80, 443 open to the world, and port 22 to just “My IP”
3. Attach the IAM role, that was created above, to the ec2
4. When prompted, create new private key and download for use in connecting via ssh later.
5. Launch the ec2

```
#!/bin/bash
export AWS_DEFAULT_REGION=us-west-2
yum update -y
sudo yum install jq -y
sudo amazon-linux-extras install -y lamp-mariadb10.2-php7.2 php7.2
sudo yum install -y httpd mariadb-server
sudo systemctl start httpd
sudo systemctl enable httpd
sudo usermod -a -G apache ec2-user
sudo chown -R ec2-user:apache /var/www
sudo chmod 2775 /var/www
find /var/www -type d -exec chmod 2775 {} \;
find /var/www -type f -exec chmod 0664 {} \;
sudo systemctl enable mariadb.service
sudo systemctl start mariadb.service
MYSQLROOTPASS=$(aws ssm get-parameter --name "projectshare_db_root_user_pass" --with-decryption | jq -r .Parameter.Value)
DBPASS=$(aws ssm get-parameter --name "projectshare_db_user_pass" --with-decryption | jq -r .Parameter.Value)
mysql --host=localhost --user="root" << EOF
ALTER USER 'root'@'localhost' IDENTIFIED BY '${MYSQLROOTPASS}';
flush privileges;
CREATE USER 'projectshare'@'localhost' IDENTIFIED BY '${DBPASS}';
CREATE DATABASE IF NOT EXISTS projectshare;
GRANT ALL PRIVILEGES ON projectshare . * TO  'projectshare'@'localhost';
flush privileges;

CREATE TABLE `users` (
  `id` int(11) AUTO_INCREMENT PRIMARY KEY,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `bio` text NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `timezone` varchar(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE `posts` (
  `id` int(11) AUTO_INCREMENT PRIMARY KEY,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `posted_time` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS loves (
    `id` int(11) AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `post_id` INT NOT NULL
)  ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS comments (
    `id` int(11) AUTO_INCREMENT PRIMARY KEY,
    `comment` TEXT,
    `user_id` INT NOT NULL,
    `post_id` INT NOT NULL,
    `posted_time` INT NOT NULL
)  ENGINE=INNODB;

EOF
```
## SSH Connect to your EC2
Once your ec2 is launched, ssh into it to execute any bash commands in the below sections

## Changing SSH port to non standard port 
```
#locate the line that reads #Port 22, then change it, uncomment it, save and exit
sudo vi /etc/ssh/sshd_config

# restart sshd service to apply port change
sudo systemctl restart sshd
```

## Upload the web application
1. Clone this repo locally
2. Create an env.ini file in the root of the project with the following contents (make sure the password is the userdb password from your paramater store):
```
[db]
servername = "localhost";
username = "projectshare";
password = "";
dbname = "projectshare";
```
3. Download and install FileZilla (Or some other ftp transfer client. If you don't have it already) -> [FileZilla Download Link](https://filezilla-project.org/download.php)
4. Connect to your ec2 with your ftp client, and upload all the files into the `/var/www/` folder (minus the hidden .git folder)
5. The app should be working over non ssl (http traffic). Visit your `Public IPV4 DNS` name in your browser to check it out. 

## Adding Elastic IP
- In the EC2 dashboard area in aws, find `Elastic IPs` in the left side menu.
- Get an IP in your region and click “Allocate IP” button on bottom of page
- Click on Elastic IP checkbox > Actions > Associate Elastic IP Address (and associate it with your EC2)

## Create New Domain on Route 53
NOTE: If you have a name server on another host, you can either forward the Name Server to aws Route 53, Transfer the Domain to aws Route 53, or simply create an A record on your exiting DNS for your Elastic IP.

(Skip these if you made an A record in your exsting DNS from non aws)
- Search in the AWS services bar for “Route 53”
- Select Register Domain, and purchase domain
- Select “Create hosted zone” and add an A record pointing to your Elastic IP

## Adding SSL to your server
NOTE: In order to do SSL you must purchase your own custom domain, and Elastic IP, and attach them to the EC2.

Run the following bash commands, and make following edits to files, based on the code below

```
#Make sure port 443 is allowed via security group!
#visit public domain name prepended with https://
sudo systemctl is-enabled httpd
sudo systemctl start httpd && sudo systemctl enable httpd
sudo yum install -y mod_ssl
cd /etc/pki/tls/certs
sudo ./make-dummy-cert localhost.crt

#edit file and comment out this line by adding # at beginning of line
sudo vi /etc/httpd/conf.d/ssl.conf
#SSLCertificateKeyFile /etc/pki/tls/private/localhost.key

sudo systemctl restart httpd

#Make sure port 443 is allowed via security group!
#visit public domain name prepended with https://
#Probably will display series of warnings, but if you can get through that means it's working!

#Install Let's Encrypt SSL with, Automated renewal, using Let's Encrypt Certbot
cd /home/ec2-user
sudo wget -r --no-parent -A 'epel-release-*.rpm' https://dl.fedoraproject.org/pub/epel/7/x86_64/Packages/e/
sudo rpm -Uvh dl.fedoraproject.org/pub/epel/7/x86_64/Packages/e/epel-release-*.rpm
sudo yum-config-manager --enable epel*
sudo yum repolist all

#edit file. Look for "Listen 80" and add following lines underneath it
sudo vi /etc/httpd/conf/httpd.conf
<VirtualHost *:80>
    DocumentRoot "/var/www/html"
    ServerName "example.com"
    ServerAlias "www.example.com"
</VirtualHost>

#save and exit file ^

#Restart apache
sudo systemctl restart httpd

#install and run Certbot
sudo amazon-linux-extras install epel -y
sudo yum install -y certbot python2-certbot-apache

#run the certbot, and complete steps
sudo certbot

#Once it says "Congratulations" make sure to copy the paths to certificate and 
#keyfile it provides in the Important Notes
/etc/letsencrypt/live/example.com/fullchain.pem
/etc/letsencrypt/live/example.com/privkey.pem

#test site in browser over https://
#Optionally harden security based on aws recommendations:
#https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/SSL-on-amazon-linux-2.html#ssl_test

#Configure certifacte renewal automation
#Edit crontab and add below line to crontab
sudo crontab -e
39      1,13    *       *       *       root    certbot renew --no-self-upgrade

#restart cron daemon
sudo systemctl restart crond
```

### Documentation referenced in this tutorial
- [Tutorial: Install a LAMP web server on Amazon Linux 2](https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/ec2-lamp-amazon-linux-2.html) 

- [Tutorial: Configure SSL/TLS on Amazon Linux 2](https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/SSL-on-amazon-linux-2.html) 
