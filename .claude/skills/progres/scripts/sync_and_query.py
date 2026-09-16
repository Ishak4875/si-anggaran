#!/usr/bin/env python3
"""
Click "Sinkron Data API" button, wait for sync completion, then query database.

Usage:
  python sync_and_query.py <db_host> <db_user> <db_password> <db_name> <app_url>

Returns:
  JSON with satker ranking, PPK ranking, and latest sync timestamp
"""

import json
import sys
import time
from datetime import datetime
import mysql.connector
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

SATKER_MAP = {
    "694133": "balai",
    "694134": "pjpa",
    "694135": "pjsa",
    "694137": "bendungan",
    "694136": "op"
}

SATKER_DISPLAY = {
    "balai": "Balai",
    "pjpa": "PJPA",
    "pjsa": "PJSA",
    "bendungan": "SNVT Bendungan",
    "op": "OP PSDA"
}

def click_sync_button(app_url: str = "http://127.0.0.1:8000"):
    """
    Click 'Sinkron Data API' button and wait for completion.
    """

    driver = None
    try:
        options = webdriver.ChromeOptions()
        options.add_argument("--disable-blink-features=AutomationControlled")
        driver = webdriver.Chrome(options=options)
        driver.set_page_load_timeout(30)

        # Navigate to dashboard
        dashboard_url = f"{app_url}/dashboard"
        print(f"[1/3] Navigating to {dashboard_url}...")
        driver.get(dashboard_url)

        # Wait for sync button to be clickable
        print("[2/3] Waiting for sync button...")
        sync_button = WebDriverWait(driver, 15).until(
            EC.element_to_be_clickable((By.XPATH, "//button[contains(text(), 'Sinkron Data API')]"))
        )

        print("[3/3] Clicking sync button and waiting for completion...")
        sync_button.click()

        # Wait for sync to complete (check for success message or button state change)
        # Timeout: 60 seconds
        WebDriverWait(driver, 60).until(
            lambda d: "success" in d.page_source.lower() or "berhasil" in d.page_source.lower()
        )

        print("✅ Sync completed!")
        return {"status": "success"}

    except Exception as e:
        print(f"⚠️  Sync warning: {str(e)}")
        return {"status": "warning", "message": str(e)}

    finally:
        if driver:
            driver.quit()

def query_database(db_host: str, db_user: str, db_password: str, db_name: str):
    """
    Query packets table and return aggregated satker/PPK ranking.
    """

    try:
        print("Connecting to database...")
        conn = mysql.connector.connect(
            host=db_host,
            user=db_user,
            password=db_password,
            database=db_name
        )
        cursor = conn.cursor(dictionary=True)

        # Get latest sync timestamp
        cursor.execute("SELECT MAX(updated_at) as latest_sync FROM packets")
        sync_time = cursor.fetchone()["latest_sync"]
        print(f"Latest sync: {sync_time}")

        # Query satker ranking
        satker_query = """
            SELECT
              p.kdsatker,
              ROUND((SUM(p.realisasi) / NULLIF(SUM(p.pagu), 0) * 100), 2) AS progres_keuangan,
              ROUND(SUM(p.real_fisik * p.pagu) / NULLIF(SUM(p.pagu), 0), 2) AS progres_fisik,
              SUM(p.pagu) AS total_pagu,
              SUM(p.realisasi) AS total_realisasi
            FROM packets p
            WHERE p.kdsatker IN ('694133', '694134', '694135', '694137', '694136')
            GROUP BY p.kdsatker
            ORDER BY progres_keuangan DESC
        """
        cursor.execute(satker_query)
        satker_data = cursor.fetchall()

        satker_ranking = []
        for row in satker_data:
            satker_slug = SATKER_MAP.get(row["kdsatker"], "unknown")
            satker_ranking.append({
                "kdsatker": row["kdsatker"],
                "satker_slug": satker_slug,
                "satker_name": SATKER_DISPLAY.get(satker_slug, "Unknown"),
                "progres_keuangan": float(row["progres_keuangan"] or 0),
                "progres_fisik": float(row["progres_fisik"] or 0),
                "total_pagu": int(row["total_pagu"] or 0),
                "total_realisasi": int(row["total_realisasi"] or 0)
            })

        # Query PPK ranking (all 17 PPKs)
        ppk_query = """
            SELECT
              ppk.id,
              ppk.name,
              ppk.satker_group,
              COUNT(p.id) AS packet_count,
              ROUND((SUM(COALESCE(p.realisasi, 0)) / NULLIF(SUM(COALESCE(p.pagu, 0)), 0) * 100), 2) AS progres_keuangan,
              ROUND(SUM(COALESCE(p.real_fisik, 0) * COALESCE(p.pagu, 0)) / NULLIF(SUM(COALESCE(p.pagu, 0)), 0), 2) AS progres_fisik,
              SUM(COALESCE(p.pagu, 0)) AS total_pagu,
              SUM(COALESCE(p.realisasi, 0)) AS total_realisasi
            FROM ppks ppk
            LEFT JOIN packets p ON p.ppk_id = ppk.id
            GROUP BY ppk.id, ppk.name, ppk.satker_group
            ORDER BY progres_keuangan DESC
        """
        cursor.execute(ppk_query)
        ppk_data = cursor.fetchall()

        ppk_ranking = []
        for row in ppk_data:
            ppk_ranking.append({
                "id": row["id"],
                "name": row["name"],
                "satker_group": row["satker_group"],
                "packet_count": int(row["packet_count"]),
                "progres_keuangan": float(row["progres_keuangan"] or 0),
                "progres_fisik": float(row["progres_fisik"] or 0),
                "total_pagu": int(row["total_pagu"] or 0),
                "total_realisasi": int(row["total_realisasi"] or 0)
            })

        # Overall BWS aggregate
        overall_query = """
            SELECT
              ROUND((SUM(realisasi) / NULLIF(SUM(pagu), 0) * 100), 2) AS progres_keuangan,
              ROUND(SUM(real_fisik * pagu) / NULLIF(SUM(pagu), 0), 2) AS progres_fisik,
              SUM(pagu) AS total_pagu,
              SUM(realisasi) AS total_realisasi
            FROM packets
        """
        cursor.execute(overall_query)
        overall = cursor.fetchone()

        cursor.close()
        conn.close()

        print("✅ Database query successful!")

        return {
            "status": "success",
            "latest_sync": str(sync_time),
            "bws_aggregate": {
                "progres_keuangan": float(overall["progres_keuangan"] or 0),
                "progres_fisik": float(overall["progres_fisik"] or 0),
                "total_pagu": int(overall["total_pagu"] or 0),
                "total_realisasi": int(overall["total_realisasi"] or 0)
            },
            "satker_ranking": satker_ranking,
            "ppk_ranking": ppk_ranking
        }

    except Exception as e:
        print(f"❌ Database query failed: {str(e)}")
        return {
            "status": "error",
            "message": str(e)
        }

if __name__ == "__main__":
    if len(sys.argv) < 5:
        print("Usage: python sync_and_query.py <db_host> <db_user> <db_password> <db_name> [app_url]")
        sys.exit(1)

    db_host = sys.argv[1]
    db_user = sys.argv[2]
    db_password = sys.argv[3]
    db_name = sys.argv[4]
    app_url = sys.argv[5] if len(sys.argv) > 5 else "http://127.0.0.1:8000"

    # Step 1: Click sync button
    sync_result = click_sync_button(app_url)
    if sync_result["status"] not in ["success", "warning"]:
        print(json.dumps(sync_result, indent=2))
        sys.exit(1)

    # Step 2: Query database
    query_result = query_database(db_host, db_user, db_password, db_name)
    print(json.dumps(query_result, indent=2))
