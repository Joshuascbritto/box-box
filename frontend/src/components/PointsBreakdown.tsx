import styles from './PointsBreakdown.module.css';

interface BreakdownRow {
  label: string;
  points: number;
}

export function PointsBreakdown({
  rows,
  total,
}: {
  rows: BreakdownRow[];
  total: number;
}) {
  return (
    <div className={styles.wrap}>
      <div className={styles.title}>Note —— points</div>
      <ul className={styles.list}>
        {rows.map((r, i) => (
          <li key={i} className={styles.row}>
            <span className={styles.label}>{r.label}</span>
            <span className={styles.dots} aria-hidden />
            <span className={[styles.value, r.points === 0 ? styles.zero : ''].join(' ')}>
              {r.points >= 0 ? '+' : ''}{r.points}
            </span>
          </li>
        ))}
      </ul>
      <div className={styles.total}>
        <span>total</span>
        <span className={styles.dots} aria-hidden />
        <span className={styles.totalValue}>{total}</span>
      </div>
    </div>
  );
}
