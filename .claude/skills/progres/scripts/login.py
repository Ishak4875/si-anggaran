#!/usr/bin/env python3
"""
Login to dashboard and return authenticated session.

Usage:
  python login.py <email> <password> <app_url>

Returns:
  JSON with status, cookies, and auth_token
"""

import json
import sys
import time
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

def login(email: str, password: str, app_url: str = "http://127.0.0.1:8000"):
    """
    Login to Laravel dashboard using Selenium.

    Args:
        email: User email
        password: User password
        app_url: Base URL of the app

    Returns:
        dict with status, session cookies, and auth_token
    """

    driver = None
    try:
        # Launch browser
        options = webdriver.ChromeOptions()
        options.add_argument("--disable-blink-features=AutomationControlled")
        driver = webdriver.Chrome(options=options)
        driver.set_page_load_timeout(30)

        # Navigate to login page
        login_url = f"{app_url}/login"
        print(f"[1/4] Navigating to {login_url}...")
        driver.get(login_url)

        # Wait for email input
        print("[2/4] Waiting for email input...")
        email_field = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.NAME, "email"))
        )

        # Fill email and password
        print("[3/4] Filling credentials...")
        email_field.clear()
        email_field.send_keys(email)

        password_field = driver.find_element(By.NAME, "password")
        password_field.clear()
        password_field.send_keys(password)

        # Click login button
        login_button = driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        login_button.click()

        # Wait for redirect to dashboard
        print("[4/4] Waiting for dashboard...")
        WebDriverWait(driver, 15).until(
            lambda d: "/dashboard" in d.current_url or d.current_url == f"{app_url}/"
        )

        # Extract auth cookie
        cookies = driver.get_cookies()
        auth_cookie = None
        for cookie in cookies:
            if cookie['name'] == 'XSRF-TOKEN':
                auth_cookie = cookie['value']
                break

        print(f"✅ Login successful! Current URL: {driver.current_url}")

        return {
            "status": "success",
            "current_url": driver.current_url,
            "auth_cookie": auth_cookie,
            "all_cookies": cookies
        }

    except Exception as e:
        print(f"❌ Login failed: {str(e)}")
        return {
            "status": "error",
            "message": str(e)
        }

    finally:
        if driver:
            driver.quit()

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: python login.py <email> <password> [app_url]")
        sys.exit(1)

    email = sys.argv[1]
    password = sys.argv[2]
    app_url = sys.argv[3] if len(sys.argv) > 3 else "http://127.0.0.1:8000"

    result = login(email, password, app_url)
    print(json.dumps(result, indent=2))
