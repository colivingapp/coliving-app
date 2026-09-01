export const MATE_SOCIAL_NETWORKS = [
  { name: 'twitter', label: 'Twitter' },
  { name: 'facebook', label: 'Facebook' },
  { name: 'instagram', label: 'Instagram' },
  { name: 'youtube', label: 'YouTube' },
  { name: 'tiktok', label: 'TikTok' },
  { name: 'pinterest', label: 'Pinterest' },
  { name: 'linkedin', label: 'LinkedIn' },
  { name: 'github', label: 'GitHub' },
  { name: 'substack', label: 'Substack' },
  { name: 'medium', label: 'Medium' },
];

const BASES = {
  twitter: 'https://x.com/',
  facebook: 'https://www.facebook.com/',
  instagram: 'https://www.instagram.com/',
  youtube: 'https://www.youtube.com/@',
  tiktok: 'https://www.tiktok.com/@',
  pinterest: 'https://www.pinterest.com/',
  linkedin: 'https://www.linkedin.com/in/',
  github: 'https://github.com/',
  substack: 'https://substack.com/@',
  medium: 'https://medium.com/@',
  telegram: 'https://t.me/',
  linktree: 'https://linktr.ee/',
};

export function safeExternalUrl(url) {
  if (!url) {
    return '';
  }

  let value = String(url).trim();
  if (!value) {
    return '';
  }

  if (!/^https?:\/\//i.test(value)) {
    value = 'https://' + value;
  }

  try {
    const parsed = new URL(value);
    if (parsed.protocol !== 'http:' && parsed.protocol !== 'https:') {
      return '';
    }
    return parsed.href;
  } catch (error) {
    return '';
  }
}

export function normalizeSocialUrl(network, value) {
  let raw = String(value ?? '').trim();
  if (!raw) {
    return '';
  }

  if (/^https?:\/\//i.test(raw)) {
    try {
      const host = new URL(raw).hostname;
      if (!host || host.includes('.')) {
        return safeExternalUrl(raw);
      }
      raw = host;
    } catch (error) {
      return safeExternalUrl(raw);
    }
  } else if (raw.includes('/') || /^www\./i.test(raw)) {
    return safeExternalUrl(raw);
  }

  const handle = raw.replace(/^@+/, '');
  const base = BASES[String(network || '').toLowerCase()];
  if (!base || !handle) {
    return safeExternalUrl(raw);
  }

  return safeExternalUrl(base + handle);
}
