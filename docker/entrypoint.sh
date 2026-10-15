#!/bin/sh
set -e

# Foldery z conf/confe.docker.ini (logi, pliki tymczasowe importu, dokumenty do pobrania)
DANE=/var/lib/ewnioski
mkdir -p "$DANE/logs" "$DANE/tmp" "$DANE/pliki" "$DANE/pliki_kierownicy"
chown www-data:www-data "$DANE/logs" "$DANE/tmp"

# Konfiguracja wysyłki maili (msmtp) ze zmiennych SMTP_*
if [ -n "${SMTP_HOST:-}" ]; then
    {
        echo "defaults"
        echo "logfile -"
        echo "account default"
        echo "host ${SMTP_HOST}"
        echo "port ${SMTP_PORT:-25}"
        echo "from ${SMTP_FROM:-eWnioski.Admin@motherson.com}"
        echo "tls ${SMTP_TLS:-off}"
        if [ "${SMTP_TLS:-off}" = "on" ]; then
            echo "tls_starttls ${SMTP_STARTTLS:-on}"
            echo "tls_trust_file /etc/ssl/certs/ca-certificates.crt"
        fi
        if [ -n "${SMTP_USER:-}" ]; then
            echo "auth on"
            echo "user ${SMTP_USER}"
            echo "password ${SMTP_PASSWORD:-}"
        else
            echo "auth off"
        fi
    } > /etc/msmtprc
    chown root:www-data /etc/msmtprc
    chmod 640 /etc/msmtprc
else
    echo "eWnioski: SMTP_HOST nie jest ustawiony - maile nie będą wysyłane" >&2
fi

exec "$@"
