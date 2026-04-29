import { useEffect, useState } from 'react';
import { fetchLeaderboard, type LeaderboardRow } from '../api/leaderboard';
import { ApiError } from '../api/client';
import { useAuth } from '../auth/AuthContext';
import { LoadingLine } from '../components/LoadingLine';
import { DimensionLine } from '../components/DimensionLine';
import { Annotation } from '../components/Annotation';
import styles from './Leaderboard.module.css';

export function Leaderboard() {
  const [rows, setRows] = useState<LeaderboardRow[] | null>(null);
  const [error, setError] = useState<string | null>(null);
  const { user } = useAuth();

  useEffect(() => {
    fetchLeaderboard()
      .then(setRows)
      .catch((e) => setError(e instanceof ApiError ? e.message : 'Failed to load standings.'));
  }, []);

  if (error) return <div className={styles.error}>Failed to load — {error}</div>;
  if (!rows) return <LoadingLine label="loading standings" />;

  const top3 = rows.slice(0, 3);
  const rest = rows.slice(3);

  return (
    <section className={styles.page}>
      <header className={styles.header}>
        <div className={styles.section}>§ B · standings</div>
        <h1 className={styles.title}>Standings · 2026</h1>
        <DimensionLine label={`${rows.length} predictors`} />
      </header>

      {rows.length === 0 ? (
        <div className={styles.empty}>
          <Annotation>NO PREDICTIONS SETTLED YET</Annotation>
          <p>Standings populate once a race is classified.</p>
        </div>
      ) : (
        <>
          <ol className={styles.podium}>
            {top3.map((row) => (
              <li
                key={row.user.id}
                className={[
                  styles.podiumRow,
                  user?.id === row.user.id ? styles.you : '',
                ].join(' ')}
              >
                <div className={styles.podiumRank}>
                  <span className={row.rank === 1 ? styles.rankAccent : ''}>{row.rank}</span>
                </div>
                <div className={styles.podiumBody}>
                  <div className={styles.predictor}>{row.user.name}</div>
                  <div className={styles.subline}>
                    <span>{row.races_predicted} races</span>
                    {row.perfect_podiums > 0 && (
                      <>
                        <span className={styles.dot}>·</span>
                        <span>{row.perfect_podiums} perfect</span>
                      </>
                    )}
                  </div>
                </div>
                <div className={styles.points}>{row.total_points}</div>
              </li>
            ))}
          </ol>

          {rest.length > 0 && (
            <table className={styles.table}>
              <thead>
                <tr>
                  <th>rank</th>
                  <th>predictor</th>
                  <th>points</th>
                  <th>races</th>
                  <th>perfect</th>
                </tr>
              </thead>
              <tbody>
                {rest.map((row) => (
                  <tr
                    key={row.user.id}
                    className={user?.id === row.user.id ? styles.youRow : ''}
                  >
                    <td className={styles.tdMono}>{row.rank}</td>
                    <td>{row.user.name}</td>
                    <td className={styles.tdMono}>{row.total_points}</td>
                    <td className={styles.tdMono}>{row.races_predicted}</td>
                    <td className={styles.tdMono}>{row.perfect_podiums || '—'}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </>
      )}
    </section>
  );
}
