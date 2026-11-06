/**
 * config.js - Dynamic URL & Asset Resolver
 * Otomatis mendeteksi subfolder (misal /Clone2/public) baik di localhost, IP LAN (172.x / 192.x), maupun domain produksi
 */

export function getBaseUrl() {
  if (window.__SAMSAT_CONFIG__?.baseUrl) {
    return window.__SAMSAT_CONFIG__.baseUrl.replace(/\/+$/, '');
  }

  const { origin, pathname } = window.location;
  const match = pathname.match(/^(.*\/public)/i);
  if (match) {
    return `${origin}${match[1]}`.replace(/\/+$/, '');
  }

  return origin;
}

export function getApiBase() {
  if (window.__SAMSAT_CONFIG__?.apiUrl && !window.__SAMSAT_CONFIG__.apiUrl.startsWith('/')) {
    return window.__SAMSAT_CONFIG__.apiUrl.replace(/\/+$/, '');
  }

  // Jika apiUrl di-inject sebagai path relatif misal "/api/pwa", gabungkan dengan baseUrl
  const base = getBaseUrl();
  return `${base}/api/pwa`;
}

export function getAssetUrl(relativePath = '') {
  const clean = relativePath.startsWith('/') ? relativePath.slice(1) : relativePath;

  if (window.__SAMSAT_CONFIG__?.assetUrl) {
    const base = window.__SAMSAT_CONFIG__.assetUrl.endsWith('/')
      ? window.__SAMSAT_CONFIG__.assetUrl
      : `${window.__SAMSAT_CONFIG__.assetUrl}/`;
    return `${base}${clean}`;
  }

  return `${getBaseUrl()}/${clean}`;
}

