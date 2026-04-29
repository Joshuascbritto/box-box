import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { fetchRaces, type Race } from '../api/races';
import { ApiError } from '../api/client';
import { LoadingLine } from '../components/LoadingLine';
import { DimensionLine } from '../components/DimensionLine';
import { StatusStamp } from '../components/StatusStamp';
import { CountdownTimer } from '../components/CountdownTimer';
import { Annotation } from '../components/Annotation';
import { ChevronRight } from '../components/icons/Icons';
import { formatRaceDate, formatRaceTime, pad } from '../lib/format';
import styles from './Schedule.module.css';

export function Schedule() {
  const [races, setRaces] = useState<Race[] | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    fetchRaces()
      .then(setRaces)
      .catch((e) => setError(e instanceof ApiError ? e.message : 'Failed to load schedule.'));
  }, []);

  if (error) {
    return <div className={styles.error}>Failed to load — {error}</div>;
  }

  if (!races) return <LoadingLine label="loading schedule" />;

  return (
    <section className={styles.page}>
      <header className={styles.header}>
        <div className={styles.titleRow}>
          <h1 className={styles.title}>Season Schedule</h1>
          <span className={styles.titleMeta}>· {races[0]?.season ?? '2026'}</span>
        </div>
        <DimensionLine label={`${races.length} rounds`} />
      </header>

      <ul className={styles.list}>
        {races.map((r) => (
          <RaceRow key={r.id} race={r} />
        ))}
        {races.length === 0 && (
          <li className={styles.empty}>
            <Annotation>NO RACES SCHEDULED</Annotation>
          </li>
        )}
      </ul>
    </section>
  );
}

function RaceRow({ race }: { race: Race }) {
  return (
    <li className={[styles.row, styles[race.status]].join(' ')}>
      <div className={styles.round}>
        <span className={styles.roundLabel}>R</span>
        <span className={styles.roundNumber}>{pad(race.round)}</span>
      </div>

      <div className={styles.body}>
        <Link to={`/races/${race.id}`} className={styles.name}>
          {race.name}
        </Link>
        <div className={styles.meta}>
          <span>{race.circuit}</span>
          <span className={styles.dot}>·</span>
          <span>{race.country}</span>
        </div>
      </div>

      <div className={styles.dates}>
        <span className={styles.date}>{formatRaceDate(race.race_date)}</span>
        <span className={styles.time}>{formatRaceTime(race.race_date)}</span>
      </div>

      <div className={styles.statusCell}>
        <StatusStamp status={race.status} small />
      </div>

      <div className={styles.action}>
        {race.status === 'upcoming' && (
          <Link to={`/races/${race.id}`} className={styles.cta}>
            submit prediction <ChevronRight />
          </Link>
        )}
        {race.status === 'locked' && (
          <CountdownTimer target={race.race_date} compact />
        )}
        {race.status === 'finished' && (
          <Link to={`/races/${race.id}`} className={styles.viewLink}>
            classification <ChevronRight />
          </Link>
        )}
      </div>
    </li>
  );
}
