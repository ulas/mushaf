<?php
// API proxy endpoint
if (isset($_GET['api'])) {
    $path = $_GET['api'];
  if (!preg_match('/^(surah|juz|page)\/[\d,]+(\/editions)?\/[\w.,\-]+$/', $path)) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array('error' => 'Gecersiz API yolu'));
        exit;
    }
    $url = 'https://api.alquran.cloud/v1/' . $path;
    $data = false;

    // cURL ile dene (tercih edilen)
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'DijitalKuran/1.0');
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($data === false || $httpCode < 200 || $httpCode >= 300) $data = false;
    }

    // file_get_contents ile dene (yedek)
    if ($data === false && ini_get('allow_url_fopen')) {
        $ctx = stream_context_create(array('http' => array(
            'timeout' => 20,
            'header' => 'User-Agent: DijitalKuran/1.0'
        )));
        $data = @file_get_contents($url, false, $ctx);
    }

    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    if ($data === false) {
        http_response_code(502);
        echo json_encode(array('error' => 'API baglanti hatasi'));
    } else {
        echo $data;
    }
    exit;
}
?>
<!DOCTYPE html>
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
  <!-- Backdrop -->
  <div id="backdrop" onclick="closeSidebar()"></div>

  <!-- Sidebar -->
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

    <!-- Reciter -->
    <div class="reciter-section">
      <label>Kâri (Okuyucu)</label>
      <select id="sidebar-reciter" onchange="setState('reciter', this.value)"></select>
    </div>

    <!-- Quick actions -->
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

    <!-- Search -->
    <div class="search-row">
      <input type="text" id="search-input" placeholder="Sure ara..." oninput="filterList(this.value)">
      <button aria-label="Ara">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
      </button>
    </div>

    <!-- Tabs -->
    <div class="tab-row">
      <button class="tab-btn active" id="tab-surah" onclick="setTab('surah')">Sure</button>
      <button class="tab-btn" id="tab-juz" onclick="setTab('juz')">Cüz</button>
      <button class="tab-btn" id="tab-page" onclick="setTab('page')">Sayfa</button>
    </div>

    <!-- List -->
    <div class="list-container" id="list-container"></div>
  </aside>

  <!-- Main -->
  <main id="main">
    <header>
      <button id="settings-btn" onclick="toggleSidebar()" title="Sure listesi / Ayarlar">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
      </button>

      <h1 id="page-title">1. Fâtiha Sûresi</h1>

      <div style="display:flex;gap:4px">
        <button class="nav-btn" onclick="navPrev()">Önceki</button>
        <button class="nav-btn" onclick="navNext()">Sonraki</button>
      </div>

      <div class="player-controls">
        <button onclick="audioPrev()" title="Önceki âyet">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="19 20 9 12 19 4 19 20"/><line x1="5" y1="19" x2="5" y2="5"/></svg>
        </button>
        <button id="play-btn" onclick="togglePlay()" title="Oynat / Durdur">
          <svg id="icon-play" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          <svg id="icon-pause" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="none" style="display:none"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
        </button>
        <button onclick="audioNext()" title="Sonraki âyet">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 4 15 12 5 20 5 4"/><line x1="19" y1="5" x2="19" y2="19"/></svg>
        </button>
        <select id="speed-select" onchange="setSpeed(parseFloat(this.value))">
          <option value="0.75">0.75x</option>
          <option value="1" selected>1x</option>
          <option value="1.25">1.25x</option>
          <option value="1.5">1.5x</option>
          <option value="1.75">1.75x</option>
          <option value="2">2x</option>
        </select>
        <button id="repeat-btn" onclick="toggleRepeat()" title="Sürekli tekrar">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m17 2 4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><path d="m7 22-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
        </button>
        <button onclick="toggleMute()" id="mute-btn" title="Sesi kapat/aç">
          <svg id="icon-vol" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/></svg>
          <svg id="icon-mute" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><line x1="22" y1="9" x2="16" y2="15"/><line x1="16" y1="9" x2="22" y2="15"/></svg>
        </button>
      </div>
    </header>

    <!-- Reader -->
    <section id="reader">
      <div id="loading" class="show">Yükleniyor...</div>
      <div id="bookmarks-panel">
        <h2>Yer İmleri</h2>
        <div id="bookmarks-list"></div>
      </div>
      <div id="content"></div>
    </section>
  </main>
</div>

<!-- Settings Modal -->
<div id="settings-overlay" onclick="closeSettingsOutside(event)">
  <div id="settings-modal">
    <div class="settings-header">
      <h2>Ayarlar</h2>
      <button onclick="closeSettings()">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <!-- Font size -->
    <div class="settings-section">
      <label class="settings-label">Yazı Boyutu: <span id="font-size-label">34</span>px</label>
      <input type="range" min="20" max="60" value="34" id="font-size-range" oninput="setState('fontSize', parseInt(this.value)); document.getElementById('font-size-label').textContent = this.value; renderContent()">
    </div>

    <!-- Font family -->
    <div class="settings-section">
      <label class="settings-label">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>
        Yazı Tipi
      </label>
      <select id="font-select" class="reciter-select-settings" onchange="setState('fontFamily', this.value); renderContent();"></select>
    </div>

    <!-- Translation -->
    <div class="settings-section">
      <label class="settings-label">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 8 6 6"/><path d="m4 14 6-6 2-3"/><path d="M2 5h12"/><path d="M7 2h1"/><path d="m22 22-5-10-5 10"/><path d="M14 18h6"/></svg>
        Meal
      </label>
      <button id="meal-toggle-btn" class="translation-toggle-btn active" onclick="setState('showTranslation', !appState.showTranslation); renderContent()">Meal Açık</button>
      <div class="translations-grid" id="translations-grid"></div>
      <p class="translations-hint">Birden fazla meal seçebilirsiniz.</p>
    </div>

    <!-- Reciter in settings -->
    <div class="settings-section">
      <label class="settings-label">Kâri (Okuyucu)</label>
      <select class="reciter-select-settings" id="settings-reciter" onchange="setState('reciter', this.value); document.getElementById('sidebar-reciter').value = this.value"></select>
    </div>

    <!-- Theme -->
    <div class="settings-section">
      <label class="settings-label">Tema</label>
      <div class="theme-grid">
        <button class="theme-btn active" id="theme-btn-light" onclick="setState('theme', 'light')">Mushaf</button>
        <button class="theme-btn" id="theme-btn-sepia" onclick="setState('theme', 'sepia')">Beyaz</button>
        <button class="theme-btn" id="theme-btn-dark" onclick="setState('theme', 'dark')">Karanlık</button>
      </div>
    </div>

    <!-- Repeat -->
    <div class="settings-section">
      <label class="settings-label">Tekrar: <span id="repeat-label">1</span>x</label>
      <input type="range" min="1" max="10" value="1" id="repeat-range" oninput="appState.repeat = parseInt(this.value); document.getElementById('repeat-label').textContent = this.value">
    </div>
  </div>
</div>

<!-- Copied toast -->
<div class="copied-toast" id="copied-toast">Kopyalandı!</div>

<!-- Audio -->
<audio id="audio" preload="auto"></audio>

<script>
// ===== DATA =====
const SURAHS = [
  {number:1,name:"Fâtiha",arabicName:"الْفَاتِحَةِ",ayahs:7,type:"Mekki"},
  {number:2,name:"Bakara",arabicName:"الْبَقَرَةِ",ayahs:286,type:"Medeni"},
  {number:3,name:"Âl-i İmrân",arabicName:"آلِ عِمْرَانَ",ayahs:200,type:"Medeni"},
  {number:4,name:"Nisâ",arabicName:"النِّسَاءِ",ayahs:176,type:"Medeni"},
  {number:5,name:"Mâide",arabicName:"الْمَائِدَةِ",ayahs:120,type:"Medeni"},
  {number:6,name:"En'âm",arabicName:"الْأَنْعَامِ",ayahs:165,type:"Mekki"},
  {number:7,name:"A'râf",arabicName:"الْأَعْرَافِ",ayahs:206,type:"Mekki"},
  {number:8,name:"Enfâl",arabicName:"الْأَنْفَالِ",ayahs:75,type:"Medeni"},
  {number:9,name:"Tevbe",arabicName:"التَّوْبَةِ",ayahs:129,type:"Medeni"},
  {number:10,name:"Yûnus",arabicName:"يُونُسَ",ayahs:109,type:"Mekki"},
  {number:11,name:"Hûd",arabicName:"هُودٍ",ayahs:123,type:"Mekki"},
  {number:12,name:"Yûsuf",arabicName:"يُوسُفَ",ayahs:111,type:"Mekki"},
  {number:13,name:"Ra'd",arabicName:"الرَّعْدِ",ayahs:43,type:"Medeni"},
  {number:14,name:"İbrâhîm",arabicName:"إِبْرَاهِيمَ",ayahs:52,type:"Mekki"},
  {number:15,name:"Hicr",arabicName:"الْحِجْرِ",ayahs:99,type:"Mekki"},
  {number:16,name:"Nahl",arabicName:"النَّحْلِ",ayahs:128,type:"Mekki"},
  {number:17,name:"İsrâ",arabicName:"الْإِسْرَاءِ",ayahs:111,type:"Mekki"},
  {number:18,name:"Kehf",arabicName:"الْكَهْفِ",ayahs:110,type:"Mekki"},
  {number:19,name:"Meryem",arabicName:"مَرْيَمَ",ayahs:98,type:"Mekki"},
  {number:20,name:"Tâhâ",arabicName:"طٰهٰ",ayahs:135,type:"Mekki"},
  {number:21,name:"Enbiyâ",arabicName:"الْأَنْبِيَاءِ",ayahs:112,type:"Mekki"},
  {number:22,name:"Hac",arabicName:"الْحَجِّ",ayahs:78,type:"Medeni"},
  {number:23,name:"Mü'minûn",arabicName:"الْمُؤْمِنُونَ",ayahs:118,type:"Mekki"},
  {number:24,name:"Nûr",arabicName:"النُّورِ",ayahs:64,type:"Medeni"},
  {number:25,name:"Furkân",arabicName:"الْفُرْقَانِ",ayahs:77,type:"Mekki"},
  {number:26,name:"Şuarâ",arabicName:"الشُّعَرَاءِ",ayahs:227,type:"Mekki"},
  {number:27,name:"Neml",arabicName:"النَّمْلِ",ayahs:93,type:"Mekki"},
  {number:28,name:"Kasas",arabicName:"الْقَصَصِ",ayahs:88,type:"Mekki"},
  {number:29,name:"Ankebût",arabicName:"الْعَنْكَبُوتِ",ayahs:69,type:"Mekki"},
  {number:30,name:"Rûm",arabicName:"الرُّومِ",ayahs:60,type:"Mekki"},
  {number:31,name:"Lokmân",arabicName:"لُقْمَانَ",ayahs:34,type:"Mekki"},
  {number:32,name:"Secde",arabicName:"السَّجْدَةِ",ayahs:30,type:"Mekki"},
  {number:33,name:"Ahzâb",arabicName:"الْأَحْزَابِ",ayahs:73,type:"Medeni"},
  {number:34,name:"Sebe'",arabicName:"سَبَإٍ",ayahs:54,type:"Mekki"},
  {number:35,name:"Fâtır",arabicName:"فَاطِرٍ",ayahs:45,type:"Mekki"},
  {number:36,name:"Yâsîn",arabicName:"يٰسٓ",ayahs:83,type:"Mekki"},
  {number:37,name:"Sâffât",arabicName:"الصَّافَّاتِ",ayahs:182,type:"Mekki"},
  {number:38,name:"Sâd",arabicName:"صٓ",ayahs:88,type:"Mekki"},
  {number:39,name:"Zümer",arabicName:"الزُّمَرِ",ayahs:75,type:"Mekki"},
  {number:40,name:"Mü'min",arabicName:"غَافِرٍ",ayahs:85,type:"Mekki"},
  {number:41,name:"Fussilet",arabicName:"فُصِّلَتْ",ayahs:54,type:"Mekki"},
  {number:42,name:"Şûrâ",arabicName:"الشُّورَىٰ",ayahs:53,type:"Mekki"},
  {number:43,name:"Zuhruf",arabicName:"الزُّخْرُفِ",ayahs:89,type:"Mekki"},
  {number:44,name:"Duhân",arabicName:"الدُّخَانِ",ayahs:59,type:"Mekki"},
  {number:45,name:"Câsiye",arabicName:"الْجَاثِيَةِ",ayahs:37,type:"Mekki"},
  {number:46,name:"Ahkâf",arabicName:"الْأَحْقَافِ",ayahs:35,type:"Mekki"},
  {number:47,name:"Muhammed",arabicName:"مُحَمَّدٍ",ayahs:38,type:"Medeni"},
  {number:48,name:"Fetih",arabicName:"الْفَتْحِ",ayahs:29,type:"Medeni"},
  {number:49,name:"Hucurât",arabicName:"الْحُجُرَاتِ",ayahs:18,type:"Medeni"},
  {number:50,name:"Kâf",arabicName:"قٓ",ayahs:45,type:"Mekki"},
  {number:51,name:"Zâriyât",arabicName:"الذَّارِيَاتِ",ayahs:60,type:"Mekki"},
  {number:52,name:"Tûr",arabicName:"الطُّورِ",ayahs:49,type:"Mekki"},
  {number:53,name:"Necm",arabicName:"النَّجْمِ",ayahs:62,type:"Mekki"},
  {number:54,name:"Kamer",arabicName:"الْقَمَرِ",ayahs:55,type:"Mekki"},
  {number:55,name:"Rahmân",arabicName:"الرَّحْمٰنِ",ayahs:78,type:"Medeni"},
  {number:56,name:"Vâkıa",arabicName:"الْوَاقِعَةِ",ayahs:96,type:"Mekki"},
  {number:57,name:"Hadîd",arabicName:"الْحَدِيدِ",ayahs:29,type:"Medeni"},
  {number:58,name:"Mücâdele",arabicName:"الْمُجَادَلَةِ",ayahs:22,type:"Medeni"},
  {number:59,name:"Haşr",arabicName:"الْحَشْرِ",ayahs:24,type:"Medeni"},
  {number:60,name:"Mümtehine",arabicName:"الْمُمْتَحَنَةِ",ayahs:13,type:"Medeni"},
  {number:61,name:"Saff",arabicName:"الصَّفِّ",ayahs:14,type:"Medeni"},
  {number:62,name:"Cuma",arabicName:"الْجُمُعَةِ",ayahs:11,type:"Medeni"},
  {number:63,name:"Münâfikûn",arabicName:"الْمُنَافِقُونَ",ayahs:11,type:"Medeni"},
  {number:64,name:"Teğâbün",arabicName:"التَّغَابُنِ",ayahs:18,type:"Medeni"},
  {number:65,name:"Talâk",arabicName:"الطَّلَاقِ",ayahs:12,type:"Medeni"},
  {number:66,name:"Tahrîm",arabicName:"التَّحْرِيمِ",ayahs:12,type:"Medeni"},
  {number:67,name:"Mülk",arabicName:"الْمُلْكِ",ayahs:30,type:"Mekki"},
  {number:68,name:"Kalem",arabicName:"الْقَلَمِ",ayahs:52,type:"Mekki"},
  {number:69,name:"Hâkka",arabicName:"الْحَاقَّةِ",ayahs:52,type:"Mekki"},
  {number:70,name:"Meâric",arabicName:"الْمَعَارِجِ",ayahs:44,type:"Mekki"},
  {number:71,name:"Nûh",arabicName:"نُوحٍ",ayahs:28,type:"Mekki"},
  {number:72,name:"Cin",arabicName:"الْجِنِّ",ayahs:28,type:"Mekki"},
  {number:73,name:"Müzzemmil",arabicName:"الْمُزَّمِّلِ",ayahs:20,type:"Mekki"},
  {number:74,name:"Müddessir",arabicName:"الْمُدَّثِّرِ",ayahs:56,type:"Mekki"},
  {number:75,name:"Kıyâme",arabicName:"الْقِيَامَةِ",ayahs:40,type:"Mekki"},
  {number:76,name:"İnsan",arabicName:"الْإِنْسَانِ",ayahs:31,type:"Medeni"},
  {number:77,name:"Mürselât",arabicName:"الْمُرْسَلَاتِ",ayahs:50,type:"Mekki"},
  {number:78,name:"Nebe'",arabicName:"النَّبَإِ",ayahs:40,type:"Mekki"},
  {number:79,name:"Nâziât",arabicName:"النَّازِعَاتِ",ayahs:46,type:"Mekki"},
  {number:80,name:"Abese",arabicName:"عَبَسَ",ayahs:42,type:"Mekki"},
  {number:81,name:"Tekvîr",arabicName:"التَّكْوِيرِ",ayahs:29,type:"Mekki"},
  {number:82,name:"İnfitâr",arabicName:"الْإِنْفِطَارِ",ayahs:19,type:"Mekki"},
  {number:83,name:"Mutaffifîn",arabicName:"الْمُطَفِّفِينَ",ayahs:36,type:"Mekki"},
  {number:84,name:"İnşikâk",arabicName:"الْإِنْشِقَاقِ",ayahs:25,type:"Mekki"},
  {number:85,name:"Burûc",arabicName:"الْبُرُوجِ",ayahs:22,type:"Mekki"},
  {number:86,name:"Târık",arabicName:"الطَّارِقِ",ayahs:17,type:"Mekki"},
  {number:87,name:"A'lâ",arabicName:"الْأَعْلَىٰ",ayahs:19,type:"Mekki"},
  {number:88,name:"Gâşiye",arabicName:"الْغَاشِيَةِ",ayahs:26,type:"Mekki"},
  {number:89,name:"Fecr",arabicName:"الْفَجْرِ",ayahs:30,type:"Mekki"},
  {number:90,name:"Beled",arabicName:"الْبَلَدِ",ayahs:20,type:"Mekki"},
  {number:91,name:"Şems",arabicName:"الشَّمْسِ",ayahs:15,type:"Mekki"},
  {number:92,name:"Leyl",arabicName:"اللَّيْلِ",ayahs:21,type:"Mekki"},
  {number:93,name:"Duhâ",arabicName:"الضُّحَىٰ",ayahs:11,type:"Mekki"},
  {number:94,name:"İnşirâh",arabicName:"الشَّرْحِ",ayahs:8,type:"Mekki"},
  {number:95,name:"Tîn",arabicName:"التِّينِ",ayahs:8,type:"Mekki"},
  {number:96,name:"Alak",arabicName:"الْعَلَقِ",ayahs:19,type:"Mekki"},
  {number:97,name:"Kadir",arabicName:"الْقَدْرِ",ayahs:5,type:"Mekki"},
  {number:98,name:"Beyyine",arabicName:"الْبَيِّنَةِ",ayahs:8,type:"Medeni"},
  {number:99,name:"Zilzâl",arabicName:"الزَّلْزَلَةِ",ayahs:8,type:"Medeni"},
  {number:100,name:"Âdiyât",arabicName:"الْعَادِيَاتِ",ayahs:11,type:"Mekki"},
  {number:101,name:"Kâria",arabicName:"الْقَارِعَةِ",ayahs:11,type:"Mekki"},
  {number:102,name:"Tekâsür",arabicName:"التَّكَاثُرِ",ayahs:8,type:"Mekki"},
  {number:103,name:"Asr",arabicName:"الْعَصْرِ",ayahs:3,type:"Mekki"},
  {number:104,name:"Hümeze",arabicName:"الْهُمَزَةِ",ayahs:9,type:"Mekki"},
  {number:105,name:"Fîl",arabicName:"الْفِيلِ",ayahs:5,type:"Mekki"},
  {number:106,name:"Kureyş",arabicName:"قُرَيْشٍ",ayahs:4,type:"Mekki"},
  {number:107,name:"Mâûn",arabicName:"الْمَاعُونِ",ayahs:7,type:"Mekki"},
  {number:108,name:"Kevser",arabicName:"الْكَوْثَرِ",ayahs:3,type:"Mekki"},
  {number:109,name:"Kâfirûn",arabicName:"الْكَافِرُونَ",ayahs:6,type:"Mekki"},
  {number:110,name:"Nasr",arabicName:"النَّصْرِ",ayahs:3,type:"Medeni"},
  {number:111,name:"Tebbet",arabicName:"الْمَسَدِ",ayahs:5,type:"Mekki"},
  {number:112,name:"İhlâs",arabicName:"الْإِخْلَاصِ",ayahs:4,type:"Mekki"},
  {number:113,name:"Felak",arabicName:"الْفَلَقِ",ayahs:5,type:"Mekki"},
  {number:114,name:"Nâs",arabicName:"النَّاسِ",ayahs:6,type:"Mekki"},
];

const RECITERS = [
  {id:"ar.alafasy",name:"Mishary Râşid el-Afâsi"},
  {id:"ar.abdurrahmaansudais",name:"Abdurrahman es-Südeys"},
  {id:"ar.abdulbasitmurattal",name:"Abdülbâsıt Abdüssamed (Murattal)"},
  {id:"ar.abdullahbasfar",name:"Abdullah Basfer"},
  {id:"ar.abdulsamad",name:"Abdülbâsıt Abdüssamed"},
  {id:"ar.ahmedajamy",name:"Ahmed el-Aclemî"},
  {id:"ar.aymanswoaid",name:"Eymen Süveyd"},
  {id:"ar.hanirifai",name:"Hâni er-Rifâî"},
  {id:"ar.husary",name:"Mahmud Halil el-Husarî"},
  {id:"ar.hudhaify",name:"Ali el-Huzeyfî"},
  {id:"ar.ibrahimakhbar",name:"İbrahim el-Ahbâr"},
  {id:"ar.mahermuaiqly",name:"Mâhir el-Mu'aykılî"},
  {id:"ar.minshawi",name:"Muhammed Sıddîk el-Minşâvî"},
  {id:"ar.muhammadayyoub",name:"Muhammed Eyyûb"},
  {id:"ar.muhammadjibreel",name:"Muhammed Cibrîl"},
  {id:"ar.saoodshuraym",name:"Suûd eş-Şureym"},
  {id:"ar.shaatree",name:"Ebû Bekr eş-Şâtırî"},
];

const TRANSLATIONS = [
  {id:"tr.diyanet",name:"Diyanet İşleri"},
  {id:"tr.yazir",name:"Elmalılı Hamdi Yazır"},
  {id:"tr.golpinarli",name:"Abdülbaki Gölpınarlı"},
  {id:"tr.ates",name:"Süleyman Ateş"},
  {id:"tr.ozturk",name:"Yaşar Nuri Öztürk"},
  {id:"tr.vakfi",name:"Diyanet Vakfı"},
];

const FONTS = [
  {id:"ShaikhHamdullah",name:"Shaikh Hamdullah"},
  {id:"ShaikhHamdullahBasic",name:"Shaikh Hamdullah Basic"},
  {id:"ShaikhHamdullahBook",name:"Shaikh Hamdullah Book"},
  {id:"Elfmshf",name:"Elfmshf"},
  {id:"KKAbay",name:"KK Abay"},
  {id:"AbdoLine",name:"Abdo Line"},
  {id:"AdwaAssalaf",name:"Adwa Assalaf"},
  {id:"Arakom",name:"Arakom"},
  {id:"Hsnt",name:"Hüsn-i Hat"},
  {id:"Slymnfyzlgl",name:"Süleyman Fevzioğlu"},
  {id:"WinSoftPro",name:"WinSoft Pro"},
];

const TOTAL_PAGES = 604;
const TOTAL_JUZ = 30;

// ===== STATE =====
const DEFAULT_STATE = {
  mode: 'surah', surah: 1, juz: 1, page: 1,
  reciter: 'ar.shaatree', theme: 'light',
  fontSize: 34, fontFamily: 'ShaikhHamdullah',
  speed: 1, repeat: 1,
  showBookmarks: false, showTranslation: true,
  translations: ['tr.diyanet'],
};

let appState = {...DEFAULT_STATE};
let currentTab = 'surah';
let ayahs = [];
let activeIdx = -1;
let playing = false;
let muted = false;
let continuous = true;
let repeatCount = 0;
let openMenuNum = null;
let bookmarks = [];
let favorites = [];

// Load persisted state
try {
  const raw = localStorage.getItem('mushaf-state');
  if (raw) {
    const p = JSON.parse(raw);
    if (p.translation && !p.translations) { p.translations = [p.translation]; delete p.translation; }
    appState = {...DEFAULT_STATE, ...p};
  }
} catch(e) {}
try { bookmarks = JSON.parse(localStorage.getItem('mushaf-bookmarks') || '[]'); } catch(e) {}
try { favorites = JSON.parse(localStorage.getItem('mushaf-favs') || '[]'); } catch(e) {}

function saveState() {
  localStorage.setItem('mushaf-state', JSON.stringify(appState));
}

function setState(key, value) {
  appState[key] = value;
  saveState();
  applyState();
}

function applyState() {
  // Theme
  document.body.classList.remove('theme-sepia','theme-dark');
  if (appState.theme === 'sepia') document.body.classList.add('theme-sepia');
  if (appState.theme === 'dark') document.body.classList.add('theme-dark');

  // Header title
  let title = '';
  if (appState.mode === 'surah') {
    const s = SURAHS[appState.surah - 1];
    title = s.number + '. ' + s.name + ' Sûresi';
  } else if (appState.mode === 'juz') {
    title = appState.juz + '. Cüz';
  } else {
    title = appState.page + '. Sayfa';
  }
  document.getElementById('page-title').textContent = title;

  // Meal label
  document.getElementById('meal-btn-label').textContent = appState.showTranslation ? 'Meal Açık' : 'Meal Kapalı';
  const mealToggle = document.getElementById('meal-toggle-btn');
  if (mealToggle) {
    mealToggle.textContent = appState.showTranslation ? 'Meal Açık' : 'Meal Kapalı';
    mealToggle.classList.toggle('active', appState.showTranslation);
  }

  // Theme icons
  const isDark = appState.theme === 'dark';
  document.getElementById('theme-icon-moon').style.display = isDark ? 'none' : 'inline-block';
  document.getElementById('theme-icon-sun').style.display = isDark ? 'inline-block' : 'none';

  // Theme buttons
  ['light','sepia','dark'].forEach(t => {
    const btn = document.getElementById('theme-btn-' + t);
    if (btn) btn.classList.toggle('active', appState.theme === t);
  });

  // Font size
  const fsr = document.getElementById('font-size-range');
  if (fsr) fsr.value = appState.fontSize;
  const fsl = document.getElementById('font-size-label');
  if (fsl) fsl.textContent = appState.fontSize;

  // Repeat
  const rr = document.getElementById('repeat-range');
  if (rr) rr.value = appState.repeat;
  const rl = document.getElementById('repeat-label');
  if (rl) rl.textContent = appState.repeat;

  // Speed
  const ss = document.getElementById('speed-select');
  if (ss) ss.value = appState.speed;

  // Reciters
  ['sidebar-reciter', 'settings-reciter'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = appState.reciter;
  });

  // Font buttons
  document.querySelectorAll('.font-btn').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.font === appState.fontFamily);
  });

  // Translations
  document.querySelectorAll('.translation-checkbox-btn').forEach(btn => {
    const checked = appState.translations.includes(btn.dataset.trans);
    btn.classList.toggle('active', checked);
    const cb = btn.querySelector('input[type=checkbox]');
    if (cb) cb.checked = checked;
  });
  const tgrid = document.getElementById('translations-grid');
  if (tgrid) tgrid.classList.toggle('translations-disabled', !appState.showTranslation);

  // Bookmarks panel
  const bp = document.getElementById('bookmarks-panel');
  bp.classList.toggle('show', appState.showBookmarks);
  if (appState.showBookmarks) renderBookmarksPanel();

  // Active list item
  document.querySelectorAll('.list-item').forEach(el => {
    const mode = el.dataset.mode;
    const num = parseInt(el.dataset.num);
    const isActive = mode === appState.mode &&
      ((mode === 'surah' && num === appState.surah) ||
       (mode === 'juz' && num === appState.juz) ||
       (mode === 'page' && num === appState.page));
    el.classList.toggle('active', isActive);
  });
}

// ===== ARABIC NUMERALS =====
function toArabicNumerals(n) {
  const map = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
  return String(n).replace(/\d/g, d => map[Number(d)]);
}

// ===== STRIP BASMALA =====
function stripBasmala(text) {
  const target = 'بسماللهالرحمنالرحيم';
  const isSkip = c => /[\u064B-\u065F\u0670\u06D6-\u06ED\u0640\s\u200F\u200E]/.test(c);
  const norm = c => (c === 'ٱ' || c === 'أ' || c === 'إ' || c === 'آ') ? 'ا' : c;
  let i = 0, j = 0;
  while (i < text.length && j < target.length) {
    const c = text[i];
    if (isSkip(c)) { i++; continue; }
    if (norm(c) === target[j]) { i++; j++; } else return text;
  }
  if (j < target.length) return text;
  while (i < text.length && isSkip(text[i])) i++;
  return text.slice(i);
}

// ===== API =====
// Use PHP proxy if available, otherwise direct CORS
async function fetchEditions(path, translationEditions = [], surahNum) {
  const editionsArr = ['quran-uthmani', ...translationEditions];
  const isSurah = path.startsWith('surah');
  let fetchedData = [];

  try {
    if (isSurah) {
      // SURE İÇİN (Hızlı Yöntem)
      const editions = editionsArr.join(',');
      let url = `?api=${encodeURIComponent(path + '/editions/' + editions)}`;
      let r = await fetch(url);
      if (!r.ok) {
        r = await fetch(`https://api.alquran.cloud/v1/${path}/editions/${editions}`);
      }
      const j = await r.json();
      if (!j.data) throw new Error("API Sure verisini döndürmedi.");
      fetchedData = j.data.map(editionData => editionData.ayahs);
      
    } else {
      // CÜZ VEYA SAYFA İÇİN (Çökme Korumalı Paralel İstekler)
      const fetchPromises = editionsArr.map(async (edition) => {
        let url = `?api=${encodeURIComponent(path + '/' + edition)}`;
        let r = await fetch(url);
        if (!r.ok) {
          // PHP Proxy hata verirse doğrudan tarayıcı üzerinden çek
          r = await fetch(`https://api.alquran.cloud/v1/${path}/${edition}`);
        }
        const j = await r.json();
        
        // Eğer API'den o meal için veri gelmezse hata verdirtme, boş liste dön
        if (!j.data || !j.data.ayahs) {
          console.warn(`Veri çekilemedi (Edition: ${edition}).`);
          return []; 
        }
        return j.data.ayahs; 
      });
      
      fetchedData = await Promise.all(fetchPromises);
    }

    const arabic = fetchedData[0];
    if (!arabic || arabic.length === 0) {
      throw new Error("Arapça ayet verisi alınamadı. API sunucusu yanıt vermiyor olabilir.");
    }

    const transData = translationEditions.map((id, i) => ({
      id, 
      name: TRANSLATIONS.find(t => t.id === id)?.name ?? id,
      ayahs: fetchedData[i + 1] || [],
    }));

    return arabic.map((a, i) => {
      const translations = transData.map(t => ({
        id: t.id, 
        name: t.name, 
        text: t.ayahs[i]?.text ?? '', // Çeviri bulunamazsa boş bırak, sayfayı çökertme
      }));
      
      return {
        number: a.number, 
        text: a.text,
        numberInSurah: a.numberInSurah,
        surah: a.surah?.number ?? surahNum ?? 0,
        page: a.page, 
        juz: a.juz,
        translations: translations.length ? translations : undefined,
      };
    });
    
  } catch (error) {
    console.error("fetchEditions Kritik Hata:", error);
    throw error; // Hata arayüzüne (UI) yansıması için hatayı fırlat
  }
}

function audioUrl(globalAyahNumber, reciterEdition) {
  return `https://cdn.islamic.network/quran/audio/128/${reciterEdition}/${globalAyahNumber}.mp3`;
}
// ===== LOAD CONTENT =====
let loadToken = 0;
async function loadContent() {
  const token = ++loadToken;
  setLoading(true);
  ayahs = [];
  activeIdx = -1;
  stopAudio();
  try {
    const trans = appState.showTranslation ? appState.translations : [];
    let data;
    if (appState.mode === 'surah') data = await fetchEditions(`surah/${appState.surah}`, trans, appState.surah);
    else if (appState.mode === 'juz') data = await fetchEditions(`juz/${appState.juz}`, trans);
    else data = await fetchEditions(`page/${appState.page}`, trans);
    if (token !== loadToken) return;
    ayahs = data;
    renderContent();
  } catch(e) {
    if (token !== loadToken) return;
    document.getElementById('content').innerHTML = '<p style="color:var(--destructive);text-align:center;padding:40px">Yüklenirken bir hata oluştu. Lütfen tekrar deneyin.</p>';
  } finally {
    if (token === loadToken) setLoading(false);
  }
}

function setLoading(show) {
  document.getElementById('loading').classList.toggle('show', show);
  document.getElementById('content').style.display = show ? 'none' : '';
}

// ===== RENDER =====
function renderContent() {
  const content = document.getElementById('content');
  if (!ayahs.length) { content.innerHTML = ''; return; }

  const fs = appState.fontSize;
  const ff = appState.fontFamily;

  let html = '<div class="mushaf-frame">';

  // Surah header
  if (appState.mode === 'surah') {
    const arabicName = SURAHS[appState.surah - 1]?.arabicName ?? '';
    const fullText = 'سُورَةُ ' + arabicName;
    const baseMax = fs + 18;
    const scale = Math.min(1, 14 / Math.max(fullText.length, 8));
    const autoSize = Math.max(24, Math.round(baseMax * scale));
    html += `<div class="sure-baslik"><div class="arabic" style="font-size:${autoSize}px;font-family:${ff};white-space:nowrap;overflow:hidden" dir="rtl">${fullText}</div></div>`;

    if (appState.surah !== 1 && appState.surah !== 9) {
      html += `<div class="basmala" style="font-size:${fs+2}px;font-family:${ff}" dir="rtl">بِسْمِ ٱللَّهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ</div><div class="mushaf-divider"></div>`;
    }
  }

  if (!appState.showTranslation) {
    // Flow mode
    html += `<div class="mushaf-flow" style="font-size:${fs}px;font-family:${ff}" dir="rtl">`;
    ayahs.forEach((a, idx) => {
      let text = a.text;
      if (idx === 0 && appState.mode === 'surah' && appState.surah !== 1 && appState.surah !== 9) text = stripBasmala(text);
      const isActive = activeIdx === idx;
      html += `<span id="ayah-wrap-${idx}">`;
      html += `<span class="ayah${isActive ? ' active' : ''}" onclick="playIndex(${idx})" title="Âyet ${a.numberInSurah}" data-idx="${idx}">${text}</span>`;
      html += `<span class="ayah-inline-controls">`;
      html += `<span class="ayah-num">﴿${toArabicNumerals(a.numberInSurah)}﴾</span>`;
      html += `<button class="ayah-dots" onclick="toggleMenu(event,${a.number})" aria-label="Âyet menüsü">${dotsSVG()}</button>`;
      html += `<span class="ayah-menu${openMenuNum === a.number ? ' open' : ''}" id="menu-${a.number}" dir="ltr">`;
      html += menuButtons(a, idx);
      html += `</span></span> </span>`;
    });
    html += '</div>';
  } else {
    // With-translation mode
    ayahs.forEach((a, idx) => {
      let text = a.text;
      if (idx === 0 && appState.mode === 'surah' && appState.surah !== 1 && appState.surah !== 9) text = stripBasmala(text);
      const isActive = activeIdx === idx;
      html += `<div class="ayah-block" id="ayah-wrap-${idx}">`;
      html += `<div class="ayah-row" style="font-size:${fs}px;font-family:${ff}" dir="rtl">`;
      html += `<span class="ayah${isActive ? ' active' : ''}" onclick="playIndex(${idx})" title="Âyet ${a.numberInSurah}" data-idx="${idx}">${text}</span>`;
      html += `<span class="ayah-inline-controls">`;
      html += `<span class="ayah-num">﴿${toArabicNumerals(a.numberInSurah)}﴾</span>`;
      html += `<button class="ayah-dots" onclick="toggleMenu(event,${a.number})" aria-label="Âyet menüsü">${dotsSVG()}</button>`;
      html += `<span class="ayah-menu${openMenuNum === a.number ? ' open' : ''}" id="menu-${a.number}" dir="ltr">`;
      html += menuButtons(a, idx);
      html += `</span></span></div>`;
      if (a.translations && a.translations.length) {
        a.translations.forEach(t => {
          html += `<div class="ayah-translation"><div class="translation-label">${escHtml(t.name)}</div><span class="ayah-num-label">${a.numberInSurah}.</span>${escHtml(t.text)}</div>`;
        });
      }
      html += '</div>';
    });
  }

  html += '</div>';
  content.innerHTML = html;
}

function dotsSVG() {
  return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>`;
}

function menuButtons(a, idx) {
  const isBook = bookmarks.includes(a.number);
  const isFav = favorites.includes(a.number);
  let html = '';
  html += `<button onclick="toggleBookmark(${a.number})" title="Ayraç" class="${isBook ? 'active' : ''}">${bookmarkSVG()}</button>`;
  html += `<button onclick="toggleFavorite(${a.number})" title="Favori" class="${isFav ? 'active' : ''}">${heartSVG(isFav)}</button>`;
  html += `<button onclick="shareAyah(${idx})" title="Paylaş">${shareSVG()}</button>`;
  html += `<button onclick="copyAyah(${idx})" title="Kopyala">${copySVG()}</button>`;
  return html;
}

function bookmarkSVG() { return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/></svg>`; }
function heartSVG(filled) { return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="${filled ? 'currentColor' : 'none'}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>`; }
function shareSVG() { return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>`; }
function copySVG() { return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>`; }

function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ===== MENU =====
function toggleMenu(e, num) {
  e.stopPropagation();
  openMenuNum = openMenuNum === num ? null : num;
  renderContent();
}
document.addEventListener('mousedown', e => {
  if (openMenuNum !== null && !e.target.closest('[id^="menu-"]') && !e.target.closest('.ayah-dots')) {
    openMenuNum = null;
    renderContent();
  }
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape' && openMenuNum !== null) { openMenuNum = null; renderContent(); }
});

// ===== BOOKMARKS & FAVORITES =====
function toggleBookmark(num) {
  if (bookmarks.includes(num)) bookmarks = bookmarks.filter(n => n !== num);
  else bookmarks = [...bookmarks, num];
  localStorage.setItem('mushaf-bookmarks', JSON.stringify(bookmarks));
  openMenuNum = null;
  renderContent();
  if (appState.showBookmarks) renderBookmarksPanel();
}
function toggleFavorite(num) {
  if (favorites.includes(num)) favorites = favorites.filter(n => n !== num);
  else favorites = [...favorites, num];
  localStorage.setItem('mushaf-favs', JSON.stringify(favorites));
  openMenuNum = null;
  renderContent();
}
function renderBookmarksPanel() {
  const el = document.getElementById('bookmarks-list');
  if (!bookmarks.length) { el.innerHTML = '<p style="color:var(--muted-foreground);font-size:14px">Henüz yer imi yok.</p>'; return; }
  el.innerHTML = '<ul style="font-size:14px;list-style:none">' + bookmarks.map(b => `<li style="padding:4px 0">Âyet #${b}</li>`).join('') + '</ul>';
}

// ===== SHARE & COPY =====
function shareAyah(idx) {
  const a = ayahs[idx]; if (!a) return;
  const surahName = SURAHS[a.surah - 1]?.name ?? '';
  const trans = a.translations ? a.translations.map(t => t.text).join('\n\n') : '';
  const txt = `${a.text}\n\n${trans}\n\n— ${surahName} ${a.numberInSurah}`;
  if (navigator.share) { navigator.share({title:`${surahName} ${a.numberInSurah}`, text: txt}).catch(()=>{}); }
  else { navigator.clipboard.writeText(txt).catch(()=>{}); showToast(); }
}
function copyAyah(idx) {
  const a = ayahs[idx]; if (!a) return;
  const surahName = SURAHS[a.surah - 1]?.name ?? '';
  const trans = a.translations ? a.translations.map(t => t.text).join('\n\n') : '';
  const txt = `${a.text}\n\n${trans}\n\n— ${surahName} ${a.numberInSurah}`;
  navigator.clipboard.writeText(txt).catch(()=>{});
  showToast();
}
function showToast() {
  const t = document.getElementById('copied-toast');
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 1500);
}

// ===== AUDIO =====
const audio = document.getElementById('audio');
audio.addEventListener('ended', () => {
  if (repeatCount < appState.repeat - 1) {
    repeatCount++;
    audio.currentTime = 0;
    audio.play().catch(()=>{});
    return;
  }
  repeatCount = 0;
  if (continuous && activeIdx >= 0 && activeIdx < ayahs.length - 1) {
    playIndex(activeIdx + 1);
  } else {
    playing = false;
    updatePlayBtn();
  }
});

function playIndex(idx) {
  if (!ayahs[idx]) return;
  const a = ayahs[idx];
  audio.src = audioUrl(a.number, appState.reciter);
  audio.playbackRate = appState.speed;
  audio.play().then(() => { playing = true; updatePlayBtn(); }).catch(()=>{});
  activeIdx = idx;
  renderContent();
  const el = document.getElementById('ayah-wrap-' + idx);
  if (el) el.scrollIntoView({behavior:'smooth', block:'center'});
}

function togglePlay() {
  if (!audio.src || audio.ended) { playIndex(activeIdx >= 0 ? activeIdx : 0); return; }
  if (audio.paused) { audio.play().then(() => { playing = true; updatePlayBtn(); }).catch(()=>{}); }
  else { audio.pause(); playing = false; updatePlayBtn(); }
}
function stopAudio() { audio.pause(); audio.src = ''; playing = false; updatePlayBtn(); }
function audioNext() { if (activeIdx < ayahs.length - 1) playIndex(activeIdx + 1); }
function audioPrev() { if (activeIdx > 0) playIndex(activeIdx - 1); }
function toggleMute() {
  muted = !muted; audio.muted = muted;
  document.getElementById('icon-vol').style.display = muted ? 'none' : 'inline-block';
  document.getElementById('icon-mute').style.display = muted ? 'inline-block' : 'none';
}
function toggleRepeat() {
  continuous = !continuous;
  document.getElementById('repeat-btn').classList.toggle('on', continuous);
}
function setSpeed(s) {
  appState.speed = s; audio.playbackRate = s; saveState();
}
function updatePlayBtn() {
  document.getElementById('icon-play').style.display = playing ? 'none' : 'inline-block';
  document.getElementById('icon-pause').style.display = playing ? 'inline-block' : 'none';
}

// ===== NAVIGATION =====
function navPrev() {
  if (appState.mode === 'surah' && appState.surah > 1) setState('surah', appState.surah - 1);
  else if (appState.mode === 'juz' && appState.juz > 1) setState('juz', appState.juz - 1);
  else if (appState.mode === 'page' && appState.page > 1) setState('page', appState.page - 1);
  loadContent();
}
function navNext() {
  if (appState.mode === 'surah' && appState.surah < 114) setState('surah', appState.surah + 1);
  else if (appState.mode === 'juz' && appState.juz < TOTAL_JUZ) setState('juz', appState.juz + 1);
  else if (appState.mode === 'page' && appState.page < TOTAL_PAGES) setState('page', appState.page + 1);
  loadContent();
}

// ===== SIDEBAR =====
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('backdrop').classList.toggle('open');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('backdrop').classList.remove('open');
}

// ===== TABS =====
function setTab(tab) {
  currentTab = tab;
  ['surah','juz','page'].forEach(t => {
    document.getElementById('tab-' + t).classList.toggle('active', t === tab);
  });
  renderList('');
}

// ===== LIST =====
function renderList(query) {
  const container = document.getElementById('list-container');
  const q = query.trim().toLocaleLowerCase('tr');
  let html = '';

  if (currentTab === 'surah') {
    const filtered = q
      ? SURAHS.filter(s => s.name.toLocaleLowerCase('tr').includes(q) || String(s.number) === q || s.arabicName.includes(q))
      : SURAHS;
    filtered.forEach(s => {
      const isActive = appState.mode === 'surah' && appState.surah === s.number;
      html += `<button class="list-item${isActive ? ' active' : ''}" data-mode="surah" data-num="${s.number}" onclick="selectSurah(${s.number})">
        <span class="item-info">
          <span class="item-num">${s.number}</span>
          <span><span class="item-name">${escHtml(s.name)}</span><span class="item-sub">${escHtml(s.type)} · ${s.ayahs} âyet</span></span>
        </span>
        <span class="item-arabic">${escHtml(s.arabicName)}</span>
      </button>`;
    });
  } else if (currentTab === 'juz') {
    for (let j = 1; j <= TOTAL_JUZ; j++) {
      const isActive = appState.mode === 'juz' && appState.juz === j;
      html += `<button class="list-item${isActive ? ' active' : ''}" data-mode="juz" data-num="${j}" onclick="selectJuz(${j})">${j}. Cüz</button>`;
    }
  } else {
    for (let p = 1; p <= TOTAL_PAGES; p++) {
      const isActive = appState.mode === 'page' && appState.page === p;
      html += `<button class="list-item${isActive ? ' active' : ''}" data-mode="page" data-num="${p}" onclick="selectPage(${p})">${p}. Sayfa</button>`;
    }
  }
  container.innerHTML = html;
}

function filterList(q) { renderList(q); }

function selectSurah(num) {
  appState.mode = 'surah'; appState.surah = num; saveState(); applyState();
  renderList(document.getElementById('search-input').value);
  if (window.innerWidth < 900) closeSidebar();
  loadContent();
}
function selectJuz(num) {
  appState.mode = 'juz'; appState.juz = num; saveState(); applyState();
  renderList('');
  if (window.innerWidth < 900) closeSidebar();
  loadContent();
}
function selectPage(num) {
  appState.mode = 'page'; appState.page = num; saveState(); applyState();
  renderList('');
  if (window.innerWidth < 900) closeSidebar();
  loadContent();
}

// ===== THEME CYCLE =====
function cycleTheme() {
  const themes = ['light','sepia','dark'];
  const idx = themes.indexOf(appState.theme);
  setState('theme', themes[(idx + 1) % 3]);
}

// ===== SETTINGS MODAL =====
function openSettings() { document.getElementById('settings-overlay').classList.add('open'); }
function closeSettings() { document.getElementById('settings-overlay').classList.remove('open'); }
function closeSettingsOutside(e) { if (e.target === document.getElementById('settings-overlay')) closeSettings(); }

// ===== INIT =====
function initSelects() {
  const opts = RECITERS.map(r => `<option value="${r.id}">${escHtml(r.name)}</option>`).join('');
  document.getElementById('sidebar-reciter').innerHTML = opts;
  document.getElementById('settings-reciter').innerHTML = opts;

 // Font dropdown (Yazı tipi açılır menüsü)
  const fontSelect = document.getElementById('font-select');
  if (fontSelect) {
    fontSelect.innerHTML = FONTS.map(f =>
      `<option value="${f.id}" ${appState.fontFamily === f.id ? 'selected' : ''}>
        ${escHtml(f.name)}
      </option>`
    ).join('');
  }

  // Translation checkboxes
  document.getElementById('translations-grid').innerHTML = TRANSLATIONS.map(t => {
    const checked = appState.translations.includes(t.id);
    return `<button class="translation-checkbox-btn${checked ? ' active' : ''}" data-trans="${t.id}" onclick="toggleTranslation('${t.id}')">
      <input type="checkbox" ${checked ? 'checked' : ''} onclick="event.stopPropagation()">
      <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escHtml(t.name)}</span>
    </button>`;
  }).join('');
}

function toggleTranslation(id) {
  let trans = [...appState.translations];
  if (trans.includes(id)) {
    trans = trans.filter(t => t !== id);
    if (!trans.length) trans = [id]; // min 1
  } else {
    trans = [...trans, id];
  }
  appState.translations = trans;
  saveState();
  applyState();
  loadContent();
}

// Boot
initSelects();
applyState();
renderList('');
loadContent();
</script>
</body>
</html>