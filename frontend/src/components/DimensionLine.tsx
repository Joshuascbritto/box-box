import styles from './DimensionLine.module.css';

interface DimensionLineProps {
  orientation?: 'horizontal' | 'vertical';
  label?: string;
  className?: string;
}

export function DimensionLine({
  orientation = 'horizontal',
  label,
  className,
}: DimensionLineProps) {
  const cls = [
    styles.dim,
    orientation === 'vertical' ? styles.vertical : styles.horizontal,
    className,
  ]
    .filter(Boolean)
    .join(' ');

  return (
    <div className={cls} role="presentation">
      <span className={styles.tickStart} />
      <span className={styles.line} />
      {label && <span className={styles.label}>{label}</span>}
      <span className={styles.line} />
      <span className={styles.tickEnd} />
    </div>
  );
}
