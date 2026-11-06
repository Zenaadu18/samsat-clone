/**
 * Utility untuk identifikasi unik perangkat (Device Fingerprint)
 * Digunakan untuk pembatasan 1 Akun = 1 Perangkat fisik.
 */

export function getDeviceId() {
  let deviceId = localStorage.getItem('pajakin_device_id');
  if (!deviceId) {
    if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
      deviceId = crypto.randomUUID();
    } else {
      deviceId = 'dev_' + Math.random().toString(36).substring(2, 12) + '_' + Date.now().toString(36);
    }
    localStorage.setItem('pajakin_device_id', deviceId);
  }
  return deviceId;
}

export function getDeviceName() {
  const ua = typeof navigator !== 'undefined' ? navigator.userAgent : '';
  let os = 'Perangkat Tidak Dikenal';

  if (/iPhone/i.test(ua)) os = 'iPhone';
  else if (/iPad/i.test(ua)) os = 'iPad';
  else if (/Android/i.test(ua)) os = 'Android';
  else if (/Windows NT/i.test(ua)) os = 'Windows PC';
  else if (/Macintosh|Mac OS/i.test(ua)) os = 'MacBook / Mac';
  else if (/Linux/i.test(ua)) os = 'Linux';

  let browser = 'Browser';
  if (/Edg/i.test(ua)) browser = 'Edge';
  else if (/Chrome|CriOS/i.test(ua)) browser = 'Chrome';
  else if (/Safari/i.test(ua) && !/Chrome|CriOS/i.test(ua)) browser = 'Safari';
  else if (/Firefox|FxiOS/i.test(ua)) browser = 'Firefox';

  return `${os} (${browser})`;
}

