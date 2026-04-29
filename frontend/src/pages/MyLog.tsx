import { useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { fetchMyPredictions, type Prediction } from '../api/predictions';
import { fetchDrivers, type Driver } from '../api/races';
import { ApiError } from '../api/client';
import { LoadingLine } from '../components/LoadingLine';
import { DimensionLine } from '../components/DimensionLine';
import { Annotation } from '../components/Annotation';
import { formatRaceDate, pad } from '../lib/format';
import styles from './MyLog.module.css';

export function MyLog() {
  const [predictions, setPredictions] = useState<Prediction[] | null>(null);
  const [drivers, setDrivers] = useState<Driver[] | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    Promise.all([fetchMyPredictions(), fetchDrivers()])
      .then(([p, d]) => {
        setPredictions(p);
        setDrivers(d);
      })
      .catch((e) => setError(e instanceof ApiError ? e.message : 'Failed to load.'));
  }, []);

  const codeMap = useMemo(() => {
    const m = new Map<number, string>();
    drivers?.forEach((d) => m.set(d.id, d.code));
    return m;
  }, [drivers]);

  const aggregate = useMemo(() => {
    if (!predictions) return null;
    const finished = predictions.filter((p) => p.race?.status === 'finished');
    const totalPoints = finished.reduce((sum, p) => sum + (p.points ?? 0), 0);
    const perfectThreshold = 25 + 18 + 15 + 15; // 73
    const perfect = finished.filter((p) => (p.points ?? 0) >= perfectThreshold).length;
    const best = finished.reduce<{ points: number; round: number | null }>(
      (acc, p) => ((p.points ?? 0) > acc.points ? { points: p.points ?? 0, round: p.race?.round ?? null } : acc),
      { points: 0, round: null },
    );
    return {
      totalPoints,
      perfect,
      bestRound: best.round,
      bestPoints: best.points,
      avg: finished.length ? Math.round(totalPoints / finished.length) : 0,
      finishedCount: finished.length,
    };
  }, [predictions]);

  if (error) return <div className={styles.error}>Failed to load — {error}</div>;
  if (!predictions || !drivers) return <LoadingLine label="loading log" />;

  const renderPrediction = (p: Prediction) =>
    [p.p1_driver_id, p.p2_driver_id, p.p3_driver_id]
      .map((id) => (id ? codeMap.get(id) ?? '???' : '—'))
      .join(' · ') + ` | ${p.dnf_count ?? '—'} DNF`;

  const renderResult = (p: Prediction) => {
    if (p.race?.status !== 'finished' || !p.race.result) return '—';
    const r = p.race.result;
    return (
      [r.p1_driver_id, r.p2_driver_id, r.p3_driver_id]
        .map((id) => codeMap.get(id) ?? '???')
        .join(' · ') + ` | ${r.dnf_count} DNF`
    );
  };

  return (
    <section className={styles.page}>
      <header className={styles.header}>
        <div className={styles.section}>§ C · personal log</div>
        <h1 className={styles.title}>My log</h1>
        <DimensionLine label={`${predictions.length} entries`} />
      </header>

      {aggregate && aggregate.finishedCount > 0 && (
        <div className={styles.aggregate}>
          <Stat label="total points" value={aggregate.totalPoints} />
          <Stat label="avg / race" value={aggregate.avg} />
          <Stat label="perfect podiums" value={aggregate.perfect} />
          <Stat
            label="best round"
            value={aggregate.bestRound ? `R${pad(aggregate.bestRound)} · ${aggregate.bestPoints}pts` : '—'}
          />
        </div>
      )}

      {predictions.length === 0 ? (
        <div className={styles.empty}>
          <Annotation>NO PREDICTIONS LOGGED</Annotation>
          <p>
            Submit a prediction from the <Link to="/">schedule</Link> to start the log.
          </p>
        </div>
      ) : (
        <table className={styles.table}>
          <thead>
            <tr>
              <th>round</th>
              <th>race</th>
              <th>your call</th>
              <th>result</th>
              <th>points</th>
            </tr>
          </thead>
          <tbody>
            {predictions.map((p) => (
              <tr key={p.id}>
                <td className={styles.tdMono}>{p.race ? `R${pad(p.race.round)}` : '—'}</td>
                <td>
                  {p.race ? (
                    <Link to={`/races/${p.race.id}`} className={styles.raceLink}>
                      {p.race.name}
                    </Link>
                  ) : (
                    '—'
                  )}
                  <div className={styles.dateLine}>
                    {p.race ? formatRaceDate(p.race.race_date) : ''}
                  </div>
                </td>
                <td className={styles.tdMono}>{renderPrediction(p)}</td>
                <td className={styles.tdMono}>{renderResult(p)}</td>
                <td className={styles.tdMonoRight}>{p.points ?? '—'}</td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </section>
  );
}

function Stat({ label, value }: { label: string; value: string | number }) {
  return (
    <div className={styles.stat}>
      <div className={styles.statLabel}>{label}</div>
      <div className={styles.statValue}>{value}</div>
    </div>
  );
}
