import type { ReactNode } from 'react';
import { Nav } from './Nav';
import { Footer } from './Footer';
import styles from './Layout.module.css';

export function Layout({ children }: { children: ReactNode }) {
  return (
    <>
      <div className="app-grid-bg" aria-hidden>
        <div className="app-grid-bg__inner">
          {Array.from({ length: 13 }).map((_, i) => (
            <span key={i} />
          ))}
        </div>
      </div>
      <div className={styles.shell}>
        <Nav />
        <main className={styles.main}>
          <div className={styles.container}>{children}</div>
        </main>
        <Footer />
      </div>
    </>
  );
}
