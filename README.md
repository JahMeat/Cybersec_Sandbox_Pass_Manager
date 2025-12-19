# Overview

UW HusKey Manager is a dockerized secrets management sandbox designed
for penetration testing, vulnerability remediation, and secure
development practice.

The project explores security concepts commonly found in
secrets-management and PAM-adjacent systems, including encryption,
audit logging, authentication hardening, and defense-in-depth.

This repository is intentionally scoped as an educational sandbox and
does not represent a full enterprise PAM platform.

For educational penetration testing purposes, the application
contains a single intentionally weak credential used to demonstrate
authentication flaws and remediation workflows.

The credential is documented in the vulnerability report and should
not be reused outside this sandbox environment.
```
username: username
password: password!
```

# Set Up

1. (Windows User Only) Download WSL and install Ubuntu, a linux distribution. 

    ```bash
    wsl --install -d Ubuntu
    sudo apt update && sudo apt upgrade -y
    ```
    You can cd to your c drive then your user account:
    ```bash
    cd /mnt/c/Users/<your_user_account>
    ```

2. Download Docker Desktop right [here](https://www.docker.com/products/docker-desktop/)


3. Call this new file `.env`. Within this file, copy and paste the following information:

    ```
    MYSQL_PORT: 3306
    MYSQL_PASSWORD: supersecretpw
    MYSQL_DATABASE: password_manager
    MYSQL_USER: user
    BUILD_TARGET: backend-php-server
    ```

4. (Optional) Create a [Loggly](https://loggly.com) account to enable centralized logging.

    Each user must provide their own Loggly customer token via `.env`.
    Tokens are not shared, committed, or exposed by this repository.

    ```
    LOGGLY_TOKEN=<your_personal_token>
    ```

5. Generate a local, development-only Certificate Authority (CA) and
localhost certificate to enable HTTPS.

    This step mimics PKI behavior for educational purposes only.
    All keys and certificates are generated locally, ignored by Git,
    and never shared.

    (Windows users should run this inside Ubuntu via WSL.)

    ```bash
    chmod +x scripts/gen_dev_ca_and_localhost_cert.sh
    ./scripts/gen_dev_ca_and_localhost_cert.sh
    ```

6. Run the application which will be hosted on https://localhost:443

    ```bash
    docker-compose up --build
    ```

# Bug Bounty

This project encourages responsible security testing within the
sandboxed environment.

Testers are encouraged to:
- Identify vulnerabilities
- Document impact and reproduction steps
- Implement and validate remediations

Known vulnerabilities are documented [here](docs/Recorded_Vulnerabilities.pdf).
Fork the repository to explore, test, and remediate additional issues.

## Remediations

- SQL Injection using `' OR 1=1 -- ` or user-specific passwordless login `Admin' AND 1=1 -- ` is no longer possible. 

- Session cookie always and only uses `PHPSESSID` for authentication. Cookie can no longer be manipulated by the client. 

- Session cookie must have `HttpOnly`, `Secure`, and `SameSite` flags.

- Encryption standards are enforced by using `https` on `port 443`

- Prevention security control against brute force attacks enforced by locking out users for multiple failed attempts and detected by Loggly. 

## Testing

All remediation tests are executed locally using pytest.

Some tests were initially recorded using Selenium IDE (Firefox),
then validated through automated pytest runs.

```bash
pip install pytest
pytest ./pytest/
```

# Notes

This project is intentionally scoped as an educational security sandbox.
All vulnerabilities, credentials, and cryptographic materials are
development-only and confined to the local environment.

The goal of this repository is to encourage hands-on exploration of
security failures, remediation techniques, and secure system design
patterns commonly encountered in real-world environments.

Feedback, responsible testing, and thoughtful discussion are welcome.
