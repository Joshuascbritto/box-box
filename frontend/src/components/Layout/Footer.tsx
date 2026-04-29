import { ThemeToggle } from '../ThemeToggle';
import styles from './Footer.module.css';

export function Footer() {
  const today = new Date();
  const dateStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}`;

  return (
    <footer className={styles.footer}>
      <div className={styles.inner}>
        <div className={styles.cols}>
          <div className={styles.col}>
            <div className={styles.wordmark}>box-box</div>
            <div className={styles.tagline}>Make the call.</div>
            <div className={styles.tag}>F1 podium predictions, settled by results.</div>
          </div>

          <div className={styles.col}>
            <div className={styles.colTitle}>theme</div>
            <ThemeToggle />
          </div>

          <div className={styles.col}>
            <div className={styles.colTitle}>links</div>
            <ul className={styles.links}>
              <li><a href="https://github.com/" rel="noreferrer">github</a></li>
              <li><a href="mailto:hello@box-box.app">contact</a></li>
              <li><a href="/credits">/credits</a></li>
            </ul>
          </div>
        </div>

        <div className={styles.drawingBlock} aria-label="drawing block">
          <div className={styles.dbRow}>
            <span><strong>PROJECT</strong> box-box</span>
            <span><strong>DRAWN BY</strong> Joshua</span>
            <span><strong>SCALE</strong> 1:1</span>
          </div>
          <div className={styles.dbRow}>
            <span><strong>REV</strong> 0.1</span>
            <span><strong>DATE</strong> {dateStr}</span>
            <span><strong>SHEET</strong> 1/1</span>
          </div>
        </div>
      </div>
    </footer>
  );
}
