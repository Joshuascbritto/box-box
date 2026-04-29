import styles from './TechCallout.module.css';

interface TechCalloutProps {
  title?: string;
  children: React.ReactNode;
  className?: string;
}

export function TechCallout({ title = 'Note', children, className }: TechCalloutProps) {
  const cls = [styles.box, className].filter(Boolean).join(' ');
  return (
    <aside className={cls}>
      <div className={styles.title}>{title} ——</div>
      <div className={styles.body}>{children}</div>
    </aside>
  );
}
