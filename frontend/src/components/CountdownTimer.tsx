import { useEffect, useState } from 'react';
import { diffToCountdown, pad } from '../lib/format';
import styles from './CountdownTimer.module.css';

interface CountdownTimerProps {
  target: string | null;
  /** When `compact`, omits days separator and shrinks layout. */
  compact?: boolean;
  doneLabel?: string;
}

export function CountdownTimer({
  target,
  compact = false,
  doneLabel = 'lights out',
}: CountdownTimerProps) {
  const [now, setNow] = useState(() => Date.now());

  useEffect(() => {
    const id = setInterval(() => setNow(Date.now()), 1000);
    return () => clearInterval(id);
  }, []);

  const c = diffToCountdown(target);
  // `now` referenced so the effect re-renders each second
  void now;

  if (c.done) {
    return (
      <span className={[styles.timer, styles.done, compact ? styles.compact : ''].filter(Boolean).join(' ')}>
        {doneLabel}
      </span>
    );
  }

  return (
    <span
      className={[styles.timer, compact ? styles.compact : ''].filter(Boolean).join(' ')}
      aria-label={`${c.days} days, ${c.hours} hours, ${c.minutes} minutes, ${c.seconds} seconds remaining`}
    >
      {c.days > 0 && (
        <>
          <span className={styles.value}>{pad(c.days)}</span>
          <span className={styles.unit}>d</span>
        </>
      )}
      <span className={styles.value}>{pad(c.hours)}</span>
      <span className={styles.unit}>h</span>
      <span className={styles.value}>{pad(c.minutes)}</span>
      <span className={styles.unit}>m</span>
      <span className={styles.value}>{pad(c.seconds)}</span>
      <span className={styles.unit}>s</span>
    </span>
  );
}
