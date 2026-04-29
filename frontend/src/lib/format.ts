export function formatRaceDate(iso: string | null): string {
  if (!iso) return '—';
  const d = new Date(iso);
  return d.toLocaleDateString(undefined, {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  });
}

export function formatRaceTime(iso: string | null): string {
  if (!iso) return '';
  const d = new Date(iso);
  return d.toLocaleTimeString(undefined, {
    hour: '2-digit',
    minute: '2-digit',
  });
}

export function pad(n: number, width = 2): string {
  return String(n).padStart(width, '0');
}

export function diffToCountdown(target: string | null): {
  done: boolean;
  days: number;
  hours: number;
  minutes: number;
  seconds: number;
} {
  if (!target) {
    return { done: true, days: 0, hours: 0, minutes: 0, seconds: 0 };
  }

  const ms = new Date(target).getTime() - Date.now();
  if (ms <= 0) {
    return { done: true, days: 0, hours: 0, minutes: 0, seconds: 0 };
  }

  const totalSeconds = Math.floor(ms / 1000);
  const days = Math.floor(totalSeconds / 86_400);
  const hours = Math.floor((totalSeconds % 86_400) / 3600);
  const minutes = Math.floor((totalSeconds % 3600) / 60);
  const seconds = totalSeconds % 60;
  return { done: false, days, hours, minutes, seconds };
}
