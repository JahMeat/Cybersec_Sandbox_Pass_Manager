import pytest
import requests
from urllib.parse import urlparse

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.desired_capabilities import DesiredCapabilities


class TestHttpsAndPort443:
    def setup_method(self, method):
        # You can switch to Chrome() or another driver if you want
        self.driver = webdriver.Firefox()
        self.vars = {}

    def teardown_method(self, method):
        self.driver.quit()

    def test_https_and_port_443_and_status_200(self):
        # Change this to whatever page you want to check
        base_url = "https://localhost:443/login.php"

        # --- Selenium part: open the page ---
        self.driver.get(base_url)
        self.driver.set_window_size(1936, 1048)

        # Get the current URL actually loaded by the browser
        current_url = self.driver.current_url

        # --- Parse URL to check scheme and port ---
        parsed = urlparse(current_url)

        # Assert HTTPS
        assert parsed.scheme == "https", f"Expected HTTPS, got {parsed.scheme}"

        # Determine port; if not explicitly set, default for https is 443
        port = parsed.port or 443
        assert port == 443, f"Expected port 443, got {port}"

        # --- Use requests to check HTTP status code ---
        # If you're using a self-signed cert in dev, you may need verify=False
        response = requests.get(current_url, verify=False)
        assert (
            response.status_code == 200
        ), f"Expected status code 200, got {response.status_code}"

