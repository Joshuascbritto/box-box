import styles from './LoadingLine.module.css';

export function LoadingLine({ label = 'loading' }: { label?: string }) {
  return (
    <div className={styles.wrap} role="status" aria-live="polite">
      <div className={styles.line}>
        <div className={styles.fill} />
      </div>
      <div className={styles.label}>{label}…</div>
    </div>
  );
}
