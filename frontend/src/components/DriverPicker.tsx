import type { Driver } from '../api/races';
import styles from './DriverPicker.module.css';

interface DriverPickerProps {
  label: string;
  position: 'P1' | 'P2' | 'P3';
  drivers: Driver[];
  value: number | null;
  exclude: number[];
  onChange: (id: number) => void;
  metalLabel: string;
}

export function DriverPicker({
  label,
  position,
  drivers,
  value,
  exclude,
  onChange,
  metalLabel,
}: DriverPickerProps) {
  const available = drivers.filter((d) => !exclude.includes(d.id) || d.id === value);

  return (
    <div className={styles.wrap}>
      <div className={styles.head}>
        <span className={styles.position}>{position}</span>
        <span className={styles.lead}>·</span>
        <span className={styles.metal}>{metalLabel}</span>
      </div>
      <select
        className={styles.select}
        value={value ?? ''}
        onChange={(e) => onChange(Number(e.target.value))}
        aria-label={label}
      >
        <option value="">— select driver —</option>
        {available.map((d) => (
          <option key={d.id} value={d.id}>
            {d.code} · {d.name} · {d.team}
          </option>
        ))}
      </select>
    </div>
  );
}
