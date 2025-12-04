#!/bin/bash

# =============================================
# MyBBA Deployment Script untuk VPS/Cloud
# Script ini membantu setup otomatis di VPS Ubuntu
# =============================================

set -e  # Exit on error
set -u  # Exit on undefined variables

echo "🚀 MyBBA Deployment Script"
echo "================================"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "❌ Script ini harus dijalankan sebagai root"
    echo "   Gunakan: sudo bash deploy.sh"
    exit 1
fi

# Variables
PROJECT_DIR="/var/www/mybba"
DB_NAME="dbsekolah"
DB_USER="mybba_user"
DOMAIN=""

# Prompt for configuration
echo "📝 Konfigurasi Deployment"
echo "================================"
read -p "Domain (atau tekan Enter untuk skip): " DOMAIN
read -p "Database username [mybba_user]: " input_db_user
DB_USER=${input_db_user:-$DB_USER}

# Generate random password
DB_PASS=$(openssl rand -base64 12)
echo ""
echo "✅ Database password akan di-generate otomatis"
echo ""

# Update system
echo "📦 Updating system packages..."
apt update && apt upgrade -y

# Install Nginx
echo "🔧 Installing Nginx..."
apt install nginx -y

# Install PHP 8.2
echo "🐘 Installing PHP 8.2..."
apt install software-properties-common -y
add-apt-repository ppa:ondrej/php -y
apt update
apt install php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-gd -y

# Install MySQL
echo "🗄️ Installing MySQL..."
apt install mysql-server -y

# Secure MySQL installation (non-interactive)
echo "🔒 Securing MySQL installation..."
# Set root password and secure MySQL
mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${DB_PASS}';"
mysql -u root -p"${DB_PASS}" -e "DELETE FROM mysql.user WHERE User='';"
mysql -u root -p"${DB_PASS}" -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');"
mysql -u root -p"${DB_PASS}" -e "DROP DATABASE IF EXISTS test;"
mysql -u root -p"${DB_PASS}" -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';"
mysql -u root -p"${DB_PASS}" -e "FLUSH PRIVILEGES;"

# Create database and user
echo "📊 Creating database..."
mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# Clone or update repository
if [ -d "$PROJECT_DIR" ]; then
    echo "📂 Updating existing project..."
    cd $PROJECT_DIR
    git pull
else
    echo "📥 Cloning repository..."
    cd /var/www
    git clone https://github.com/dadannf/mybba.git
fi

# Import database
echo "📊 Importing database..."
if [ -f "$PROJECT_DIR/database/backups/dbsekolah.sql" ]; then
    mysql -u $DB_USER -p$DB_PASS $DB_NAME < $PROJECT_DIR/database/backups/dbsekolah.sql
    echo "✅ Database imported successfully"
else
    echo "⚠️ Database backup file not found. Please import manually."
fi

# Update configuration
echo "⚙️ Updating configuration..."
cat > $PROJECT_DIR/public/.env << EOF
DB_HOST=localhost
DB_USER=$DB_USER
DB_PASS=$DB_PASS
DB_NAME=$DB_NAME
APP_ENV=production
EOF

# Set permissions
echo "🔐 Setting file permissions..."
chown -R www-data:www-data $PROJECT_DIR
chmod -R 755 $PROJECT_DIR
chmod -R 775 $PROJECT_DIR/public/uploads

# Create Nginx configuration
echo "🌐 Configuring Nginx..."
if [ -z "$DOMAIN" ]; then
    SERVER_NAME="_"
else
    SERVER_NAME="$DOMAIN www.$DOMAIN"
fi

cat > /etc/nginx/sites-available/mybba << EOF
server {
    listen 80;
    server_name $SERVER_NAME;
    
    root $PROJECT_DIR/public;
    index index.php index.html;
    
    access_log /var/log/nginx/mybba_access.log;
    error_log /var/log/nginx/mybba_error.log;
    
    location / {
        try_files \$uri \$uri/ =404;
    }
    
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }
    
    client_max_body_size 10M;
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Enable site
ln -sf /etc/nginx/sites-available/mybba /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Test Nginx configuration
nginx -t

# Restart services
echo "🔄 Restarting services..."
systemctl restart nginx
systemctl restart php8.2-fpm
systemctl restart mysql

# Enable services on boot
systemctl enable nginx
systemctl enable php8.2-fpm
systemctl enable mysql

# Setup firewall
echo "🔥 Configuring firewall..."
ufw allow 'Nginx Full'
ufw allow OpenSSH
echo "y" | ufw enable

# Print success message
echo ""
echo "================================"
echo "✅ Deployment completed successfully!"
echo "================================"
echo ""
echo "📋 Database Credentials:"
echo "   Database: $DB_NAME"
echo "   Username: $DB_USER"
echo "   Password: $DB_PASS"
echo ""
echo "⚠️ PENTING: Simpan password database di tempat yang aman!"
echo ""

if [ -z "$DOMAIN" ]; then
    IP=$(curl -s ifconfig.me)
    echo "🌐 Akses aplikasi di: http://$IP"
else
    echo "🌐 Akses aplikasi di: http://$DOMAIN"
    echo ""
    echo "📝 Setup SSL dengan Let's Encrypt:"
    echo "   sudo apt install certbot python3-certbot-nginx -y"
    echo "   sudo certbot --nginx -d $DOMAIN -d www.$DOMAIN"
fi

echo ""
echo "🔐 Default Login:"
echo "   Admin: admin / admin123"
echo "   ⚠️ Segera ubah password default setelah login!"
echo ""
echo "================================"
