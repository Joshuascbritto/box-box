import styles from './DnfStepper.module.css';

interface DnfStepperProps {
  value: number;
  onChange: (n: number) => void;
  min?: number;
  max?: number;
}

export function DnfStepper({ value, onChange, min = 0, max = 20 }: DnfStepperProps) {
  const dec = () => onChange(Math.max(min, value - 1));
  const inc = () => onChange(Math.min(max, value + 1));

  return (
    <div className={styles.wrap}>
      <div className={styles.head}>
        <span className={styles.label}>DNF count</span>
        <span className={styles.range}>{min}–{max}</span>
      </div>
      <div className={styles.row}>
        <button
          type="button"
          className={styles.btn}
          onClick={dec}
          disabled={value <= min}
          aria-label="decrease"
        >
          −
        </button>
        <input
          className={styles.input}
          type="number"
          inputMode="numeric"
          min={min}
          max={max}
          value={value}
          onChange={(e) => {
            const n = Number(e.target.value);
            if (Number.isFinite(n)) onChange(Math.max(min, Math.min(max, Math.floor(n))));
          }}
        />
        <button
          type="button"
          className={styles.btn}
          onClick={inc}
          disabled={value >= max}
          aria-label="increase"
        >
          +
        </button>
      </div>
    </div>
  );
}
