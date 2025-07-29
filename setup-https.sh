#!/usr/bin/env bash
#
# Laravel Boilerplate HTTPS Setup Script
# This script sets up HTTPS with a custom domain for local development
#

set -o errexit

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}ℹ️  $1${NC}"
}

# Detect docker-compose command
if command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE="docker-compose"
elif docker compose version &> /dev/null 2>&1; then
    DOCKER_COMPOSE="docker compose"
else
    print_error "Neither 'docker-compose' nor 'docker compose' command found."
    print_info "Please install Docker Compose and try again."
    exit 1
fi

# Check if docker is running
if ! docker info > /dev/null 2>&1; then
    print_error "Docker is not running. Please start Docker and try again."
    exit 1
fi

# Welcome message
echo ""
echo "🚀 Laravel Boilerplate HTTPS Setup"
echo "=================================="
echo ""

# Get domain from user
read -p "Enter your custom domain (default: laravel.test): " DOMAIN
DOMAIN=${DOMAIN:-laravel.test}

echo ""
print_info "Setting up HTTPS for domain: $DOMAIN"
echo ""

# Make scripts executable
chmod +x ./docker/bin/setup-ssl-ca
chmod +x ./docker/bin/setup-ssl
chmod +x ./docker/bin/setup-domain

# Check if .env exists
if [ ! -f .env ]; then
    print_info "Creating .env file from .env.example..."
    cp .env.example .env
fi

# Ensure WWWGROUP is set
if ! grep -q "^WWWGROUP=" .env; then
    echo "WWWGROUP=1000" >> .env
    print_info "Added WWWGROUP=1000 to .env file"
fi

# Ensure WWWUSER is set
if ! grep -q "^WWWUSER=" .env; then
    echo "WWWUSER=1000" >> .env
    print_info "Added WWWUSER=1000 to .env file"
fi

# Export variables for docker-compose
export WWWGROUP=${WWWGROUP:-1000}
export WWWUSER=${WWWUSER:-1000}

# Build containers
print_info "Building Docker containers..."
$DOCKER_COMPOSE build

# Start containers
print_info "Starting Docker containers..."
$DOCKER_COMPOSE up -d

# Wait for containers to be ready
print_info "Waiting for containers to be ready..."
chmod +x ./docker/bin/wait-for-container
./docker/bin/wait-for-container nginx
./docker/bin/wait-for-container laravel.test

# Setup domain and SSL
print_info "Setting up domain and SSL certificates..."
./docker/bin/setup-domain "$DOMAIN"

# Install CA on host system
print_info "Installing Certificate Authority on your system..."
print_info "You will be prompted for your system password to trust the certificates."
./docker/bin/setup-ssl-ca

# Final message
echo ""
echo "========================================="
print_success "Setup complete!"
echo ""
echo "Your application is available at:"
echo "  🔒 https://$DOMAIN"
echo ""
echo "⚠️  IMPORTANT: You must restart your browser now!"
echo "   - Completely quit your browser (Cmd+Q on Mac, Alt+F4 on Windows/Linux)"
echo "   - Then reopen it and visit https://$DOMAIN"
echo "   - You should see a green padlock 🔐"
echo ""
echo "Useful commands:"
echo "  make shell          - Access Laravel container"
echo "  make logs           - View container logs"
echo "  make down           - Stop containers"
echo "  make up             - Start containers"
echo ""
echo "For more commands: make help"
echo "========================================="
