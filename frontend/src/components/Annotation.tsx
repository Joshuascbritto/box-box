import styles from './Annotation.module.css';

interface AnnotationProps {
  children: React.ReactNode;
  tone?: 'neutral' | 'accent' | 'positive' | 'warning';
  className?: string;
}

export function Annotation({ children, tone = 'neutral', className }: AnnotationProps) {
  const cls = [styles.ann, styles[tone], className].filter(Boolean).join(' ');
  return (
    <span className={cls}>
      <span className={styles.bracket}>[</span>
      <span className={styles.body}>{children}</span>
      <span className={styles.bracket}>]</span>
    </span>
  );
}
