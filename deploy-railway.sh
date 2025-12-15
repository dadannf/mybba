#!/usr/bin/env bash
# Railway Deployment Script for MyBBA

set -e

echo "🚀 Railway Deployment Started..."
echo "================================"

# Check if Railway CLI is installed
if ! command -v railway &> /dev/null; then
    echo "❌ Railway CLI not found!"
    echo "📥 Install: npm i -g @railway/cli"
    echo "   Or: curl -fsSL https://railway.app/install.sh | sh"
    exit 1
fi

echo "✅ Railway CLI found"

# Check required files
echo ""
echo "🔍 Checking required files..."
required_files=("Dockerfile" "composer.json" "composer.lock" "railway.toml")
for file in "${required_files[@]}"; do
    if [ -f "$file" ]; then
        echo "  ✅ $file"
    else
        echo "  ❌ $file not found!"
        exit 1
    fi
done

# Login to Railway
echo ""
echo "🔐 Logging in to Railway..."
railway login

# Link or create project
echo ""
echo "📂 Railway Project Setup..."
echo "   Choose one:"
echo "   1) Link existing project: railway link"
echo "   2) Create new project: railway init"
read -p "   Enter choice (1/2): " choice

if [ "$choice" = "1" ]; then
    railway link
elif [ "$choice" = "2" ]; then
    railway init
else
    echo "❌ Invalid choice"
    exit 1
fi

# Check environment variables
echo ""
echo "🔧 Environment Variables Check..."
echo "   Make sure these are set in Railway Dashboard:"
echo "   - DB_HOST"
echo "   - DB_DATABASE"
echo "   - DB_USERNAME"
echo "   - DB_PASSWORD"
echo "   - APP_ENV=production"
echo "   - APP_DEBUG=false"
echo ""
read -p "   Environment variables configured? (y/n): " env_ready

if [ "$env_ready" != "y" ]; then
    echo "⚠️  Please configure environment variables first"
    echo "   Railway Dashboard → Your Project → Variables"
    exit 1
fi

# Deploy
echo ""
echo "🚀 Deploying to Railway..."
railway up

echo ""
echo "✅ Deployment Complete!"
echo ""
echo "📊 Next steps:"
echo "   1. Check logs: railway logs"
echo "   2. Open app: railway open"
echo "   3. Import database: railway run bash"
echo "      Then: mysql -h \$DB_HOST -u \$DB_USERNAME -p\$DB_PASSWORD \$DB_DATABASE < database/backups/dbsekolah.sql"
echo ""
echo "🎉 Done!"
