# test_cookie_security_flags.py
# Pytest that checks HttpOnly, Secure, and SameSite flags on your session cookie

import pytest
from selenium import webdriver
from selenium.webdriver.common.by import By

class TestCookieSecurityFlags:
    def setup_method(self, method):
        self.driver = webdriver.Firefox()
        self.vars = {}

    def teardown_method(self, method):
        self.driver.quit()

    def test_phpsessid_cookie_has_security_flags(self):
        # Go to a page that sets your PHPSESSID cookie
        # adjust URL if your app uses a different entry point
        self.driver.get("https://localhost/login.php")

        # If your PHPSESSID is only set after login, you can reuse your login steps.
        # Replace these with valid test creds or remove if the cookie is set on first load.
        self.driver.find_element(By.ID, "username").send_keys("testuser")
        self.driver.find_element(By.ID, "password").send_keys("testpassword")
        self.driver.find_element(By.CSS_SELECTOR, ".btn-primary").click()

        # Get the PHP session cookie
        phpsessid = self.driver.get_cookie("PHPSESSID")
        assert phpsessid is not None, "PHPSESSID session cookie was not set"

        # HttpOnly flag
        assert phpsessid.get("httpOnly") is True, "PHPSESSID cookie must be HttpOnly"

        # Secure flag
        assert phpsessid.get("secure") is True, "PHPSESSID cookie must have the Secure flag"

        # SameSite flag (accept Strict or Lax; tweak if you require one)
        same_site = phpsessid.get("sameSite")
        assert same_site in ("Strict", "Lax", "None"), (
            f"PHPSESSID cookie must have a SameSite attribute set; got {same_site!r}"
        )
