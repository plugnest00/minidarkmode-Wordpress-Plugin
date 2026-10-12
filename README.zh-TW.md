# Mini Dark Mode

[English](README.md) | 繁體中文 | [简体中文](README.zh-CN.md)

**輕量到極致的深色模式切換：切換一個 class，加上一組 CSS 變數。**

Mini Dark Mode 為你的網站加上深色模式開關。它不對每張圖片套濾鏡、也不反轉整個頁面，而是在 `html` 元素上切換一個 class，再用一組小小的 CSS 變數重新上色文字、背景與介面元件。頁面保持銳利、圖片維持你原本設計的樣子，你佈景主題自己的樣式也照常運作。

📦 **從 WordPress.org 安裝**（上架後）· 👤 由 PlugNest 開發 · 🏠 [plugnest.dev](https://www.plugnest.dev/)

## 功能特色

- **不對圖片套濾鏡、不改 `src`**——相簿、地圖與照片完全不受影響。只有文字、背景與元件會重新上色。
- **不會閃白**——已儲存的模式在頁面 `<head>` 就套用，趕在第一次繪製之前。
- **六組配色預設**——玫瑰、海洋藍、森林綠、紫羅蘭、琥珀夕陽、石板灰，一鍵填滿所有顏色欄位，之後每個欄位仍可自由修改。
- **17 個顏色控制項**——強調色、連結、標題、內文、邊框、網站與內容背景，另附參考色盤。每個欄位都會說明它影響網站的哪個部分。
- **浮動按鈕或短代碼**——左下或右下角的圓形／膠囊按鈕；關掉浮動按鈕後，也可用 `[minidarkmode]` 放在任何位置。
- **記住訪客的選擇**——以瀏覽器為單位儲存。首次來訪者會套用作業系統偏好，且在訪客自行選擇前會跟著系統即時變動。
- **無障礙**——真正的 `button` 元素、帶 `aria-pressed` 狀態、清楚的鍵盤焦點外框、已翻譯的標籤，並尊重 `prefers-reduced-motion`。
- **只作用於前台**——登入頁與 `wp-admin` 完全不受影響。
- **不用寫 CSS 也能微調**——選目標、選屬性、選顏色。目標、屬性與顏色在儲存時各自比對固定清單驗證，輸出時再檢查一次。
- **乾淨解除安裝**——可依需求清除設定。

## 相容性

核心機制（一個 class 加 CSS 變數）適用於任何佈景主題。內建調校過的樣式支援 **Astra**、**Elementor**、**Spectra (UAGB)**、**Visual Portfolio**、**Modern Events Calendar** 與 **wpDiscuz**。其他主題可用顏色控制項與「進階微調」調整。

## 安裝

1. 在 WordPress 後台前往**外掛 → 安裝外掛**，搜尋「Mini Dark Mode」（或上傳 zip）。
2. 啟用。
3. 在**設定 → Mini Dark Mode** 調整顏色與按鈕。這樣就設定完成了。

## 常見問題

**它會讓圖片變暗或反轉嗎？** 不會——這正是重點。對圖片套濾鏡會讓照片變濁，還會弄壞相簿與地圖。Mini Dark Mode 只重新上色文字、背景與介面元件。

**深色模式套用前會先閃一下白畫面嗎？** 不會。頁面 `<head>` 裡的一小段 inline script 會在第一次繪製前就套用已儲存的模式。

**跟我的佈景主題相容嗎？** 核心機制適用於任何主題。上面列出的主題與外掛有內建調校過的樣式；其他主題可用顏色控制項調整。

**可以把切換鈕放在選單或頁面裡嗎？** 可以。關掉浮動按鈕後，把 `[minidarkmode]` 短代碼放在任何地方——選單、頁尾、Elementor 短代碼小工具等。

**我可以自己寫樣式嗎？** 可以。深色模式開啟時，`html` 元素會帶有 `mdm-dark-mode` class 與 `data-mdm-dark-mode="dark"` 屬性。每個顏色都以 CSS 自訂屬性公開：`--mdm-accent`、`--mdm-link`、`--mdm-link-hover`、`--mdm-heading`、`--mdm-text`、`--mdm-border`、`--mdm-bg`、`--mdm-surface`，以及 `--mdm-palette-*` 參考色值。

**會影響 wp-admin 嗎？** 不會。Mini Dark Mode 只作用於前台。

## 授權

GPL-2.0-or-later 或更新版本。詳見 [LICENSE](LICENSE)。

© PlugNest — [plugnest.dev](https://www.plugnest.dev/)
