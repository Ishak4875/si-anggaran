#!/usr/bin/env python3
"""
Format aggregated data into WhatsApp e-Monitoring message.

Usage:
  python format_message.py <json_data_file> <ditjen_keu> <ditjen_fis>

Returns:
  Formatted WhatsApp message ready to copy-paste
"""

import json
import sys
from datetime import datetime
from zoneinfo import ZoneInfo

PPK_SHORT_NAMES = {
    "PPK Air Tanah dan Air Baku 1": "PPK Atab I",
    "PPK Air Tanah dan Air Baku 2": "PPK Atab II",
    "PPK Daya Dukung Lingkungan 1": "PPK DDL I",
    "PPK Daya Dukung Lingkungan 2": "PPK DDL II",
    "PPK Pengembangan Kawasan 1": "PPK PK I",
    "PPK Pengembangan Kawasan 2": "PPK PK II",
    "PPK Pengembangan Kawasan 3": "PPK PK III",
    "PPK Pengembangan Kawasan 4": "PPK PK IV",
    "PPK Operasional 1": "PPK Op I",
    "PPK Operasional 2": "PPK Op II",
    "PPK Pelayanan Sumber Daya Air 1": "PPK PSDA I",
    "PPK Pelayanan Sumber Daya Air 2": "PPK PSDA II",
    "PPK Perencanaan 1": "PPK Peren I",
    "PPK Perencanaan 2": "PPK Peren II",
    "PPK Bendungan 1": "PPK Bend I",
    "PPK Bendungan 2": "PPK Bend II",
    "PPK Bendungan 3": "PPK Bend III",
}

def get_greeting():
    """Get time-based greeting in Asia/Makassar timezone."""
    tz = ZoneInfo("Asia/Makassar")
    now = datetime.now(tz)
    hour = now.hour

    if 0 <= hour < 11:
        return "Pagi"
    elif 11 <= hour < 15:
        return "Siang"
    elif 15 <= hour < 18:
        return "Sore"
    else:
        return "Malam"

def format_timestamp(ts_str):
    """Format timestamp to 'DD Mon YYYY ; HH:mm WITA' format."""
    if not ts_str:
        return "N/A"

    # Parse ISO format
    try:
        dt = datetime.fromisoformat(ts_str.replace("Z", "+00:00"))
        tz = ZoneInfo("Asia/Makassar")
        dt_local = dt.astimezone(tz)

        months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]
        day = dt_local.day
        month = months[dt_local.month - 1]
        year = dt_local.year
        hour = dt_local.hour
        minute = dt_local.minute

        return f"{day:02d} {month} {year} ; {hour:02d}:{minute:02d} WITA"
    except:
        return "N/A"

def get_ppk_short_name(full_name):
    """Map full PPK name to short name."""
    return PPK_SHORT_NAMES.get(full_name, full_name[:20])

def format_message(data: dict, ditjen_keu: float, ditjen_fis: float):
    """
    Format aggregated data into WhatsApp message.

    Args:
        data: JSON from sync_and_query.py
        ditjen_keu: Ditjen SDA Keuangan % (0-100)
        ditjen_fis: Ditjen SDA Fisik % (0-100)

    Returns:
        Formatted message string
    """

    greeting = get_greeting()
    timestamp = format_timestamp(data.get("latest_sync"))
    bws = data.get("bws_aggregate", {})
    satkers = data.get("satker_ranking", [])
    ppks = data.get("ppk_ranking", [])

    bws_keu = bws.get("progres_keuangan", 0)
    bws_fis = bws.get("progres_fisik", 0)

    # Calculate deviasi
    dev_keu = bws_keu - ditjen_keu
    dev_fis = bws_fis - ditjen_fis

    # Build message
    lines = []
    lines.append(f"Selamat {greeting} Bpk/Ibu\n")
    lines.append(f"Mohon izin menyampaikan progres e-Monitoring status :  {timestamp}\n")

    # Ditjen SDA section
    lines.append("🔘Progres K/F Ditjen SDA :")
    lines.append(f"Keuangan  : {ditjen_keu:.2f} %")
    lines.append(f"Fisik  : {ditjen_fis:.2f} %\n")

    # BWS section
    lines.append("🔘Progres K/F BWS Sul IV KDI :")
    lines.append(f"Keuangan  : {bws_keu:.2f} %")
    lines.append(f"Fisik  : {bws_fis:.2f} %\n")

    # Deviasi section
    lines.append("➡ Deviasi Progres K/F BWS Sul IV KDI thdp Ditjen SDA :")
    dev_keu_str = f"+{dev_keu:.2f}" if dev_keu >= 0 else f"{dev_keu:.2f}"
    dev_fis_str = f"+{dev_fis:.2f}" if dev_fis >= 0 else f"{dev_fis:.2f}"
    lines.append(f"Keuangan  : {dev_keu_str} %")
    lines.append(f"Fisik  : {dev_fis_str} %\n")

    # Satker ranking
    lines.append("Rincian Progres K/F Masing2 Satker di lingkungan BWS Sul IV KDI :")
    for idx, satker in enumerate(satkers, 1):
        emoji = ""
        if idx == 1:
            emoji = "🥇"
        elif idx == 2:
            emoji = "🥈"
        elif idx == 3:
            emoji = "🥉"

        lines.append(
            f"🔹{satker['satker_name']} : {satker['progres_keuangan']:.2f}% / {satker['progres_fisik']:.2f}%{emoji}"
        )
    lines.append("")

    # PPK ranking
    lines.append("🔘 Progres K/F masing2 PPK di lingkungan BWS SUL IV KDI:")
    for idx, ppk in enumerate(ppks, 1):
        emoji = ""
        if idx == 1:
            emoji = "🥇"
        elif idx == 2:
            emoji = "🥈"
        elif idx == 3:
            emoji = "🥉"

        short_name = get_ppk_short_name(ppk["name"])
        lines.append(
            f"{idx}. {short_name} : {ppk['progres_keuangan']:.2f}% / {ppk['progres_fisik']:.2f}%{emoji}"
        )
    lines.append("")

    # Bottom performer (below Ditjen target)
    below_ditjen = [p for p in ppks if p["progres_keuangan"] < ditjen_keu]
    if below_ditjen:
        lowest = below_ditjen[-1]  # Last one is lowest
        lines.append("🔘  Progres Keu di bawah Ditjen SDA *berdasarkan iemon* :")
        short_name = get_ppk_short_name(lowest["name"])
        lines.append(f"{short_name} : {lowest['progres_keuangan']:.2f} %\n")
    else:
        lines.append("")

    # Footer
    lines.append("Demikian disampaikan, terima kasih..")
    lines.append("Salam Damai Indonesia, Bahagia untuk Semua 💪💪💪")
    lines.append("Cc. Bapak Kabalai")

    return "\n".join(lines)

if __name__ == "__main__":
    if len(sys.argv) < 4:
        print("Usage: python format_message.py <json_data_file> <ditjen_keu> <ditjen_fis>")
        sys.exit(1)

    json_file = sys.argv[1]
    ditjen_keu = float(sys.argv[2])
    ditjen_fis = float(sys.argv[3])

    try:
        with open(json_file, "r") as f:
            data = json.load(f)
    except Exception as e:
        print(f"Error reading JSON: {str(e)}")
        sys.exit(1)

    message = format_message(data, ditjen_keu, ditjen_fis)
    print(message)
