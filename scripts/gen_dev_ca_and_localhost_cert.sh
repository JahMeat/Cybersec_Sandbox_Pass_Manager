#!/usr/bin/env bash
set -euo pipefail

mkdir -p certs

# 1) Dev CA (private key + cert) - LOCAL ONLY
openssl genrsa -out certs/dev_ca.key 2048
openssl req -x509 -new -nodes -key certs/dev_ca.key -sha256 -days 365 \
  -subj "/C=US/ST=WA/L=Local/O=DevCA/OU=Local/CN=Dev Root CA" \
  -out certs/dev_ca.crt

# 2) Localhost key + CSR
openssl genrsa -out certs/localhost.key 2048
openssl req -new -key certs/localhost.key \
  -subj "/C=US/ST=WA/L=Local/O=LocalDev/OU=App/CN=localhost" \
  -out certs/localhost.csr

# 3) SAN extension (modern clients require SAN)
cat > certs/localhost.ext <<'EOF'
subjectAltName = DNS:localhost,IP:127.0.0.1
keyUsage = digitalSignature, keyEncipherment
extendedKeyUsage = serverAuth
EOF

# 4) Sign CSR with dev CA
openssl x509 -req -in certs/localhost.csr \
  -CA certs/dev_ca.crt -CAkey certs/dev_ca.key -CAcreateserial \
  -out certs/localhost.crt -days 365 -sha256 \
  -extfile certs/localhost.ext

echo "Generated certs in ./certs:"
ls -1 certs/dev_ca.crt certs/localhost.crt certs/localhost.key

# Run it using the command below:
# chmod +x scripts/gen_dev_ca_and_localhost_cert.sh
# ./scripts/gen_dev_ca_and_localhost_cert.sh

