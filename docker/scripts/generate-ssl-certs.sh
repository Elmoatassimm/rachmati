#!/bin/bash

# ==============================================================================
# SSL Certificate Generation Script for Local Development
# ==============================================================================

set -e

# Configuration
DOMAIN="rachmat.local"
CERT_DIR="./docker/certs"
DAYS=365

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if OpenSSL is installed
if ! command -v openssl &> /dev/null; then
    print_error "OpenSSL is not installed. Please install it first."
    exit 1
fi

# Create certificate directory
print_status "Creating certificate directory: $CERT_DIR"
mkdir -p "$CERT_DIR"

# Generate private key
print_status "Generating private key for $DOMAIN..."
openssl genrsa -out "$CERT_DIR/$DOMAIN.key" 2048

# Create certificate signing request configuration
print_status "Creating certificate configuration..."
cat > "$CERT_DIR/$DOMAIN.conf" <<EOF
[req]
default_bits = 2048
prompt = no
default_md = sha256
distinguished_name = dn
req_extensions = v3_req

[dn]
C=DZ
ST=Algiers
L=Algiers
O=Rachmat Development
OU=IT Department
CN=$DOMAIN

[v3_req]
basicConstraints = CA:FALSE
keyUsage = nonRepudiation, digitalSignature, keyEncipherment
subjectAltName = @alt_names

[alt_names]
DNS.1 = $DOMAIN
DNS.2 = *.$DOMAIN
DNS.3 = localhost
DNS.4 = *.localhost
IP.1 = 127.0.0.1
IP.2 = ::1
EOF

# Generate certificate signing request
print_status "Generating certificate signing request..."
openssl req -new -key "$CERT_DIR/$DOMAIN.key" -out "$CERT_DIR/$DOMAIN.csr" -config "$CERT_DIR/$DOMAIN.conf"

# Generate self-signed certificate
print_status "Generating self-signed certificate (valid for $DAYS days)..."
openssl x509 -req -in "$CERT_DIR/$DOMAIN.csr" -signkey "$CERT_DIR/$DOMAIN.key" -out "$CERT_DIR/$DOMAIN.crt" -days $DAYS -extensions v3_req -extfile "$CERT_DIR/$DOMAIN.conf"

# Set proper permissions
chmod 600 "$CERT_DIR/$DOMAIN.key"
chmod 644 "$CERT_DIR/$DOMAIN.crt"

# Clean up temporary files
rm "$CERT_DIR/$DOMAIN.csr" "$CERT_DIR/$DOMAIN.conf"

# Verify certificate
print_status "Verifying certificate..."
openssl x509 -in "$CERT_DIR/$DOMAIN.crt" -text -noout | grep -E "(Subject:|DNS:|IP Address:)"

print_status "SSL certificates generated successfully!"
print_warning "Certificate location: $CERT_DIR/$DOMAIN.crt"
print_warning "Private key location: $CERT_DIR/$DOMAIN.key"
echo
print_warning "To trust this certificate in your browser:"
echo "1. Open your browser and navigate to https://$DOMAIN"
echo "2. Click 'Advanced' and then 'Proceed to $DOMAIN (unsafe)'"
echo "3. Or add the certificate to your system's trusted store"
echo
print_warning "For Chrome/Edge (Linux):"
echo "certutil -d sql:\$HOME/.pki/nssdb -A -t 'P,,' -n '$DOMAIN' -i '$CERT_DIR/$DOMAIN.crt'"
echo
print_warning "For Firefox:"
echo "Go to Settings > Privacy & Security > View Certificates > Import"
echo
print_warning "Don't forget to add '$DOMAIN' to your /etc/hosts file:"
echo "127.0.0.1 $DOMAIN" 