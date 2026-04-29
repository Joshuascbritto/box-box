import styles from './StatusStamp.module.css';
import type { RaceStatus } from '../api/races';

const LABELS: Record<RaceStatus, string> = {
  upcoming: 'UPCOMING',
  locked: 'BOXED',
  finished: 'CLASSIFIED',
};

export function StatusStamp({
  status,
  small = false,
}: {
  status: RaceStatus;
  small?: boolean;
}) {
  return (
    <span
      className={[styles.stamp, styles[status], small ? styles.small : ''].filter(Boolean).join(' ')}
      title={status}
    >
      {LABELS[status]}
    </span>
  );
}
