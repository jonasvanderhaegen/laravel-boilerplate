FROM nginx:1.24-alpine
LABEL maintainer="Laravel Boilerplate"

# Install dependencies
RUN apk add --no-cache \
    ca-certificates \
    openssl \
    curl \
    nss \
    wget \
    bash

# Install mkcert with architecture detection
RUN ARCH=$(uname -m) && \
    if [ "$ARCH" = "x86_64" ]; then \
        MKCERT_ARCH="amd64"; \
    elif [ "$ARCH" = "aarch64" ]; then \
        MKCERT_ARCH="arm64"; \
    else \
        echo "Unsupported architecture: $ARCH" && exit 1; \
    fi && \
    cd /tmp && \
    wget "https://github.com/FiloSottile/mkcert/releases/download/v1.4.4/mkcert-v1.4.4-linux-${MKCERT_ARCH}" && \
    chmod +x "mkcert-v1.4.4-linux-${MKCERT_ARCH}" && \
    mv "mkcert-v1.4.4-linux-${MKCERT_ARCH}" /usr/local/bin/mkcert

# Create directories
RUN mkdir -p /etc/nginx/certs /var/www/html /root/.local/share/mkcert

# Copy nginx configuration
COPY nginx.conf /etc/nginx/nginx.conf
COPY conf/default.conf /etc/nginx/conf.d/default.conf

# Create a startup script
COPY scripts/docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

# Copy certificate generation script
COPY scripts/generate-cert.sh /usr/local/bin/generate-cert
RUN chmod +x /usr/local/bin/generate-cert

EXPOSE 80 443

ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["nginx", "-g", "daemon off;"]
