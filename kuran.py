#!/usr/pkg/bin/python
# -*- coding: utf-8 -*-

import cgi
import cgitb
import re
import urllib.request
import urllib.error
import json
import sys
import os

# Hata ayıklamayı aktif et (Geliştirme aşamasında hataları tarayıcıda görmek için yararlıdır)
cgitb.enable()

# URL Query parametrelerini al (?api=...)
form = cgi.FieldStorage()
api_path = form.getvalue('api')

if api_path:
    # 1. Regex Güvenlik Filtresi (PHP'deki preg_match mantığının birebir aynısı)
    pattern = r'^(surah|juz|page)/[\d,]+(/editions)?/[\w.,\-]+$'
    if not re.match(pattern, api_path):
        print("Status: 400 Bad Request")
        print("Content-Type: application/json; charset=utf-8")
        print()
        print(json.dumps({'error': 'Gecersiz API yolu'}))
        sys.exit()

    url = 'https://api.alquran.cloud/v1/' + api_path
    data = None
    status_code = 200
    
    # 2. HTTP İstek Ayarları (PHP cURL ve User-Agent taklidi)
    req = urllib.request.Request(
        url, 
        headers={'User-Agent': 'DijitalKuran/1.0'}
    )
    
    try:
        # SSL sertifika doğrulamasını esnetmek için (PHP'deki CURLOPT_SSL_VERIFYPEER false gibi)
        import ssl
        context = ssl._create_unverified_context()
        
        with urllib.request.urlopen(req, timeout=20, context=context) as response:
            data = response.read().decode('utf-8')
            status_code = response.status
    except urllib.error.HTTPError as e:
        status_code = e.code
    except Exception:
        status_code = 502

    # 3. HTTP Yanıt Başlıkları ve Verinin Çıktılanması
    print("Content-Type: application/json; charset=utf-8")
    print("Access-Control-Allow-Origin: *")
    
    if data and 200 <= status_code < 300:
        print("Status: 200 OK")
        print()
        print(data)
    else:
        print("Status: 502 Bad Gateway")
        print()
        print(json.dumps({'error': 'API baglanti hatasi'}))
    sys.exit()

# -------------------------------------------------------------------------
# Eğer API çağrısı değilse, normal tarayıcı isteğidir. HTML içeriğini bas.
# -------------------------------------------------------------------------
print("Content-Type: text/html; charset=utf-8")
print()

# Python'un çok satırlı metin (Multiline String) yapısı kullanılarak 
# kuran.php içerisindeki tüm HTML, CSS ve JS kod bütünlüğü aynen korunmuştur.
print("""<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dijital Kur'an</title>
<style>
/* ===== FONTS ===== */
@font-face { font-family: "ShaikhHamdullah"; src: url("fonts/shaikh_hamdullah_mushaf.woff2") format("woff2"); font-display: swap; }
@font-face { font-family: "ShaikhHamdullahBasic"; src: url("fonts/ShaikhHamdullahBasic.woff2") format("woff2"); font-display: swap; }
@font-face { font-family: "ShaikhHamdullahBook"; src: url("fonts/ShaikhHamdullahBook.woff2") format("woff2"); font-display: swap; }
@font-face { font-family: "Elfmshf"; src: url("fonts/elfmshf.woff2") format("woff2"); font-display: swap; }
@font-face { font-family: "KKAbay"; src: url("fonts/kk_abay.woff2") format("woff2"); font-display: swap; }
@font-face { font-family: "AbdoLine"; src: url("fonts/AbdoLineNormal-Regular.woff2") format("woff2"); font-display: swap; }
@font-face { font-family: "AdwaAssalaf"; src: url("fonts/adwa-assalaf.woff2") format("woff2"); font-display: swap; }
@font-face { font-family: "Arakom"; src: url("fonts/Arakom.woff2") format("woff2"); font-display: swap; }
@font-face { font-family: "Hsnt"; src: url("fonts/hsnt.woff2") format("woff2"); font-display: swap; }
@font-face { font-family: "Slymnfyzlgl"; src: url("fonts/slymnfyzlgl.woff2") format("woff2"); font-display: swap; }
@font-face { font-family: "WinSoftPro"; src: url("fonts/WinSoftPro-Med.woff2") format("woff2"); font-display: swap; }

/* ===== VARIABLES ===== */
:root {
  --radius: 0.75rem;
  --background: #f7f4eb; --foreground: #2c251e;
  --card: #ffffff; --card-foreground: #2c251e;
  --primary: #947343; --primary-foreground: #ffffff;
  --secondary: #f3ede0; --secondary-foreground: #2c251e;
  --muted: #f3ede0; --muted-foreground: #6b5a44;
  --accent: #947343; --accent-foreground: #ffffff;
  --destructive: #c0392b; --destructive-foreground: #ffffff;
  --border: #eadecc; --input: #eadecc; --ring: #947343;
  --sidebar: #ffffff; --sidebar-foreground: #2c251e;
  --highlight: #f3ede0;
  --paper-motif: url('themes/sure_motif.png');
  --sure-baslik: url('themes/SureBaslik2.png');
}
body.theme-sepia {
  --background: #f8fafc; --foreground: #0f172a;
  --card: #ffffff; --card-foreground: #0f172a;
  --primary: #0f766e; --primary-foreground: #ffffff;
  --secondary: #f1f5f9; --secondary-foreground: #0f172a;
  --muted: #f1f5f9; --muted-foreground: #475569;
  --accent: #0f766e; --accent-foreground: #ffffff;
  --border: #e2e8f0; --input: #e2e8f0; --ring: #0f766e;
  --sidebar: #ffffff; --sidebar-foreground: #0f172a;
  --highlight: #e2e8f0; --paper-motif: none;
}
body.theme-dark {
  --background: #0f172a; --foreground: #f8fafc;
  --card: #1e293b; --card-foreground: #f8fafc;
  --primary: #38bdf8; --primary-foreground: #0f172a;
  --secondary: #1e293b; --secondary-foreground: #f8fafc;
  --muted: #1e293b; --muted-foreground: #cbd5e1;
  --accent: #38bdf8; --accent-foreground: #0f172a;
  --border: #334155; --input: #334155; --ring: #38bdf8;
  --sidebar: #1e293b; --sidebar-foreground: #f8fafc;
  --highlight: #334155; --paper-motif: none;
}
body.theme-dark, body.theme-dark * { color: #f8fafc; }
body.theme-dark .ayah-translation { color: #f8fafc; }
body.theme-dark input, body.theme-dark select, body.theme-dark textarea { color: #f8fafc; background-color: #1e293b; }

/* ===== RESET & BASE ===== */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
  background-color: var(--background);
  color: var(--foreground);
  font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
  -webkit-font-smoothing: antialiased;
  height: 100dvh; overflow: hidden;
}
button { background: none; border: none; cursor: pointer; font-family: inherit; }
select, input { font-family: inherit; }

/* ===== LAYOUT ===== */
#app { display: flex; height: 100dvh; width: 100%; overflow: hidden; position: relative; }

/* ===== BACKDROP ===== */
#backdrop {
  display: none; position: fixed; inset: 0;
  background: rgba(0,0,0,0.4); z-index: 40;
}
#backdrop.open { display: block; }

/* ===== SIDEBAR ===== */
#sidebar {
  position: fixed; top: 0; left: 0; height: 100%;
  background-color: var(--sidebar);
  border-right: 1px solid var(--border);
  z-index: 50; display: flex; flex-direction: column;
  transition: transform 0.3s; width: 320px;
  transform: translateX(-100%);
  box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
}
#sidebar.open { transform: translateX(0); }

.sidebar-header {
  background-color: var(--primary);
  color: var(--primary-foreground);
  padding: 16px 20px;
  display: flex; align-items: center; justify-content: space-between;
}
.sidebar-header .title { display: flex; align-items: center; gap: 8px; font-weight: 600; }
.sidebar-header button { padding: 4px; opacity: 0.9; color: var(--primary-foreground); }
.sidebar-header button:hover { opacity: 1; }

.reciter-section { padding: 12px; border-bottom: 1px solid var(--border); background-color: var(--card); }
.reciter-section label { font-size: 12px; color: var(--muted-foreground); display: block; margin-bottom: 4px; }
.reciter-section select {
  width: 100%; border-radius: 6px; border: 1px solid var(--border);
  background: var(--background); color: var(--foreground);
  padding: 8px 12px; font-size: 14px;
}

.quick-actions { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; padding: 8px; border-bottom: 1px solid var(--border); }
.quick-btn {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  gap: 4px; background-color: var(--card); border: 1px solid var(--border);
  border-radius: 6px; padding: 8px 4px; font-size: 10px; color: var(--foreground);
  transition: background 0.15s, color 0.15s, border-color 0.15s;
}
.quick-btn:hover { background-color: var(--primary); color: var(--primary-foreground); border-color: var(--primary); }

.search-row { padding: 8px; border-bottom: 1px solid var(--border); background-color: var(--card); display: flex; gap: 4px; }
.search-row input {
  flex: 1; padding: 8px 12px; border-radius: 6px;
  border: 1px solid var(--border); background: var(--background); font-size: 14px; color: var(--foreground);
}
.search-row button {
  width: 40px; border-radius: 6px;
  background-color: var(--primary); color: var(--primary-foreground);
  display: flex; align-items: center; justify-content: center;
}

.tab-row { display: flex; gap: 4px; padding: 8px; border-bottom: 1px solid var(--border); }
.tab-btn {
  flex: 1; padding: 8px; border-radius: 6px; font-size: 14px; font-weight: 500;
  border: 1px solid var(--border); color: var(--foreground);
  transition: background 0.15s, color 0.15s, border-color 0.15s;
}
.tab-btn:hover { background-color: var(--muted); }
.tab-btn.active { background-color: var(--primary); color: var(--primary-foreground); border-color: var(--primary); }

.list-container { flex: 1; overflow-y: auto; }
.list-container::-webkit-scrollbar { width: 6px; }
.list-container::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }

.list-item {
  width: 100%; text-align: left; padding: 12px 16px;
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
  font-size: 14px; color: var(--foreground);
  transition: background 0.15s, color 0.15s;
  cursor: pointer;
}
.list-item:hover { background-color: var(--primary); color: var(--primary-foreground); }
.list-item.active { background-color: rgba(148,115,67,0.15); border-left: 4px solid var(--accent); }
.list-item .item-info { display: flex; align-items: center; gap: 12px; }
.item-num {
  width: 28px; height: 28px; border-radius: 50%;
  background-color: var(--muted); color: var(--foreground);
  font-size: 12px; display: flex; align-items: center; justify-content: center;
  font-weight: 600; flex-shrink: 0;
}
.list-item:hover .item-num { background-color: rgba(255,255,255,0.2); color: var(--primary-foreground); }
.item-name { font-weight: 500; display: block; }
.item-sub { font-size: 11px; opacity: 0.7; display: block; }
.item-arabic { font-family: "ShaikhHamdullah", serif; font-size: 18px; direction: rtl; }

/* ===== MAIN ===== */
#main { flex: 1; display: flex; flex-direction: column; min-width: 0; height: 100%; }

/* ===== HEADER ===== */
header {
  background-color: var(--primary); color: var(--primary-foreground);
  padding: 10px 16px; display: flex; align-items: center;
  justify-content: center; gap: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); flex-shrink: 0;
  flex-wrap: wrap;
}
header button { color: var(--primary-foreground); }
#page-title { font-size: 15px; font-weight: 600; color: white; text-align: center; }
.nav-btn { padding: 4px 10px; border-radius: 6px; background-color: rgba(255,255,255,0.15); font-size: 12px; color: var(--primary-foreground); }
.nav-btn:hover { background-color: rgba(255,255,255,0.25); }
.player-controls { display: flex; align-items: center; gap: 4px; }
.player-controls button { padding: 6px; border-radius: 50%; }
.player-controls button:hover { background-color: rgba(255,255,255,0.1); }
#play-btn {
  width: 36px; height: 36px; border-radius: 50%;
  background-color: white; color: var(--primary);
  display: flex; align-items: center; justify-content: center;
}
#play-btn:hover { opacity: 0.9; }
#speed-select {
  background-color: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25);
  border-radius: 6px; padding: 4px 6px; font-size: 12px; color: var(--primary-foreground);
}
#speed-select option { color: #1e293b; }
#repeat-btn.on { background-color: white; color: var(--primary); }
#settings-btn { padding: 8px; border-radius: 6px; }
#settings-btn:hover { background-color: rgba(255,255,255,0.1); }

/* ===== READER ===== */
#reader { flex: 1; overflow-y: auto; padding: 32px 40px; }
#reader::-webkit-scrollbar { width: 6px; }
#reader::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }
@media (max-width: 640px) { #reader { padding: 16px; } }

.mushaf-frame {
  max-width: 800px; margin: 0 auto;
  background-color: var(--card);
  background-image: var(--paper-motif, none);
  background-repeat: repeat;
  padding: 32px 40px; border-radius: 6px;
  border: 2px solid var(--ring, var(--border));
  outline: 1px solid var(--ring, var(--border));
  outline-offset: 4px;
}
@media (max-width: 640px) { .mushaf-frame { padding: 16px; } }

.sure-baslik {
  width: 100%; margin: 8px auto 35px;
  padding: 35px 40px;
  background: var(--sure-baslik, none) no-repeat center center;
  background-size: 100% 100%;
  display: flex; flex-direction: column;
  justify-content: center; align-items: center; text-align: center;
}
.sure-baslik .arabic { color: #ffffff; font-weight: normal; line-height: 1.2; text-shadow: 0 2px 4px rgba(0,0,0,0.4); }
body.theme-sepia .sure-baslik, body.theme-dark .sure-baslik {
  background: var(--primary); border-radius: 10px; color: var(--primary-foreground); padding: 12px 24px;
}
body.theme-sepia .sure-baslik .arabic, body.theme-dark .sure-baslik .arabic { color: var(--primary-foreground); text-shadow: 0 1px 2px rgba(0,0,0,0.35); }

.basmala { font-family: "ShaikhHamdullah", serif; direction: rtl; line-height: 2.55; text-align: center; font-size: 36px; }
.mushaf-divider {
  height: 1px;
  background: linear-gradient(to right, transparent, rgba(148,115,67,0.55), transparent);
  margin: 1.1rem auto 1.5rem; max-width: 60%;
}

/* Flow mode (no translation) */
.mushaf-flow { text-align: right; word-spacing: 0.05em; direction: rtl; font-family: "ShaikhHamdullah", serif; line-height: 2.55; }
/* With-translation mode */
.ayah-block { margin-bottom: 4px; }
.ayah-row { display: block; text-align: right; margin: 2px 0; direction: rtl; font-family: "ShaikhHamdullah", serif; line-height: 2.55; unicode-bidi: plaintext; }
.ayah-row > .ayah { display: inline; }

.arabic { font-family: "ShaikhHamdullah", serif; direction: rtl; line-height: 2.55; font-feature-settings: "liga", "calt"; }

.ayah { transition: background 0.18s ease; border-radius: 0.35rem; padding: 0 2px; cursor: pointer; }
.ayah:hover { background: rgba(148,115,67,0.12); }
.ayah.active { background: rgba(148,115,67,0.22); }
body.theme-sepia .ayah:hover { background: rgba(15,118,110,0.12); }
body.theme-sepia .ayah.active { background: rgba(15,118,110,0.22); }
body.theme-dark .ayah:hover { background: rgba(56,189,248,0.12); }
body.theme-dark .ayah.active { background: rgba(56,189,248,0.22); }

.ayah-num {
  display: inline-block;
  font-family: "ShaikhHamdullah", "Elfmshf", serif;
  font-size: 0.78em; color: var(--accent);
  margin: 0 3px; vertical-align: baseline; user-select: none;
}

.ayah-inline-controls { display: inline-flex; align-items: center; vertical-align: middle; white-space: nowrap; margin: 0 4px; position: relative; }
.ayah-dots {
  display: inline-flex; align-items: center; justify-content: center;
  width: 32px; height: 32px; border-radius: 999px;
  color: var(--accent); opacity: 0.9;
  background: rgba(148,115,67,0.1);
  transition: opacity 0.15s, background 0.15s;
  cursor: pointer;
}
.ayah-dots:hover { opacity: 1; background: rgba(148,115,67,0.22); }
body.theme-sepia .ayah-dots { background: rgba(15,118,110,0.1); }
body.theme-sepia .ayah-dots:hover { background: rgba(15,118,110,0.22); }
body.theme-dark .ayah-dots { background: rgba(56,189,248,0.1); }
body.theme-dark .ayah-dots:hover { background: rgba(56,189,248,0.22); }

.ayah-menu {
  position: absolute; top: -2.6rem; left: 50%; transform: translateX(-50%);
  display: none; gap: 2px; padding: 4px;
  background: var(--card); border: 1px solid var(--border);
  border-radius: 999px;
  box-shadow: 0 10px 24px -10px rgba(0,0,0,0.35);
  z-index: 30; white-space: nowrap; direction: ltr;
}
.ayah-menu.open { display: inline-flex; }
.ayah-menu button {
  display: inline-flex; align-items: center; justify-content: center;
  width: 32px; height: 32px; border-radius: 999px; color: var(--foreground);
  transition: background 0.15s, color 0.15s;
}
.ayah-menu button:hover { background: rgba(148,115,67,0.18); color: var(--accent); }
.ayah-menu button.active { color: var(--accent); }

.ayah-translation {
  direction: ltr; text-align: left;
  font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
  font-size: 0.95rem; line-height: 1.7; color: var(--muted-foreground);
  border-left: 2px solid rgba(148,115,67,0.45);
  padding: 3px 14px; margin: 5px 0 20px;
}
.translation-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--accent); margin-bottom: 2px; }
.ayah-num-label { font-weight: 700; color: var(--accent); margin-right: 4px; font-size: 0.85em; }

/* ===== LOADING ===== */
#loading { text-align: center; color: var(--muted-foreground); padding: 80px 20px; display: none; }
#loading.show { display: block; }

/* ===== BOOKMARKS PANEL ===== */
#bookmarks-panel {
  display: none; max-width: 800px; margin: 0 auto 24px;
  background-color: var(--card); border-radius: 12px;
  border: 1px solid var(--border); padding: 24px;
}
#bookmarks-panel.show { display: block; }
#bookmarks-panel h2 { font-weight: 600; margin-bottom: 12px; }

/* ===== SETTINGS MODAL ===== */
#settings-overlay {
  display: none; position: fixed; inset: 0;
  background: rgba(0,0,0,0.5); z-index: 50;
  align-items: center; justify-content: center; padding: 16px;
}
#settings-overlay.open { display: flex; }
#settings-modal {
  background: var(--card); color: var(--card-foreground);
  border: 1px solid var(--border); border-radius: 1rem; padding: 24px;
  width: 100%; max-width: 520px; max-height: 90dvh; overflow-y: auto;
}
#settings-modal::-webkit-scrollbar { width: 6px; }
#settings-modal::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }
.settings-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
.settings-header h2 { font-size: 18px; font-weight: 600; }
.settings-section { margin-bottom: 20px; }
.settings-label { font-size: 14px; font-weight: 500; display: block; margin-bottom: 8px; }
.settings-label svg { display: inline; vertical-align: middle; margin-right: 6px; }
input[type="range"] { width: 100%; accent-color: var(--accent); }
.font-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
.font-btn {
  padding: 12px; border-radius: 6px; border: 1px solid var(--border);
  text-align: center; cursor: pointer; transition: border-color 0.15s, background 0.15s;
  background: none; color: var(--foreground);
}
.font-btn:hover { border-color: var(--accent); }
.font-btn.active { border-color: var(--accent); background: rgba(148,115,67,0.2); }
.font-btn .preview { display: block; font-size: 22px; line-height: 1.3; direction: rtl; }
.font-btn .fname { font-size: 11px; margin-top: 4px; opacity: 0.8; }

.translation-toggle-btn {
  padding: 6px 12px; border-radius: 6px; font-size: 14px;
  border: 1px solid var(--border); cursor: pointer; margin-bottom: 8px;
  background: none; color: var(--foreground); transition: background 0.15s, color 0.15s, border-color 0.15s;
}
.translation-toggle-btn.active { background: var(--accent); color: var(--accent-foreground); border-color: var(--accent); }
.translations-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
.translation-checkbox-btn {
  display: flex; align-items: center; gap: 8px;
  padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border);
  font-size: 14px; cursor: pointer; transition: border-color 0.15s, background 0.15s;
  background: none; color: var(--foreground);
}
.translation-checkbox-btn:hover { border-color: var(--accent); }
.translation-checkbox-btn.active { border-color: var(--accent); background: rgba(148,115,67,0.15); }
.translations-hint { font-size: 12px; color: var(--muted-foreground); margin-top: 8px; }
.translations-disabled { opacity: 0.5; pointer-events: none; }

.reciter-select-settings {
  width: 100%; border-radius: 6px; border: 1px solid var(--border);
  background: var(--background); color: var(--foreground);
  padding: 8px 12px; font-size: 14px;
}
.theme-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.theme-btn {
  padding: 8px; border-radius: 6px; font-size: 14px;
  border: 1px solid var(--border); cursor: pointer;
  background: none; color: var(--foreground); transition: border-color 0.15s, background 0.15s;
}
.theme-btn:hover { border-color: var(--accent); }
.theme-btn.active { border-color: var(--accent); background: rgba(148,115,67,0.2); }

/* SVG icons */
svg { display: inline-block; vertical-align: middle; }

/* Copied toast */
.copied-toast {
  position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%);
  background: var(--primary); color: var(--primary-foreground);
  padding: 8px 20px; border-radius: 999px; font-size: 14px;
  opacity: 0; pointer-events: none; transition: opacity 0.2s; z-index: 100;
}
.copied-toast.show { opacity: 1; }
</style>
</head>
<body>

<div id="app">
  <div id="backdrop" onclick="closeSidebar()"></div>

  <aside id="sidebar">
    <div class="sidebar-header">
      <div class="title">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
        <span>Mushaf</span>
      </div>
      <button onclick="closeSidebar()" aria-label="Kapat">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <div class="reciter-section">
      <label>Kâri (Okuyucu)</label>
      <select id="sidebar-reciter" onchange="setState('reciter', this.value)"></select>
    </div>

    <div class="quick-actions">
      <button class="quick-btn" onclick="setState('showBookmarks', !appState.showBookmarks)">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/></svg>
        <span>Yer İmleri</span>
      </button>
      <button class="quick-btn" onclick="setState('showTranslation', !appState.showTranslation)">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 8 6 6"/><path d="m4 14 6-6 2-3"/><path d="M2 5h12"/><path d="M7 2h1"/><path d="m22 22-5-10-5 10"/><path d="M14 18h6"/></svg>
        <span id="meal-btn-label">Meal Açık</span>
      </button>
      <button class="quick-btn" id="theme-toggle-btn" onclick="cycleTheme()">
        <svg id="theme-icon-moon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9z"/></svg>
        <svg id="theme-icon-sun" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
        <span>Tema</span>
      </button>
      <button class="quick-btn" onclick="openSettings()">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
        <span>Ayarlar</span>
      </button>
    </div>

    <div class="search-row">
      <input type="text" id="search-input" placeholder="Sure ara..." oninput="filterList(this.value)">
      <button aria-label="Ara">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
      </button>
    </div>

    <div class="tab-row">
      <button class="tab-btn active" id="tab-surah" onclick="setTab('surah')">Sure</button>
      <button class="tab-btn" id="tab-juz" onclick="setTab('juz')">Cüz</button>
      <button class="tab-btn" id="tab-page" onclick="setTab('page')">Sayfa</button>
    </div>

    <div class="list-container" id="list-container"></div>
  </aside>

  <main id="main">
    <header>
      <button id="settings-btn" onclick="toggleSidebar()" title="Sure listesi / Ayarlar">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>

      <div class="player-controls">
        <button id="prev-btn" onclick="navPage(-1)" title="Önceki Sayfa/Sure">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="19 20 9 12 19 4 19 20"/><line x1="5" y1="19" x2="5" y2="5"/></svg>
        </button>
        <button id="play-btn" onclick="togglePlay()" title="Oynat / Durdur">
          <svg id="play-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          <svg id="pause-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" style="display:none"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
        </button>
        <button id="next-btn" onclick="navPage(1)" title="Sonraki Sayfa/Sure">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 4 15 12 5 20 5 4"/><line x1="19" y1="5" x2="19" y2="19"/></svg>
        </button>
        <button id="repeat-btn" onclick="toggleRepeat()" title="Ayet Tekrarı">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
        </button>
        <select id="speed-select" onchange="setSpeed(this.value)" title="Okuma Hızı">
          <option value="0.5">0.5x</option>
          <option value="0.75">0.75x</option>
          <option value="1" selected>1.0x</option>
          <option value="1.25">1.25x</option>
          <option value="1.5">1.5x</option>
          <option value="2">2.0x</option>
        </select>
      </div>

      <div id="page-title">Yükleniyor...</div>
    </header>

    <div id="reader">
      <div id="bookmarks-panel">
        <h2>Yer İmleri</h2>
        <div id="bookmarks-list">Henüz yer imi eklenmemiş.</div>
      </div>

      <div id="loading" class="show">Veriler indiriliyor, lütfen bekleyin...</div>

      <div id="mushaf-content"></div>
    </div>
  </main>
</div>

<div id="settings-overlay" onclick="closeSettings()">
  <div id="settings-modal" onclick="event.stopPropagation()">
    <div class="settings-header">
      <h2>Görünüm ve Okuma Ayarları</h2>
      <button onclick="closeSettings()">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <div class="settings-section">
      <label class="settings-label">Tema</label>
      <div class="theme-grid">
        <button class="theme-btn" id="theme-btn-default" onclick="setTheme('default')">Parşömen</button>
        <button class="theme-btn" id="theme-btn-sepia" onclick="setTheme('sepia')">Aydınlık</button>
        <button class="theme-btn" id="theme-btn-dark" onclick="setTheme('dark')">Karanlık</button>
      </div>
    </div>

    <div class="settings-section">
      <label class="settings-label">Arapça Yazı Tipi Boyutu</label>
      <input type="range" min="20" max="64" value="36" oninput="setState('arabicFontSize', this.value)">
    </div>

    <div class="settings-section">
      <label class="settings-label">Meal Yazı Tipi Boyutu</label>
      <input type="range" min="12" max="28" value="16" oninput="setState('translationFontSize', this.value)">
    </div>

    <div class="settings-section">
      <label class="settings-label">Arapça Hat Tipi</label>
      <select id="font-select" class="reciter-select-settings" onchange="setState('fontFamily', this.value)"></select>
    </div>

    <div class="settings-section">
      <label class="settings-label">Sesli Okuyucu (Kâri)</label>
      <select id="settings-reciter" class="reciter-select-settings" onchange="setState('reciter', this.value)"></select>
    </div>

    <div class="settings-section">
      <button class="translation-toggle-btn" id="settings-translation-toggle" onclick="setState('showTranslation', !appState.showTranslation)">Meal Görünümünü Kapat</button>
      <div id="translations-selection-wrapper">
        <label class="settings-label">Aktif Mealler</label>
        <div class="translations-grid" id="translations-grid"></div>
        <p class="translations-hint">Birden fazla meal seçerek karşılaştırmalı okuma yapabilirsiniz.</p>
      </div>
    </div>
  </div>
</div>

<div id="copied-toast" class="copied-toast">Kopyalandı</div>

<audio id="audio-player" style="display:none"></audio>

<script>
// ===== APP STATE =====
const DEFAULT_STATE = {
  tab: 'surah', // surah, juz, page
  currentSurah: 1,
  currentJuz: 1,
  currentPage: 1,
  showTranslation: true,
  theme: 'default', // default, sepia, dark
  arabicFontSize: 36,
  translationFontSize: 16,
  fontFamily: 'ShaikhHamdullah',
  reciter: 'ar.alafasy',
  translations: ['tr.diyanet'],
  showBookmarks: false,
  bookmarks: []
};

let appState = {...DEFAULT_STATE};

// ===== CONSTANTS =====
const FONTS = [
  { id: 'ShaikhHamdullah', name: 'Şeyh Hamdullah' },
  { id: 'ShaikhHamdullahBasic', name: 'Hamdullah Basic' },
  { id: 'ShaikhHamdullahBook', name: 'Hamdullah Book' },
  { id: 'Elfmshf', name: 'Elif Mushaf' },
  { id: 'KKAbay', name: 'Abay Kaligrafi' },
  { id: 'AbdoLine', name: 'Abdo Line' },
  { id: 'AdwaAssalaf', name: 'Adwa Assalaf' },
  { id: 'Arakom', name: 'Arakom Hat' },
  { id: 'Hsnt', name: 'Hasanat Font' },
  { id: 'Slymnfyzlgl', name: 'Süleyman Feyzullah' },
  { id: 'WinSoftPro', name: 'WinSoft Pro' }
];

const TRANSLATIONS = [
  { id: 'tr.diyanet', name: 'Diyanet Meali' },
  { id: 'tr.vakfi', name: 'Diyanet Vakfı' },
  { id: 'tr.ozturk', name: 'Yaşar Nuri Öztürk' },
  { id: 'tr.ates', name: 'Süleyman Ateş' },
  { id: 'tr.yildirim', name: 'Suat Yıldırım' },
  { id: 'tr.golderi', name: 'Cemal Külünkoğlu' }
];

const RECITERS = [
  { id: 'ar.alafasy', name: 'Mishary Rashid Alafasy' },
  { id: 'ar.abdulbasitmurattal', name: 'AbdulBaset AbdulSamad (Murattal)' },
  { id: 'ar.minshawi', name: 'Saddiq Al-Minshawi' },
  { id: 'ar.husary', name: 'Mahmoud Khalil Al-Husary' },
  { id: 'ar.ghamadi', name: 'Saad Al-Ghamdi' },
  { id: 'ar.hudhaify', name: 'Ali Huthaify' },
  { id: 'ar.shatree', name: 'Abu Bakr Al-Shatri' },
  { id: 'ar.saoodshuraym', name: 'Sa\'ud Al-Shuraym' },
  { id: 'ar.mahermuaiqly', name: 'Maher Al-Muaiqly' }
];

// Orijinal surah listesi
const SURAHS = [
  {id:1, name:"Al-Fatihah", ayahs:7, englishName:"Al-Faatiha", arabic:"الفاتحة"},
  {id:2, name:"Al-Baqarah", ayahs:286, englishName:"Al-Baqara", arabic:"البقرة"},
  {id:3, name:"Ali 'Imran", ayahs:200, englishName:"Aal-i-Imraan", arabic:"آل عمران"},
  {id:4, name:"An-Nisa", ayahs:176, englishName:"An-Nisaa", arabic:"النساء"},
  {id:5, name:"Al-Ma'idah", ayahs:120, englishName:"Al-Maaida", arabic:"المائدة"},
  {id:6, name:"Al-An'am", ayahs:165, englishName:"Al-An'aam", arabic:"الأنعام"},
  {id:7, name:"Al-A'raf", ayahs:206, englishName:"Al-A'raaf", arabic:"الأعراف"},
  {id:8, name:"Al-Anfal", ayahs:75, englishName:"Al-Anfaal", arabic:"الأنفال"},
  {id:9, name:"At-Tawbah", ayahs:129, englishName:"At-Tawba", arabic:"التوبة"},
  {id:10, name:"Yunus", ayahs:109, englishName:"Yunus", arabic:"يونس"},
  {id:11, name:"Hud", ayahs:123, englishName:"Hud", arabic:"هود"},
  {id:12, name:"Yusuf", ayahs:111, englishName:"Yusuf", arabic:"يوسف"},
  {id:13, name:"Ar-Ra'd", ayahs:43, englishName:"Ar-Ra'd", arabic:"الرعد"},
  {id:14, name:"Ibrahim", ayahs:52, englishName:"Ibrahim", arabic:"إبراهيم"},
  {id:15, name:"Al-Hijr", ayahs:99, englishName:"Al-Hijr", arabic:"الحجر"},
  {id:16, name:"An-Nahl", ayahs:128, englishName:"An-Nahl", arabic:"النحل"},
  {id:17, name:"Al-Isra", ayahs:111, englishName:"Al-Israa", arabic:"الإسراء"},
  {id:18, name:"Al-Kahf", ayahs:110, englishName:"Al-Kahf", arabic:"الكهف"},
  {id:19, name:"Maryam", ayahs:98, englishName:"Maryam", arabic:"مريم"},
  {id:20, name:"Ta-Ha", ayahs:135, englishName:"Ta-Ha", arabic:"طه"},
  {id:21, name:"Al-Anbiya", ayahs:112, englishName:"Al-Anbiyaa", arabic:"الأنبياء"},
  {id:22, name:"Al-Hajj", ayahs:78, englishName:"Al-Hajj", arabic:"الحج"},
  {id:23, name:"Al-Mu'minun", ayahs:118, englishName:"Al-Mu'minoon", arabic:"المؤمنون"},
  {id:24, name:"An-Nur", ayahs:64, englishName:"An-Noor", arabic:"النور"},
  {id:25, name:"Al-Furqan", ayahs:77, englishName:"Al-Furqaan", arabic:"الفرقان"},
  {id:26, name:"Ash-Shu'ara", ayahs:227, englishName:"Ash-Shu'araa", arabic:"الشعراء"},
  {id:27, name:"An-Naml", ayahs:93, englishName:"An-Naml", arabic:"النمل"},
  {id:28, name:"Al-Qasas", ayahs:88, englishName:"Al-Qasas", arabic:"القصص"},
  {id:29, name:"Al-'Ankabut", ayahs:69, englishName:"Al-Ankaboot", arabic:"العنكبوت"},
  {id:30, name:"Ar-Rum", ayahs:60, englishName:"Ar-Room", arabic:"الروم"},
  {id:31, name:"Luqman", ayahs:34, englishName:"Luqman", arabic:"لقمان"},
  {id:32, name:"As-Sajdah", ayahs:30, englishName:"As-Sajda", arabic:"السجدة"},
  {id:33, name:"Al-Ahzab", ayahs:73, englishName:"Al-Ahzaab", arabic:"الأحزاب"},
  {id:34, name:"Saba", ayahs:54, englishName:"Saba", arabic:"سبإ"},
  {id:35, name:"Fatir", ayahs:45, englishName:"Faatir", arabic:"فاطر"},
  {id:36, name:"Ya-Sin", ayahs:83, englishName:"Ya-Seen", arabic:"يس"},
  {id:37, name:"As-Saffat", ayahs:182, englishName:"As-Saaffaat", arabic:"الصافات"},
  {id:38, name:"Sad", ayahs:88, englishName:"Saad", arabic:"ص"},
  {id:39, name:"Az-Zumar", ayahs:75, englishName:"Az-Zumar", arabic:"الزumar"},
  {id:40, name:"Ghafir", ayahs:85, englishName:"Ghaafir", arabic:"غافر"},
  {id:41, name:"Fussilat", ayahs:54, englishName:"Fussilat", arabic:"فصلت"},
  {id:42, name:"Ash-Shura", ayahs:53, englishName:"Ash-Shoora", arabic:"الشورى"},
  {id:43, name:"Az-Zukhruf", ayahs:89, englishName:"Az-Zukhruf", arabic:"الزخرف"},
  {id:44, name:"Ad-Dukhan", ayahs:59, englishName:"Ad-Dukhaan", arabic:"الدخان"},
  {id:45, name:"Al-Jathiyah", ayahs:37, englishName:"Al-Jaathiya", arabic:"الجاثية"},
  {id:46, name:"Al-Ahqaf", ayahs:35, englishName:"Al-Ahqaaf", arabic:"الأحقاف"},
  {id:47, name:"Muhammad", ayahs:38, englishName:"Muhammad", arabic:"محمد"},
  {id:48, name:"Al-Fath", ayahs:29, englishName:"Al-Fath", arabic:"الفتح"},
  {id:49, name:"Al-Hujurat", ayahs:18, englishName:"Al-Hujuraat", arabic:"الحجرات"},
  {id:50, name:"Qaf", ayahs:45, englishName:"Qaaf", arabic:"ق"},
  {id:51, name:"Adh-Dhariyat", ayahs:60, englishName:"Adh-Dhaariyat", arabic:"الذاريات"},
  {id:52, name:"At-Tur", ayahs:49, englishName:"At-Toor", arabic:"الطور"},
  {id:53, name:"An-Najm", ayahs:62, englishName:"An-Najm", arabic:"النجم"},
  {id:54, name:"Al-Muqamar", ayahs:55, englishName:"Al-Qamar", arabic:"القمر"},
  {id:55, name:"Ar-Rahman", ayahs:78, englishName:"Ar-Rahmaan", arabic:"الرحمن"},
  {id:56, name:"Al-Waqi'ah", ayahs:96, englishName:"Al-Waaqia", arabic:"الواقعة"},
  {id:57, name:"Al-Hadid", ayahs:29, englishName:"Al-Hadid", arabic:"الحديد"},
  {id:58, name:"Al-Mujadilah", ayahs:22, englishName:"Al-Mujaadila", arabic:"المجادلة"},
  {id:59, name:"Al-Hashr", ayahs:24, englishName:"Al-Hashr", arabic:"الحشر"},
  {id:60, name:"Al-Mumtahanah", ayahs:13, englishName:"Al-Mumtahana", arabic:"الممتحنة"},
  {id:61, name:"As-Saff", ayahs:14, englishName:"As-Saff", arabic:"الصف"},
  {id:62, name:"Al-Jumu'ah", ayahs:11, englishName:"Al-Jumu'a", arabic:"الجمعة"},
  {id:63, name:"Al-Munafiqun", ayahs:11, englishName:"Al-Munaafiqoon", arabic:"المنافقون"},
  {id:64, name:"At-Taghabun", ayahs:18, englishName:"At-Taghaabun", arabic:"التغابن"},
  {id:65, name:"At-Talaq", ayahs:12, englishName:"At-Talaaq", arabic:"الطلاق"},
  {id:66, name:"At-Tahrim", ayahs:12, englishName:"At-Tahreem", arabic:"التحريم"},
  {id:67, name:"Al-Mulk", ayahs:30, englishName:"Al-Mulk", arabic:"المlk"},
  {id:68, name:"Al-Qalam", ayahs:52, englishName:"Al-Qalam", arabic:"القلم"},
  {id:69, name:"Al-Haqqah", ayahs:52, englishName:"Al-Haaqqa", arabic:"الحاقة"},
  {id:70, name:"Al-Ma'arij", ayahs:44, englishName:"Al-Ma'aarij", arabic:"المعارج"},
  {id:71, name:"Nuh", ayahs:28, englishName:"Nooh", arabic:"نوح"},
  {id:72, name:"Al-Jinn", ayahs:28, englishName:"Al-Jinn", arabic:"Gen"},
  {id:73, name:"Al-Muzzammil", ayahs:20, englishName:"Al-Muzzammil", arabic:"المزمل"},
  {id:74, name:"Al-Muddaththir", ayahs:56, englishName:"Al-Muddaththir", arabic:"المدثر"},
  {id:75, name:"Al-Qiyamah", ayahs:40, englishName:"Al-Qiyaama", arabic:"القيامة"},
  {id:76, name:"Al-Insan", ayahs:31, englishName:"Al-Insaan", arabic:"الإنسان"},
  {id:77, name:"Al-Mursalat", ayahs:50, englishName:"Al-Mursalaat", arabic:"المرسلات"},
  {id:78, name:"An-Naba", ayahs:40, englishName:"An-Naba", arabic:"النبإ"},
  {id:79, name:"An-Nazi'at", ayahs:46, englishName:"An-Naazi'aat", arabic:"النازعات"},
  {id:80, name:"'Abasa", ayahs:42, englishName:"Abasa", arabic:"عبس"},
  {id:81, name:"At-Takwir", ayahs:29, englishName:"At-Takweer", arabic:"التكوير"},
  {id:82, name:"Al-Infitar", ayahs:19, englishName:"Al-Infitaar", arabic:"الانفطار"},
  {id:83, name:"Al-Mutaffifin", ayahs:36, englishName:"Al-Mutaffifeen", arabic:"المطففين"},
  {id:84, name:"Al-Inshiqaq", ayahs:25, englishName:"Al-Inshiqaaq", arabic:"الانشقاق"},
  {id:85, name:"Al-Buruj", ayahs:22, englishName:"Al-Burooj", arabic:"البروج"},
  {id:86, name:"At-Tariq", ayahs:17, englishName:"At-Taariq", arabic:"الطارق"},
  {id:87, name:"Al-A'la", ayahs:19, englishName:"Al-A'la", arabic:"الأعلى"},
  {id:88, name:"Al-Ghashiyah", ayahs:26, englishName:"Al-Ghaashiya", arabic:"الغاشية"},
  {id:89, name:"Al-Fajr", ayahs:30, englishName:"Al-Fajr", arabic:"الفجر"},
  {id:90, name:"Al-Balad", ayahs:20, englishName:"Al-Balad", arabic:"البلد"},
  {id:91, name:"Ash-Shams", ayahs:15, englishName:"Ash-Shams", arabic:"الشمس"},
  {id:92, name:"Al-Layl", ayahs:21, englishName:"Al-Layl", arabic:"الليل"},
  {id:93, name:"Ad-Duha", ayahs:11, englishName:"Ad-Dhuha", arabic:"الضحى"},
  {id:94, name:"Ash-Sharh", ayahs:8, englishName:"Ash-Sharh", arabic:"الشرح"},
  {id:95, name:"At-Tin", ayahs:8, englishName:"At-Teen", arabic:"التين"},
  {id:96, name:"Al-'Alaq", ayahs:19, englishName:"Al-Alaq", arabic:"العلق"},
  {id:97, name:"Al-Qadr", ayahs:5, englishName:"Al-Qadr", arabic:"القدر"},
  {id:98, name:"Al-Bayyinah", ayahs:8, englishName:"Al-Bayyina", arabic:"البينة"},
  {id:99, name:"Az-Zalzalah", ayahs:8, englishName:"Az-Zalzala", arabic:"الزلزلة"},
  {id:100, name:"Al-'Adiyat", ayahs:11, englishName:"Al-Aadiyaat", arabic:"العاديات"},
  {id:101, name:"Al-Qari'ah", ayahs:11, englishName:"Al-Qaari'a", arabic:"القارعة"},
  {id:102, name:"At-Takathur", ayahs:8, englishName:"At-Takaathur", arabic:"التكاثر"},
  {id:103, name:"Al-'Asr", ayahs:3, englishName:"Al-Asr", arabic:"العصر"},
  {id:104, name:"Al-Humazah", ayahs:9, englishName:"Al-Humaza", arabic:"الهمزة"},
  {id:105, name:"Al-Fil", ayahs:5, englishName:"Al-Feel", arabic:"الفيل"},
  {id:106, name:"Quraysh", ayahs:4, englishName:"Quraish", arabic:"قريش"},
  {id:107, name:"Al-Ma'un", ayahs:7, englishName:"Al-Maa'oon", arabic:"الماعون"},
  {id:108, name:"Al-Kawthar", ayahs:3, englishName:"Al-Kawthar", arabic:"الكوثر"},
  {id:109, name:"Al-Kafirun", ayahs:6, englishName:"Al-Kaafiroon", arabic:"Kafirun"},
  {id:110, name:"An-Nasr", ayahs:3, englishName:"An-Nasr", arabic:"النصر"},
  {id:111, name:"Al-Masad", ayahs:5, englishName:"Al-Masad", arabic:"المسد"},
  {id:112, name:"Al-Ikhlas", ayahs:4, englishName:"Al-Ikhlaas", arabic:"الإخلاص"},
  {id:113, name:"Al-Falaq", ayahs:5, englishName:"Al-Falaq", arabic:"الفلق"},
  {id:114, name:"An-Nas", ayahs:6, englishName:"An-Naas", arabic:"الناس"}
];

// Global audio, play state, active audio indices
let audioPlayer = null;
let currentPlayingIndex = -1; 
let fetchedAyahs = []; 
let fetchedTranslations = {}; 
let activeMenuAyahKey = null;
let isRepeatActive = false;

// ===== INITIALIZATION =====
document.addEventListener('DOMContentLoaded', () => {
  audioPlayer = document.getElementById('audio-player');
  
  // LocalStorage'dan ayarları yükle
  loadPersistedState();
  
  // UI Kurulumları
  initUiElements();
  applyStateToUi();
  
  // İlk veri çekme
  fetchCurrentData();

  // Audio bitiş dinleyicisi
  audioPlayer.addEventListener('ended', () => {
    if (isRepeatActive) {
      audioPlayer.currentTime = 0;
      audioPlayer.play().catch(console.error);
    } else {
      playNextAyah();
    }
  });

  // Hata durumunda koruma
  audioPlayer.addEventListener('error', () => {
    console.error("Ses yükleme hatası oluştu.");
    document.getElementById('play-icon').style.display = 'block';
    document.getElementById('pause-icon').style.display = 'none';
  });
});

function loadPersistedState() {
  try {
    const saved = localStorage.getItem('dijital_kuran_state_v2');
    if (saved) {
      const parsed = JSON.parse(saved);
      appState = { ...appState, ...parsed };
    }
  } catch (e) { console.error(e); }
}

function persistState() {
  try {
    localStorage.setItem('dijital_kuran_state_v2', JSON.stringify({
      currentSurah: appState.currentSurah,
      currentJuz: appState.currentJuz,
      currentPage: appState.currentPage,
      tab: appState.tab,
      showTranslation: appState.showTranslation,
      theme: appState.theme,
      arabicFontSize: appState.arabicFontSize,
      translationFontSize: appState.translationFontSize,
      fontFamily: appState.fontFamily,
      reciter: appState.reciter,
      translations: appState.translations,
      bookmarks: appState.bookmarks
    }));
  } catch (e) { console.error(e); }
}

function initUiElements() {
  // Okuyucu listesi
  const opts = RECITERS.map(r => `<option value="${r.id}">${escHtml(r.name)}</option>`).join('');
  document.getElementById('sidebar-reciter').innerHTML = opts;
  document.getElementById('settings-reciter').innerHTML = opts;

  // Font listesi
  const fontSelect = document.getElementById('font-select');
  if (fontSelect) {
    fontSelect.innerHTML = FONTS.map(f =>
      `<option value="${f.id}" ${appState.fontFamily === f.id ? 'selected' : ''}>${escHtml(f.name)}</option>`
    ).join('');
  }

  // Meal listesi
  document.getElementById('translations-grid').innerHTML = TRANSLATIONS.map(t => {
    const checked = appState.translations.includes(t.id);
    return `<button class="translation-checkbox-btn${checked ? ' active' : ''}" data-trans="${t.id}" onclick="toggleTranslation('${t.id}')">
      <input type="checkbox" ${checked ? 'checked' : ''} onclick="event.stopPropagation()">
      <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escHtml(t.name)}</span>
    </button>`;
  }).join('');
}

function applyStateToUi() {
  // Temayı uygula
  document.body.className = '';
  if (appState.theme !== 'default') {
    document.body.classList.add(`theme-${appState.theme}`);
  }

  // Sidebar sekmeleri
  ['surah', 'juz', 'page'].forEach(t => {
    const btn = document.getElementById(`tab-${t}`);
    if (btn) {
      if (t === appState.tab) btn.classList.add('active');
      else btn.classList.remove('active');
    }
  });

  // Buton etiketleri ve görünürlükler
  document.getElementById('meal-btn-label').innerText = appState.showTranslation ? "Meal Açık" : "Meal Kapalı";
  document.getElementById('settings-translation-toggle').innerText = appState.showTranslation ? "Meal Görünümünü Kapat" : "Meal Görünümünü Aç";
  
  const wrapper = document.getElementById('translations-selection-wrapper');
  if (appState.showTranslation) wrapper.classList.remove('translations-disabled');
  else wrapper.classList.add('translations-disabled');

  // Değer atamaları
  document.getElementById('sidebar-reciter').value = appState.reciter;
  document.getElementById('settings-reciter').value = appState.reciter;
  
  // Tema butonları
  ['default', 'sepia', 'dark'].forEach(th => {
    const btn = document.getElementById(`theme-btn-${th}`);
    if (btn) {
      if (appState.theme === th) btn.classList.add('active');
      else btn.classList.remove('active');
    }
  });

  // Tema ikonu
  if (appState.theme === 'dark') {
    document.getElementById('theme-icon-moon').style.display = 'none';
    document.getElementById('theme-icon-sun').style.display = 'block';
  } else {
    document.getElementById('theme-icon-moon').style.display = 'block';
    document.getElementById('theme-icon-sun').style.display = 'none';
  }

  // Panel Gösterimleri
  document.getElementById('bookmarks-panel').style.display = appState.showBookmarks ? 'block' : 'none';

  renderLeftSidebarList();
  renderBookmarks();
}

function setState(key, value) {
  appState[key] = value;
  
  // Tip dönüşümü koruması
  if(['currentSurah', 'currentJuz', 'currentPage', 'arabicFontSize', 'translationFontSize'].includes(key)) {
    appState[key] = parseInt(appState[key], 10);
  }

  persistState();
  applyStateToUi();

  if (['currentSurah', 'currentJuz', 'currentPage', 'translations', 'reciter', 'tab'].includes(key)) {
    stopAudio();
    fetchCurrentData();
  } else if (['arabicFontSize', 'translationFontSize', 'fontFamily'].includes(key)) {
    applyStylesDynamically();
  }
}

function applyStylesDynamically() {
  const mushafContainer = document.getElementById('mushaf-content');
  if (!mushafContainer) return;

  // Arapça kelimeler için font ve boyutu ayarla
  const arabics = mushafContainer.querySelectorAll('.arabic, .ayah-row, .mushaf-flow');
  arabics.forEach(el => {
    el.style.fontFamily = `"${appState.fontFamily}", serif`;
    el.style.fontSize = `${appState.arabicFontSize}px`;
  });

  // Mealler için font boyutunu ayarla
  const translations = mushafContainer.querySelectorAll('.ayah-translation');
  translations.forEach(el => {
    el.style.fontSize = `${appState.translationFontSize}px`;
  });
}

function cycleTheme() {
  const next = { 'default': 'sepia', 'sepia': 'dark', 'dark': 'default' };
  setState('theme', next[appState.theme] || 'default');
}

function toggleTranslation(id) {
  let trans = [...appState.translations];
  if (trans.includes(id)) {
    trans = trans.filter(t => t !== id);
    if (!trans.length) trans = [TRANSLATIONS[0].id]; // En az bir tane seçili kalmalı
  } else {
    trans.push(id);
  }
  setState('translations', trans);
}

// ===== NAVIGATION & SIDEBAR =====
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('backdrop').classList.toggle('open');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('backdrop').classList.remove('open');
}
function openSettings() { document.getElementById('settings-overlay').classList.add('open'); }
function closeSettings() { document.getElementById('settings-overlay').classList.remove('open'); }

function setTab(t) {
  setState('tab', t);
}

function filterList(query) {
  renderLeftSidebarList(query.toLowerCase().trim());
}

function renderLeftSidebarList(filter = '') {
  const container = document.getElementById('list-container');
  if (!container) return;

  let html = '';
  if (appState.tab === 'surah') {
    const filtered = SURAHS.filter(s => s.name.toLowerCase().includes(filter) || s.englishName.toLowerCase().includes(filter));
    html = filtered.map(s => `
      <div class="list-item${appState.currentSurah === s.id ? ' active' : ''}" onclick="setState('currentSurah', ${s.id}); closeSidebar();">
        <div class="item-info">
          <div class="item-num">${s.id}</div>
          <div>
            <span class="item-name">${escHtml(s.name)}</span>
            <span class="item-sub">${s.ayahs} Ayet / ${escHtml(s.englishName)}</span>
          </div>
        </div>
        <div class="item-arabic">${s.arabic}</div>
      </div>
    `).join('');
  } else if (appState.tab === 'juz') {
    for (let i = 1; i <= 30; i++) {
      if (filter && !`cüz ${i}`.includes(filter) && !`${i}`.includes(filter)) continue;
      html += `
        <div class="list-item${appState.currentJuz === i ? ' active' : ''}" onclick="setState('currentJuz', ${i}); closeSidebar();">
          <div class="item-info">
            <div class="item-num">${i}</div>
            <div><span class="item-name">${i}. Cüz</span></div>
          </div>
        </div>
      `;
    }
  } else if (appState.tab === 'page') {
    for (let i = 1; i <= 604; i++) {
      if (filter && !`sayfa ${i}`.includes(filter) && !`${i}`.includes(filter)) continue;
      html += `
        <div class="list-item${appState.currentPage === i ? ' active' : ''}" onclick="setState('currentPage', ${i}); closeSidebar();">
          <div class="item-info">
            <div class="item-num">${i}</div>
            <div><span class="item-name">Sayfa ${i}</span></div>
          </div>
        </div>
      `;
    }
  }
  container.innerHTML = html;
}

function navPage(dir) {
  stopAudio();
  if (appState.tab === 'surah') {
    let next = appState.currentSurah + dir;
    if (next < 1) next = 114;
    if (next > 114) next = 1;
    setState('currentSurah', next);
  } else if (appState.tab === 'juz') {
    let next = appState.currentJuz + dir;
    if (next < 1) next = 30;
    if (next > 30) next = 1;
    setState('currentJuz', next);
  } else if (appState.tab === 'page') {
    let next = appState.currentPage + dir;
    if (next < 1) next = 604;
    if (next > 604) next = 1;
    setState('currentPage', next);
  }
}

// ===== DATA FETCHING =====
async function fetchCurrentData() {
  const loading = document.getElementById('loading');
  const content = document.getElementById('mushaf-content');
  
  if (loading) loading.classList.add('show');
  if (content) content.innerHTML = '';

  updateHeaderTitle();

  let pathStr = '';
  if (appState.tab === 'surah') {
    pathStr = `surah/${appState.currentSurah}/editions/quran-simple-clean`;
  } else if (appState.tab === 'juz') {
    pathStr = `juz/${appState.currentJuz}/editions/quran-simple-clean`;
  } else if (appState.tab === 'page') {
    pathStr = `page/${appState.currentPage}/editions/quran-simple-clean`;
  }

  if (appState.showTranslation && appState.translations.length) {
    pathStr += ',' + appState.translations.join(',');
  }

  try {
    const res = await fetch(`?api=${encodeURIComponent(pathStr)}`);
    if (!res.ok) throw new Error("API hatası");
    const jsonResult = await res.json();
    
    if (jsonResult.code === 200 && jsonResult.data) {
      processApiData(jsonResult.data);
    } else {
      if (content) content.innerHTML = `<p style="color:var(--destructive);padding:20px;">Veri alınamadı: ${escHtml(jsonResult.data || 'Bilinmeyen hata')}</p>`;
    }
  } catch (err) {
    console.error(err);
    if (content) content.innerHTML = '<p style="color:var(--destructive);padding:20px;">Sunucu veya internet bağlantı hatası oluştu.</p>';
  } finally {
    if (loading) loading.classList.remove('show');
  }
}

function updateHeaderTitle() {
  const titleEl = document.getElementById('page-title');
  if (!titleEl) return;

  if (appState.tab === 'surah') {
    const s = SURAHS.find(x => x.id === appState.currentSurah);
    titleEl.innerText = s ? `${s.id}. ${s.name} Suresi` : "Kur'an-ı Kerim";
  } else if (appState.tab === 'juz') {
    titleEl.innerText = `${appState.currentJuz}. Cüz`;
  } else if (appState.tab === 'page') {
    titleEl.innerText = `Sayfa ${appState.currentPage}`;
  }
}

function processApiData(data) {
  fetchedAyahs = [];
  fetchedTranslations = {};

  // Gelen verileri normalize et (Tek veya çoklu edition durumları için)
  if (appState.showTranslation && appState.translations.length) {
    // Array gelmesi beklenir
    const arabicEd = data.find(ed => ed.edition.type === 'quran');
    if (!arabicEd) return;

    fetchedAyahs = arabicEd.ayahs;
    
    data.forEach(ed => {
      if (ed.edition.type === 'translation') {
        fetchedTranslations[ed.edition.identifier] = {
          name: ed.edition.name,
          ayahs: ed.ayahs
        };
      }
    });
  } else {
    // Sadece arapça metin gelmiştir
    fetchedAyahs = data.ayahs || [];
  }

  renderMushaf();
}

// ===== RENDERING =====
function renderMushaf() {
  const container = document.getElementById('mushaf-content');
  if (!container) return;

  if (!fetchedAyahs.length) {
    container.innerHTML = '<p style="padding:20px;">Bu bölümde ayet bulunamadı.</p>';
    return;
  }

  let html = '<div class="mushaf-frame">';

  // Eğer Sure modundaysak ve Fatiha veya Tevbe değilse Besmele ekle
  if (appState.tab === 'surah' && appState.currentSurah !== 1 && appState.currentSurah !== 9) {
    html += `<div class="basmala arabic">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</div><div class="mushaf-divider"></div>`;
  }

  if (!appState.showTranslation) {
    // --- MEALSİZ / AKICI OKUMA MODU ---
    html += '<div class="mushaf-flow">';
    fetchedAyahs.forEach((ayah, index) => {
      let text = ayah.text;
      // Fatiha hariç ilk ayetlerde besmeleyi temizle
      if (appState.tab === 'surah' && ayah.numberInSurah === 1 && appState.currentSurah !== 1) {
        text = text.replace(/^بِسْمِ اللَّهِ الرَّحْمَٰnِ الرَّحِيمِ\s*/, '');
      }
      
      const key = `${ayah.surah.number}:${ayah.numberInSurah}`;
      const isBookmarked = appState.bookmarks.some(b => b.surah === ayah.surah.number && b.ayah === ayah.numberInSurah);
      
      html += `
        <span class="ayah" id="ayah-text-${index}" onclick="handleAyahClick(${index}, event)" data-key="${key}">
          ${text}
          <span class="ayah-num">﴿${formatArabicNumber(ayah.numberInSurah)}﴾</span>
        </span>
        <span class="ayah-inline-controls" id="controls-${index}" onclick="event.stopPropagation()">
          <span class="ayah-dots" onclick="toggleAyahMenu(${index})">⋮</span>
          <div class="ayah-menu" id="menu-${index}">
            <button onclick="playSingleAyah(${index})" title="Dinle">▶</button>
            <button class="${isBookmarked?'active':''}" onclick="toggleBookmarkBtn(${ayah.surah.number}, ${ayah.numberInSurah}, ${index})" title="Yer İmi">🔖</button>
            <button onclick="copyToClipboard('${key}', ${index})" title="Kopyala">📋</button>
          </div>
        </span>
      `;
    });
    html += '</div>';
  } else {
    // --- MEALLİ / SATIR SATIR OKUMA MODU ---
    fetchedAyahs.forEach((ayah, index) => {
      let text = ayah.text;
      if (appState.tab === 'surah' && ayah.numberInSurah === 1 && appState.currentSurah !== 1) {
        text = text.replace(/^بِسْمِ اللَّهِ الرَّحْمَٰnِ الرَّحِيمِ\s*/, '');
      }

      const key = `${ayah.surah.number}:${ayah.numberInSurah}`;
      const isBookmarked = appState.bookmarks.some(b => b.surah === ayah.surah.number && b.ayah === ayah.numberInSurah);

      let transHtml = '';
      appState.translations.forEach(transId => {
        const transData = fetchedTranslations[transId];
        if (transData && transData.ayahs[index]) {
          const sName = appState.tab === 'surah' ? '' : ` (${escHtml(ayah.surah.englishName)})`;
          transHtml += `
            <div class="ayah-translation">
              <div class="translation-label">${escHtml(transData.name)}</div>
              <span class="ayah-num-label">${ayah.surah.number}:${ayah.numberInSurah}${sName}</span>
              ${escHtml(transData.ayahs[index].text)}
            </div>
          `;
        }
      });

      html += `
        <div class="ayah-block" id="ayah-block-${index}">
          <div class="ayah-row">
            <span class="ayah" id="ayah-text-${index}" onclick="handleAyahClick(${index}, event)" data-key="${key}">
              ${text}
              <span class="ayah-num">﴿${formatArabicNumber(ayah.numberInSurah)}﴾</span>
            </span>
            <span class="ayah-inline-controls" onclick="event.stopPropagation()">
              <span class="ayah-dots" onclick="toggleAyahMenu(${index})">⋮</span>
              <div class="ayah-menu" id="menu-${index}">
                <button onclick="playSingleAyah(${index})" title="Dinle">▶</button>
                <button class="${isBookmarked?'active':''}" onclick="toggleBookmarkBtn(${ayah.surah.number}, ${ayah.numberInSurah}, ${index})" title="Yer İmi">🔖</button>
                <button onclick="copyToClipboard('${key}', ${index})" title="Kopyala">📋</button>
              </div>
            </span>
          </div>
          ${transHtml}
        </div>
        <div class="mushaf-divider"></div>
      `;
    });
  }

  html += '</div>';
  container.innerHTML = html;
  
  applyStylesDynamically();
  closeAllMenus();
}

// ===== AUDIO PLAYER CONTROLS =====
function setSpeed(v) {
  audioPlayer.playbackRate = parseFloat(v);
}

function toggleRepeat() {
  isRepeatActive = !isRepeatActive;
  document.getElementById('repeat-btn').classList.toggle('on', isRepeatActive);
  showToast(isRepeatActive ? "Ayet tekrarı açıldı" : "Ayet tekrarı kapatıldı");
}

function handleAyahClick(index, event) {
  if (event.target.classList.contains('ayah-dots') || event.target.closest('.ayah-menu')) return;
  
  if (currentPlayingIndex === index && !audioPlayer.paused) {
    pauseAudio();
  } else {
    playSingleAyah(index);
  }
}

function playSingleAyah(index) {
  closeAllMenus();
  if (index < 0 || index >= fetchedAyahs.length) return;

  // Eski aktifi temizle
  removeAudioHighlight();

  currentPlayingIndex = index;
  const ayah = fetchedAyahs[index];
  
  // Örn: https://cdn.alquran.cloud/media/audio/ayah/ar.alafasy/262
  audioPlayer.src = `https://cdn.alquran.cloud/media/audio/ayah/${appState.reciter}/${ayah.number}`;
  setSpeed(document.getElementById('speed-select').value);
  
  // Arayüzü güncelle ve oynat
  addAudioHighlight(index);
  
  document.getElementById('play-icon').style.display = 'none';
  document.getElementById('pause-icon').style.display = 'block';

  audioPlayer.play().catch(err => {
    console.error("Ses oynatılamadı:", err);
    playNextAyah();
  });
}

function playNextAyah() {
  if (currentPlayingIndex + 1 < fetchedAyahs.length) {
    playSingleAyah(currentPlayingIndex + 1);
  } else {
    stopAudio();
    // Otomatik sonraki sayfaya geçiş mantığı eklenebilir
  }
}

function togglePlay() {
  if (!fetchedAyahs.length) return;
  
  if (audioPlayer.paused) {
    if (currentPlayingIndex === -1) {
      playSingleAyah(0);
    } else {
      audioPlayer.play().catch(console.error);
      document.getElementById('play-icon').style.display = 'none';
      document.getElementById('pause-icon').style.display = 'block';
    }
  } else {
    pauseAudio();
  }
}

function pauseAudio() {
  audioPlayer.pause();
  document.getElementById('play-icon').style.display = 'block';
  document.getElementById('pause-icon').style.display = 'none';
}

function stopAudio() {
  audioPlayer.pause();
  audioPlayer.currentTime = 0;
  removeAudioHighlight();
  currentPlayingIndex = -1;
  document.getElementById('play-icon').style.display = 'block';
  document.getElementById('pause-icon').style.display = 'none';
}

function addAudioHighlight(index) {
  const el = document.getElementById(`ayah-text-${index}`);
  if (el) {
    el.classList.add('active');
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
}

function removeAudioHighlight() {
  if (currentPlayingIndex !== -1) {
    const el = document.getElementById(`ayah-text-${currentPlayingIndex}`);
    if (el) el.classList.remove('active');
  }
}

// ===== INLINE MENU & ACTIONS =====
function toggleAyahMenu(index) {
  const menu = document.getElementById(`menu-${index}`);
  if (!menu) return;

  const isOpen = menu.classList.contains('open');
  closeAllMenus();

  if (!isOpen) {
    menu.classList.add('open');
    activeMenuAyahKey = index;
    
    // Menünün dışına tıklayınca kapanması için
    setTimeout(() => {
      document.addEventListener('click', closeAllMenusOutside);
    }, 10);
  }
}

function closeAllMenus() {
  document.querySelectorAll('.ayah-menu').forEach(m => m.classList.remove('open'));
  document.removeEventListener('click', closeAllMenusOutside);
  activeMenuAyahKey = null;
}

function closeAllMenusOutside(e) {
  if (!e.target.classList.contains('ayah-dots') && !e.target.closest('.ayah-menu')) {
    closeAllMenus();
  }
}

function toggleBookmarkBtn(surahNum, ayahNum, index) {
  let list = [...appState.bookmarks];
  const idx = list.findIndex(b => b.surah === surahNum && b.ayah === ayahNum);
  
  if (idx !== -1) {
    list.splice(idx, 1);
    showToast("Yer imi kaldırıldı");
  } else {
    const sObj = SURAHS.find(s => s.id === surahNum);
    list.push({
      surah: surahNum,
      ayah: ayahNum,
      surahName: sObj ? sObj.name : `Sure ${surahNum}`,
      timestamp: Date.now()
    });
    showToast("Yer imi eklendi");
  }
  
  setState('bookmarks', list);
}

function renderBookmarks() {
  const listEl = document.getElementById('bookmarks-list');
  if (!listEl) return;

  if (!appState.bookmarks.length) {
    listEl.innerHTML = '<p style="font-size:13px;color:var(--muted-foreground)">Henüz kayıtlı yer iminiz yok. Ayetlerin yanındaki menüden (⋮) ekleyebilirsiniz.</p>';
    return;
  }

  // Tarihe göre sırala
  const sorted = [...appState.bookmarks].sort((a,b) => b.timestamp - a.timestamp);
  
  listEl.innerHTML = sorted.map(b => `
    <div style="display:flex;align-items:center;justify-content:between;padding:8px 0;border-bottom:1px solid var(--border);font-size:14px;">
      <span style="flex:1;cursor:pointer;font-weight:500;" onclick="goToBookmark(${b.surah}, ${b.ayah})">
        🔖 ${escHtml(b.surahName)} - Ayet ${b.ayah}
      </span>
      <button style="color:var(--destructive);font-size:12px;padding:2px 6px;" onclick="removeBookmarkDirect(${b.surah}, ${b.ayah})">Sil</button>
    </div>
  `).join('');
}

function removeBookmarkDirect(surahNum, ayahNum) {
  const updated = appState.bookmarks.filter(b => !(b.surah === surahNum && b.ayah === ayahNum));
  setState('bookmarks', updated);
}

function goToBookmark(surahNum, ayahNum) {
  stopAudio();
  // Yer imine gitmek için uygun sekmeyi bul ve yükle
  appState.tab = 'surah';
  appState.currentSurah = surahNum;
  persistState();
  applyStateToUi();
  
  fetchCurrentData().then(() => {
    // Ayet yüklendikten sonra odaklan ve sesini çal
    setTimeout(() => {
      const idx = fetchedAyahs.findIndex(a => a.numberInSurah === ayahNum);
      if (idx !== -1) {
        playSingleAyah(idx);
      }
    }, 400);
  });
}

function copyToClipboard(key, index) {
  closeAllMenus();
  const textEl = document.getElementById(`ayah-text-${index}`);
  if (!textEl) return;

  // Arapça metni alıp harici boşlukları temizle
  let cleanText = textEl.textContent || textEl.innerText;
  cleanText = cleanText.replace(/\s+/g, ' ').trim();

  // Aktif mealleri de kopyalamak üzere topla
  let fullTextToCopy = `(${key}) ${cleanText}`;
  
  if (appState.showTranslation) {
    const block = document.getElementById(`ayah-block-${index}`);
    if (block) {
      const transEls = block.querySelectorAll('.ayah-translation');
      transEls.forEach(el => {
        let tText = el.textContent || el.innerText;
        tText = tText.replace(/\s+/g, ' ').trim();
        fullTextToCopy += `\n\n${tText}`;
      });
    }
  }

  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(fullTextToCopy).then(() => {
      showToast("Ayet metni kopyalandı");
    }).catch(err => {
      fallbackCopy(fullTextToCopy);
    });
  } else {
    fallbackCopy(fullTextToCopy);
  }
}

function fallbackCopy(text) {
  const ta = document.createElement('textarea');
  ta.value = text;
  ta.style.position = 'fixed'; ta.style.opacity = '0';
  document.body.appendChild(ta);
  ta.select();
  try {
    document.execCommand('copy');
    showToast("Ayet metni kopyalandı");
  } catch (e) {
    console.error(e);
  }
  document.body.removeChild(ta);
}

function showToast(msg) {
  const toast = document.getElementById('copied-toast');
  if (!toast) return;
  toast.innerText = msg;
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 2000);
}

// ===== UTILS =====
function escHtml(str) {
  if (!str) return '';
  return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function formatArabicNumber(num) {
  const arabicDigits = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
  return num.toString().split('').map(d => isNaN(parseInt(d)) ? d : arabicDigits[parseInt(d)]).join('');
}
</script>
</body>
</html>""")