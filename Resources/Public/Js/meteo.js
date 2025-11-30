
    document.addEventListener('click', function(e) {
    if (e.target.classList.contains('mw-toggle-full')) {
    const btn = e.target;
    const target = document.getElementById(btn.dataset.target);
    const isHidden = target.classList.toggle('mw-full-hidden');
    btn.textContent = isHidden ? btn.dataset.open : btn.dataset.close;
}
});

    (function () {
    "use strict";

    document.addEventListener("DOMContentLoaded", function () {

    /* ================================
       HELPERS
    ================================= */

    const loadJSON = (url) =>
    fetch(url, { cache: "no-store" }).then(r => {
    if (!r.ok) throw new Error("HTTP " + r.status + " for " + url);
    return r.json();
});

    const parseDate = (d) => {
    if (d == null) return null;
    const s = d.toString();
    if (s.length !== 12) return null;
    const year = Number(s.substring(0, 4));
    const month = Number(s.substring(4, 6)) - 1;
    const day = Number(s.substring(6, 8));
    const hour = Number(s.substring(8, 10));
    const minute = Number(s.substring(10, 12));
    return new Date(year, month, day, hour, minute);
};

    const formatDate = (date) =>
    date.toLocaleDateString("de-CH", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric"
});

    const formatTime = (date) =>
    date.toLocaleTimeString("de-CH", {
    hour: "2-digit",
    minute: "2-digit",
    hour12: false
});

    const formatCoords = (lat, lon) =>
    (typeof lat === "number" && typeof lon === "number")
    ? lat.toFixed(4) + " / " + lon.toFixed(4)
    : "";

    const directionFromDeg = (deg) => {
    const dirs = ["N", "NE", "E", "SE", "S", "SW", "W", "NW"];
    return (typeof deg === "number")
    ? dirs[Math.round(deg / 45) % 8]
    : "";
};

    const sameYMD = (a, b) =>
    a.getFullYear() === b.getFullYear() &&
    a.getMonth() === b.getMonth() &&
    a.getDate() === b.getDate();

    const addDays = (date, days) => {
    const d = new Date(date.getTime());
    d.setDate(d.getDate() + days);
    return d;
};

    /**
     * Перетворюємо JSON одного параметра на відсортований масив:
     * [{ date: Date, rawDate: 202511270000, value: <number> }, ...]
     */
    const buildSeries = (json, param) => {
    if (!json || !Array.isArray(json.data)) return [];
    const series = [];

    json.data.forEach(row => {
    if (!row || row.Date == null) return;
    const v = row[param];
    if (v == null) return;

    const d = parseDate(row.Date);
    if (!d) return;

    series.push({
    date: d,
    rawDate: row.Date,
    value: v
});
});

    series.sort((a, b) => a.date - b.date);
    return series;
};

    /**
     * Повертає першу точку з date >= target,
     * або останню точку, якщо всі раніше.
     */
    const getPointOnOrAfter = (series, target) => {
    if (!series || !series.length) return null;
    for (let i = 0; i < series.length; i++) {
    if (series[i].date >= target) {
    return series[i];
}
}
    return series[series.length - 1];
};

    /**
     * Повертає першу точку в той самий календарний день, що й targetDay.
     */
    const getPointSameDay = (series, targetDay) => {
    if (!series || !series.length) return null;
    for (let i = 0; i < series.length; i++) {
    if (sameYMD(series[i].date, targetDay)) {
    return series[i];
}
}
    return null;
};

    const getSymbolDef = (symbolMap, code) => {
    if (code == null) return null;
    const key = String(code);
    return symbolMap && symbolMap[key] ? symbolMap[key] : null;
};

    const renderSymbolImg = (imgElement, symbolDef) => {
    if (!imgElement) return;
    if (!symbolDef) {
    imgElement.removeAttribute("src");
    imgElement.alt = "";
    return;
}
    imgElement.src = "/fileadmin/meteoswiss/weather_icons/" + symbolDef.file;
    imgElement.alt = "";
};

    const parameterMap = {
    tre200h0: { decimals: 1, unit: " °C" },
    fu3010h0: { decimals: 1, unit: " m/s" },
    fu3010h1: { decimals: 1, unit: " m/s" },
    dkl010h0: { decimals: 0, unit: "", formatter: directionFromDeg },
    rre150h0: { decimals: 1, unit: " mm" },
    nprolohs: { decimals: 0, unit: " %" },
    npromths: { decimals: 0, unit: " %" },
    nprohihs: { decimals: 0, unit: " %" },
    sre000h0: { decimals: 0, unit: " min" },
    gre000h0: { decimals: 0, unit: "" },
    zprfr0hs: { decimals: 0, unit: " m" }
};

    /* ================================
       MAIN WIDGET LOADING
    ================================= */

    const widgets = document.querySelectorAll('.bss-meteoswiss-widget[data-bss-meteo="widget"]');
    if (!widgets.length) return;

    let stationsPromise = null;
    let symbolMapPromise = null;

    widgets.forEach(widget => {

    const pointId = widget.getAttribute("data-point-id");

    const urls = {
    tre200h0: widget.getAttribute("data-url-tre200h0"),
    fu3010h0: widget.getAttribute("data-url-fu3010h0"),
    fu3010h1: widget.getAttribute("data-url-fu3010h1"),
    dkl010h0: widget.getAttribute("data-url-dkl010h0"),
    rre150h0: widget.getAttribute("data-url-rre150h0"),
    nprolohs: widget.getAttribute("data-url-nprolohs"),
    npromths: widget.getAttribute("data-url-npromths"),
    nprohihs: widget.getAttribute("data-url-nprohihs"),
    sre000h0: widget.getAttribute("data-url-sre000h0"),
    gre000h0: widget.getAttribute("data-url-gre000h0"),
    zprfr0hs: widget.getAttribute("data-url-zprfr0hs"),

    jww003i0: widget.getAttribute("data-url-jww003i0"),
    rp0003i0: widget.getAttribute("data-url-rp0003i0"),

    jp2000d0: widget.getAttribute("data-url-jp2000d0"),
    rka150p0: widget.getAttribute("data-url-rka150p0"),
    tre200dn: widget.getAttribute("data-url-tre200dn"),
    tre200dx: widget.getAttribute("data-url-tre200dx")
};

    const paramPromises = {};
    Object.keys(urls).forEach(param => {
    const url = urls[param];
    if (url) {
    paramPromises[param] = loadJSON(url).catch(() => null);
}
});

    const stationsUrl = widget.getAttribute("data-stations-url");
    const symbolMapUrl = widget.getAttribute("data-symbol-map-url");

    if (!stationsPromise && stationsUrl) {
    stationsPromise = loadJSON(stationsUrl).catch(() => null);
}
    if (!symbolMapPromise && symbolMapUrl) {
    symbolMapPromise = loadJSON(symbolMapUrl).catch(() => ({}));
}

    const stPromise = stationsPromise || Promise.resolve(null);
    const smPromise = symbolMapPromise || Promise.resolve({});

    Promise.all([
    Promise.all(
    Object.keys(paramPromises).map(param =>
    paramPromises[param].then(json => [param, json])
    )
    ),
    stPromise,
    smPromise
    ])
    .then(([paramResults, stations, symbolMap]) => {

    const seriesMap = {};
    paramResults.forEach(([param, json]) => {
    seriesMap[param] = buildSeries(json, param);
});

    const setRoleText = (role, text) => {
    const el = widget.querySelector('[data-bss-role="' + role + '"]');
    if (el) el.textContent = text;
};

    /* ────────────────────────────────
       STATION INFO (header)
    ──────────────────────────────── */
    if (stations && pointId && stations[pointId]) {
    const st = stations[pointId];

    setRoleText("station-name", st.station_name || "");
    setRoleText("station-canton", st.canton ? "(" + st.canton + ")" : "");

    setRoleText(
    "station-altitude",
    (st.altitude_masl != null ? st.altitude_masl + " m ü. M." : "")
    );

    setRoleText(
    "station-coords",
    formatCoords(st.lat, st.lon)
    );

    const timeSpan = widget.querySelector('[data-bss-role="station-time"]');
    if (timeSpan) {
    timeSpan.textContent = new Date().toLocaleString("de-CH");
}
}

    const now = new Date();

    /* ────────────────────────────────
       NOW ICON + DESCRIPTION
    ──────────────────────────────── */

    const symbolSeries = seriesMap["jww003i0"] || [];
    const symbolPointNow = getPointOnOrAfter(symbolSeries, now);

    if (symbolPointNow) {
    const symbolDef = getSymbolDef(symbolMap, symbolPointNow.value);
    const nowIconEl = widget.querySelector('[data-bss-role="now-icon"]');
    const nowDescEl = widget.querySelector('[data-bss-role="now-desc"]');

    renderSymbolImg(nowIconEl, symbolDef);

    if (nowDescEl) {
    nowDescEl.textContent = symbolDef ? (symbolDef.desc || "") : "";
}
}

    /* ────────────────────────────────
       NOW TEMPERATURE (big number)
    ──────────────────────────────── */

    const tempSeries = seriesMap["tre200h0"] || [];
    const tempPointNow = getPointOnOrAfter(tempSeries, now);
    const nowTempEl = widget.querySelector('[data-bss-role="now-temp"]');
    const nowTimeEl = widget.querySelector('[data-bss-role="now-time"]');

    if (tempPointNow) {
    if (nowTempEl) {
    nowTempEl.textContent = tempPointNow.value.toFixed(1) + " °C";
}
    if (nowTimeEl) {
    nowTimeEl.textContent = formatTime(tempPointNow.date);
}
}

    /* ────────────────────────────────
       LEFT NOW PARAMETERS (list)
    ──────────────────────────────── */

    Object.keys(parameterMap).forEach(param => {
    const el = widget.querySelector('[data-bss-role="' + param + '"]');
    if (!el) return;

    const series = seriesMap[param] || [];
    const point = getPointOnOrAfter(series, now);
    const cfg = parameterMap[param];

    if (!point) {
    el.textContent = "–";
    return;
}

    const val = point.value;

    if (cfg.formatter) {
    el.textContent = cfg.formatter(val) + (cfg.unit || "");
} else {
    el.textContent = val.toFixed(cfg.decimals) + cfg.unit;
}
});

    /* ============================================
CARD 1 — NEXT 3 HOURS (clean Variant 1)
=============================================== */

    const target3h = new Date(now.getTime() + 3 * 60 * 60 * 1000);

// Points
    const symbol3hPoint  = getPointOnOrAfter(seriesMap["jww003i0"] || [], target3h);
    const temp3hPoint    = getPointOnOrAfter(seriesMap["tre200h0"] || [], target3h);

    const rp3hPoint      = getPointOnOrAfter(seriesMap["rp0003i0"] || [], target3h);
    const fu3010h0_3h    = getPointOnOrAfter(seriesMap["fu3010h0"] || [], target3h);
    const fu3010h1_3h    = getPointOnOrAfter(seriesMap["fu3010h1"] || [], target3h);
    const dkl010h0_3h    = getPointOnOrAfter(seriesMap["dkl010h0"] || [], target3h);
    const nprolohs_3h    = getPointOnOrAfter(seriesMap["nprolohs"] || [], target3h);

// Time
    const time3hEl = widget.querySelector('[data-bss-role="card-3h-time"]');
    if (time3hEl && symbol3hPoint) {
    time3hEl.textContent = formatTime(symbol3hPoint.date);
}

// Symbol + desc + temp
    if (symbol3hPoint) {
    const def3h = getSymbolDef(symbolMap, symbol3hPoint.value);
    renderSymbolImg(widget.querySelector('[data-bss-role="card-3h-icon"]'), def3h);

    const descEl = widget.querySelector('[data-bss-role="card-3h-desc"]');
    if (descEl) descEl.textContent = def3h ? (def3h.desc || "") : "";
}

    if (temp3hPoint) {
    const temp3hEl = widget.querySelector('[data-bss-role="card-3h-temp"]');
    if (temp3hEl) temp3hEl.textContent = temp3hPoint.value.toFixed(1) + " °C";
}

// Helper for 3h card
    const setCard3h = (role, point, unit = "", formatter = null, decimals = 1) => {
    const el = widget.querySelector('[data-bss-role="card-3h-' + role + '"]');
    if (!el) return;

    if (!point) {
    el.textContent = "–";
    return;
}

    const v = point.value;

    if (formatter) el.textContent = formatter(v) + unit;
    else el.textContent = v.toFixed(decimals) + unit;
};

// Fill parameters (ONLY Variant 1)
    setCard3h("rp0003i0", rp3hPoint, " mm");
    setCard3h("fu3010h0", fu3010h0_3h, " m/s");
    setCard3h("fu3010h1", fu3010h1_3h, " m/s");
    setCard3h("dkl010h0", dkl010h0_3h, "", directionFromDeg, 0);
    setCard3h("nprolohs", nprolohs_3h, " %", null, 0);
    /* ============================================================
DAILY CARDS — TOMORROW & DAY AFTER TOMORROW
(Complete working block)
============================================================ */

// Функція для пошуку точки з таким самим Y-M-D
    const getPointSameDay = (series, targetDay) => {
    if (!series || !series.length) return null;
    for (let i = 0; i < series.length; i++) {
    if (sameYMD(series[i].date, targetDay)) {
    return series[i];
}
}
    return null;
};

// Додаємо сумування сонячної тривалості за день
    function sumDailySunshine(series, targetDay) {
    if (!series || !series.length) return null;

    let total = 0;

    series.forEach(p => {
    if (sameYMD(p.date, targetDay)) {
    if (typeof p.value === "number") {
    total += p.value;
}
}
});

    return total;
}

// Формуємо дати — завтра та післязавтра
    const today = new Date();
    const tomorrow = addDays(today, 1);
    const day2 = addDays(today, 2);


// Універсальний заповнювач daily-картки
    function fillDailyCard(dayDate, roles) {

    const symPt     = getPointSameDay(seriesMap["jp2000d0"], dayDate);
    const tminPt    = getPointSameDay(seriesMap["tre200dn"], dayDate);
    const tmaxPt    = getPointSameDay(seriesMap["tre200dx"], dayDate);
    const precipPt  = getPointSameDay(seriesMap["rka150p0"], dayDate);

    // =====================
    // DATE
    // =====================
    const dateEl = widget.querySelector(`[data-bss-role="${roles.date}"]`);
    if (dateEl && symPt) {
    dateEl.textContent = formatDate(symPt.date);
}

    // =====================
    // ICON + DESCRIPTION
    // =====================
    const iconEl = widget.querySelector(`[data-bss-role="${roles.icon}"]`);
    const descEl = widget.querySelector(`[data-bss-role="${roles.desc}"]`);

    if (iconEl) {
    if (symPt) {
    const def = getSymbolDef(symbolMap, symPt.value);
    renderSymbolImg(iconEl, def);

    if (descEl) {
    descEl.textContent = def ? (def.desc || "") : "";
}
} else {
    // немає даних
    iconEl.removeAttribute("src");
    iconEl.alt = "";
    if (descEl) descEl.textContent = "–";
}
}

    // =====================
    // TEMPERATURE: MIN/MAX
    // =====================
    const tempEl = widget.querySelector(`[data-bss-role="${roles.temp}"]`);
    if (tempEl) {
    if (tminPt && tmaxPt) {
    tempEl.textContent =
    tminPt.value.toFixed(1) + " … " +
    tmaxPt.value.toFixed(1) + " °C";
} else {
    tempEl.textContent = "–";
}
}

    // =====================
    // PRECIPITATION (Daily Sum)
    // =====================
    const rkaEl = widget.querySelector(`[data-bss-role="${roles.precip}"]`);
    if (rkaEl) {
    if (precipPt) {
    rkaEl.textContent = precipPt.value.toFixed(1) + " mm";
} else {
    rkaEl.textContent = "–";
}
}

    // =====================
    // SUNSHINE (Daily Sum)
    // =====================
    const sunshineTotal = sumDailySunshine(seriesMap["sre000h0"], dayDate);
    const sunEl = widget.querySelector(`[data-bss-role="${roles.sun}"]`);

    if (sunEl) {
    if (sunshineTotal != null) {
    sunEl.textContent = sunshineTotal.toFixed(0) + " min";
} else {
    sunEl.textContent = "–";
}
}
}


// ======================================================
// APPLY TO BOTH DAILY CARDS
// ======================================================

// CARD 2 — TOMORROW
    fillDailyCard(tomorrow, {
    date:   "card-tomorrow-date",
    icon:   "card-tomorrow-icon",
    desc:   "card-tomorrow-desc",
    temp:   "card-tomorrow-temp-range",
    precip: "card-tomorrow-rka150p0",
    sun:    "card-tomorrow-sre000h0"
});

// CARD 3 — DAY AFTER TOMORROW
    fillDailyCard(day2, {
    date:   "card-day2-date",
    icon:   "card-day2-icon",
    desc:   "card-day2-desc",
    temp:   "card-day2-temp-range",
    precip: "card-day2-rka150p0",
    sun:    "card-day2-sre000h0"
});

})
    .catch((error) => {
    console.error("Weather widget error:", error);
});

});

});
})();

