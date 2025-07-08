#!/bin/bash

# MaxCon ERP - GitHub Deployment Script
# This script prepares and pushes your project to GitHub

echo "🚀 MaxCon ERP - GitHub Deployment Script"
echo "========================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Check if git is installed
if ! command -v git &> /dev/null; then
    print_error "Git is not installed. Please install Git first."
    exit 1
fi

print_status "Git is installed"

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_error "This doesn't appear to be a Laravel project directory."
    print_info "Please run this script from your MaxCon ERP project root."
    exit 1
fi

print_status "Laravel project detected"

# Initialize git repository if not already done
if [ ! -d ".git" ]; then
    print_info "Initializing Git repository..."
    git init
    print_status "Git repository initialized"
else
    print_status "Git repository already exists"
fi

# Create .gitignore if it doesn't exist
if [ ! -f ".gitignore" ]; then
    print_info "Creating .gitignore file..."
    cat > .gitignore << 'EOF'
/node_modules
/public/hot
/public/storage
/storage/*.key
/vendor
.env
.env.backup
.env.production
.phpunit.result.cache
docker-compose.override.yml
Homestead.json
Homestead.yaml
npm-debug.log
yarn-error.log
/.idea
/.vscode
/database/database.sqlite
/storage/logs/*.log
EOF
    print_status ".gitignore created"
fi

# Add environment example
if [ ! -f ".env.example" ]; then
    print_info "Creating .env.example..."
    cp .env .env.example
    # Remove sensitive data from example
    sed -i.bak 's/APP_KEY=.*/APP_KEY=/' .env.example
    sed -i.bak 's/DB_PASSWORD=.*/DB_PASSWORD=your_database_password/' .env.example
    sed -i.bak 's/MAIL_PASSWORD=.*/MAIL_PASSWORD=your_mail_password/' .env.example
    rm .env.example.bak 2>/dev/null || true
    print_status ".env.example created"
fi

# Get GitHub repository URL
echo ""
print_info "Please provide your GitHub repository details:"
read -p "Enter your GitHub username: " github_username
read -p "Enter repository name (default: maxcon-erp-saas): " repo_name

# Set default repository name if not provided
if [ -z "$repo_name" ]; then
    repo_name="maxcon-erp-saas"
fi

github_url="https://github.com/${github_username}/${repo_name}.git"

print_info "Repository URL: $github_url"

# Check if remote origin already exists
if git remote get-url origin &> /dev/null; then
    print_warning "Remote 'origin' already exists"
    current_origin=$(git remote get-url origin)
    print_info "Current origin: $current_origin"
    
    read -p "Do you want to update the origin URL? (y/n): " update_origin
    if [ "$update_origin" = "y" ] || [ "$update_origin" = "Y" ]; then
        git remote set-url origin "$github_url"
        print_status "Origin URL updated"
    fi
else
    git remote add origin "$github_url"
    print_status "Remote origin added"
fi

# Stage all files
print_info "Staging files for commit..."
git add .

# Check if there are changes to commit
if git diff --staged --quiet; then
    print_warning "No changes to commit"
else
    # Commit changes
    echo ""
    read -p "Enter commit message (default: 'Initial commit: MaxCon ERP SaaS System'): " commit_message
    
    if [ -z "$commit_message" ]; then
        commit_message="Initial commit: MaxCon ERP SaaS System"
    fi
    
    git commit -m "$commit_message"
    print_status "Changes committed"
fi

# Push to GitHub
echo ""
print_info "Pushing to GitHub..."
print_warning "You may be prompted for your GitHub credentials"

# Set main branch as default
git branch -M main

# Push to GitHub
if git push -u origin main; then
    print_status "Successfully pushed to GitHub!"
    echo ""
    print_info "Your repository is now available at:"
    echo "🔗 https://github.com/${github_username}/${repo_name}"
    echo ""
    print_info "Next steps:"
    echo "1. 🌐 Set up your Cloudways server"
    echo "2. 🔗 Connect GitHub to Cloudways"
    echo "3. 🚀 Deploy your application"
    echo ""
    print_info "Refer to CLOUDWAYS_DEPLOYMENT_GUIDE.md for detailed instructions"
else
    print_error "Failed to push to GitHub"
    print_info "Please check your GitHub credentials and repository settings"
    echo ""
    print_info "Manual steps:"
    echo "1. Create repository on GitHub: https://github.com/new"
    echo "2. Repository name: $repo_name"
    echo "3. Run: git push -u origin main"
fi

echo ""
print_status "Script completed!"
