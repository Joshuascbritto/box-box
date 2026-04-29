import { Link, NavLink } from 'react-router-dom';
import { useAuth } from '../../auth/AuthContext';
import styles from './Nav.module.css';

export function Nav() {
  const { user, signOut } = useAuth();

  return (
    <header className={styles.nav}>
      <div className={styles.inner}>
        <Link to="/" className={styles.brand}>
          <span className={styles.wordmark}>box-box</span>
          <span className={styles.subMark}>podium predictions · 2026</span>
        </Link>

        <nav className={styles.links} aria-label="primary">
          <NavLink
            to="/"
            end
            className={({ isActive }) => [styles.link, isActive ? styles.active : ''].join(' ')}
          >
            schedule
          </NavLink>
          <NavLink
            to="/leaderboard"
            className={({ isActive }) => [styles.link, isActive ? styles.active : ''].join(' ')}
          >
            standings
          </NavLink>
          <NavLink
            to="/archive"
            className={({ isActive }) => [styles.link, isActive ? styles.active : ''].join(' ')}
          >
            archive
          </NavLink>
          {user && (
            <NavLink
              to="/me"
              className={({ isActive }) => [styles.link, isActive ? styles.active : ''].join(' ')}
            >
              log
            </NavLink>
          )}
        </nav>

        <div className={styles.right}>
          {user ? (
            <>
              <span className={styles.email}>{user.email}</span>
              <button type="button" className={styles.logout} onClick={signOut}>
                logout
              </button>
            </>
          ) : (
            <Link to="/login" className={styles.login}>
              request access →
            </Link>
          )}
        </div>
      </div>
    </header>
  );
}
