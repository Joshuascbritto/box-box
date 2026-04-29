import { useEffect, useMemo, useState } from 'react';
import { fetchArchive, type ArchiveSeason } from '../api/archive';
import { fetchDrivers, type Driver } from '../api/races';
import { ApiError } from '../api/client';
import { LoadingLine } from '../components/LoadingLine';
import { DimensionLine } from '../components/DimensionLine';
import { Annotation } from '../components/Annotation';
import { formatRaceDate, pad } from '../lib/format';
import styles from './Archive.module.css';

export function Archive() {
  const [seasons, setSeasons] = useState<ArchiveSeason[] | null>(null);
  const [drivers, setDrivers] = useState<Driver[] | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [activeSeason, setActiveSeason] = useState<number | null>(null);

  useEffect(() => {
    Promise.all([fetchArchive(), fetchDrivers()])
      .then(([s, d]) => {
        setSeasons(s);
        setDrivers(d);
        if (s.length > 0) setActiveSeason(s[0].season);
      })
      .catch((e) => setError(e instanceof ApiError ? e.message : 'Failed to load archive.'));
  }, []);

  const driverIndex = useMemo(() => {
    const m = new Map<number, Driver>();
    drivers?.forEach((d) => m.set(d.id, d));
    return m;
  }, [drivers]);

  if (error) return <div className={styles.error}>Failed to load — {error}</div>;
  if (!seasons || !drivers) return <LoadingLine label="loading archive" />;

  if (seasons.length === 0) {
    return (
      <section className={styles.page}>
        <header className={styles.header}>
          <div className={styles.section}>§ D · archive</div>
          <h1 className={styles.title}>Archive</h1>
          <DimensionLine label="no records on file" />
        </header>
        <div className={styles.empty}>
          <Annotation>NO CLASSIFIED RACES IN ARCHIVE</Annotation>
        </div>
      </section>
    );
  }

  const current = seasons.find((s) => s.season === activeSeason) ?? seasons[0];

  const rangeLabel =
    seasons.length === 1
      ? String(seasons[0].season)
      : `${seasons[seasons.length - 1].season} → ${seasons[0].season}`;

  return (
    <section className={styles.page}>
      <header className={styles.header}>
        <div className={styles.section}>§ D · archive</div>
        <h1 className={styles.title}>Archive</h1>
        <DimensionLine label={`${rangeLabel} · ${seasons.length} seasons on file`} />
      </header>

      <nav className={styles.tabs} aria-label="season selector">
        {seasons.map((s) => (
          <button
            key={s.season}
            type="button"
            className={[styles.tab, s.season === current.season ? styles.tabActive : ''].join(' ')}
            onClick={() => setActiveSeason(s.season)}
          >
            <span className={styles.tabYear}>{s.season}</span>
            <span className={styles.tabCount}>{s.race_count} races</span>
          </button>
        ))}
      </nav>

      <SeasonPanel season={current} driverIndex={driverIndex} />
    </section>
  );
}

function SeasonPanel({
  season,
  driverIndex,
}: {
  season: ArchiveSeason;
  driverIndex: Map<number, Driver>;
}) {
  const renderDriver = (id: number | null) => {
    if (id == null) return <span className={styles.cellEmpty}>—</span>;
    const d = driverIndex.get(id);
    if (!d) return <span className={styles.cellEmpty}>#{id}</span>;
    return (
      <span className={styles.driverCell}>
        <span className={styles.driverCode}>{d.code}</span>
        <span className={styles.driverName}>{d.name}</span>
      </span>
    );
  };

  return (
    <div className={styles.panel}>
      <div className={styles.panelHead}>
        <h2 className={styles.panelTitle}>Season {season.season}</h2>
        <Annotation>{season.race_count} CLASSIFIED RACES</Annotation>
      </div>

      <table className={styles.table}>
        <thead>
          <tr>
            <th>round</th>
            <th>race</th>
            <th>P1</th>
            <th>P2</th>
            <th>P3</th>
            <th>DNF</th>
          </tr>
        </thead>
        <tbody>
          {season.races.map((r) => (
            <tr key={r.id}>
              <td className={styles.tdMono}>R{pad(r.round)}</td>
              <td>
                <div className={styles.raceName}>{r.name}</div>
                <div className={styles.raceMeta}>
                  {r.country} · {formatRaceDate(r.race_date)}
                </div>
              </td>
              <td>{renderDriver(r.p1_driver_id)}</td>
              <td>{renderDriver(r.p2_driver_id)}</td>
              <td>{renderDriver(r.p3_driver_id)}</td>
              <td className={styles.tdMonoRight}>{r.dnf_count ?? '—'}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
