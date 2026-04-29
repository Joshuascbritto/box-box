import { useEffect, useState } from 'react';
import styles from './ThemeToggle.module.css';

type Theme = 'light' | 'dark';

const KEY = 'box-box:theme';

function readTheme(): Theme {
  const stored = localStorage.getItem(KEY);
  return stored === 'dark' ? 'dark' : 'light';
}

export function ThemeToggle() {
  const [theme, setTheme] = useState<Theme>(readTheme);

  useEffect(() => {
    document.documentElement.dataset.theme = theme;
    localStorage.setItem(KEY, theme);
  }, [theme]);

  return (
    <button
      type="button"
      className={styles.toggle}
      onClick={() => setTheme((t) => (t === 'light' ? 'dark' : 'light'))}
      aria-label={`Switch to ${theme === 'light' ? 'blueprint negative' : 'paper'} theme`}
    >
      <span className={theme === 'light' ? styles.active : ''}>paper</span>
      <span className={styles.divider} aria-hidden>·</span>
      <span className={theme === 'dark' ? styles.active : ''}>negative</span>
    </button>
  );
}
