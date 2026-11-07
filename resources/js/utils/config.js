/**
 * config.js - Dynamic URL & Asset Resolver
 * Otomatis mendeteksi subfolder (misal /Clone2/public) baik di localhost, IP LAN (172.x / 192.x), maupun domain produksi
 */

export function getBaseUrl() {
  if (window.__SAMSAT_CONFIG__?.baseUrl) {
    try {
      const parsed = new URL(window.__SAMSAT_CONFIG__.baseUrl);
      if (window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1' && (parsed.hostname === 'localhost' || parsed.hostname === '127.0.0.1')) {
        parsed.protocol = window.location.protocol;
        parsed.host = window.location.host;
        return parsed.toString().replace(/\/+$/, '');
      }
      return window.__SAMSAT_CONFIG__.baseUrl.replace(/\/+$/, '');
    } catch {
      return window.__SAMSAT_CONFIG__.baseUrl.replace(/\/+$/, '');
    }
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
    try {
      const parsedUrl = new URL(window.__SAMSAT_CONFIG__.apiUrl);
      if (window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1' && (parsedUrl.hostname === 'localhost' || parsedUrl.hostname === '127.0.0.1')) {
        parsedUrl.protocol = window.location.protocol;
        parsedUrl.host = window.location.host;
        return parsedUrl.toString().replace(/\/+$/, '');
      }
      return window.__SAMSAT_CONFIG__.apiUrl.replace(/\/+$/, '');
    } catch {
      return window.__SAMSAT_CONFIG__.apiUrl.replace(/\/+$/, '');
    }
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

