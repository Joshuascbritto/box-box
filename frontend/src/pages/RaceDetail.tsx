import { useEffect, useMemo, useState, type FormEvent } from 'react';
import { Link, useParams } from 'react-router-dom';
import { fetchDrivers, fetchRace, type Driver, type Race } from '../api/races';
import {
  fetchPredictionsForRace,
  submitPrediction,
  type Prediction,
} from '../api/predictions';
import { ApiError } from '../api/client';
import { useAuth } from '../auth/AuthContext';
import { LoadingLine } from '../components/LoadingLine';
import { DimensionLine } from '../components/DimensionLine';
import { StatusStamp } from '../components/StatusStamp';
import { CountdownTimer } from '../components/CountdownTimer';
import { Annotation } from '../components/Annotation';
import { TechCallout } from '../components/TechCallout';
import { DriverPicker } from '../components/DriverPicker';
import { DnfStepper } from '../components/DnfStepper';
import { PointsBreakdown } from '../components/PointsBreakdown';
import { breakdown } from '../lib/scoring';
import { formatRaceDate, pad } from '../lib/format';
import styles from './RaceDetail.module.css';

export function RaceDetail() {
  const { id } = useParams();
  const raceId = Number(id);
  const { user } = useAuth();

  const [race, setRace] = useState<Race | null>(null);
  const [drivers, setDrivers] = useState<Driver[] | null>(null);
  const [predictions, setPredictions] = useState<Prediction[] | null>(null);
  const [visibility, setVisibility] = useState<'self' | 'all' | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!Number.isFinite(raceId)) return;

    Promise.all([fetchRace(raceId), fetchDrivers()])
      .then(([r, d]) => {
        setRace(r);
        setDrivers(d);
      })
      .catch((e) => setError(e instanceof ApiError ? e.message : 'Failed to load race.'));

    if (user) {
      fetchPredictionsForRace(raceId)
        .then((res) => {
          setPredictions(res.data);
          setVisibility(res.visibility);
        })
        .catch(() => {
          // Leave null — page still renders.
        });
    }
  }, [raceId, user]);

  if (error) return <div className={styles.error}>Failed to load — {error}</div>;
  if (!race || !drivers) return <LoadingLine label="loading race" />;

  return (
    <article className={styles.page}>
      <header className={styles.banner}>
        <div className={styles.bannerHead}>
          <div className={styles.round}>
            <span className={styles.roundLabel}>Round</span>
            <span className={styles.roundNumber}>{pad(race.round)}</span>
          </div>
          <StatusStamp status={race.status} />
        </div>
        <h1 className={styles.title}>{race.name}</h1>
        <div className={styles.meta}>
          <span>{race.circuit}</span>
          <span className={styles.dot}>·</span>
          <span>{race.country}</span>
          <span className={styles.dot}>·</span>
          <span>{formatRaceDate(race.race_date)}</span>
        </div>

        <CircuitSchematic />

        {race.status === 'upcoming' && race.predictions_close_at && (
          <div className={styles.countdown}>
            <Annotation>predictions close in</Annotation>
            <CountdownTimer target={race.predictions_close_at} />
          </div>
        )}

        <DimensionLine label={`§ 04.2 — ${race.status}`} />
      </header>

      {race.status === 'upcoming' && (
        <UpcomingForm
          race={race}
          drivers={drivers}
          existing={predictions?.[0] ?? null}
          authed={Boolean(user)}
        />
      )}

      {race.status === 'locked' && (
        <LockedView
          race={race}
          drivers={drivers}
          predictions={predictions}
          visibility={visibility}
          authed={Boolean(user)}
        />
      )}

      {race.status === 'finished' && (
        <FinishedView
          race={race}
          drivers={drivers}
          predictions={predictions}
          visibility={visibility}
        />
      )}
    </article>
  );
}

function CircuitSchematic() {
  // Generic placeholder rectangle with corner labels — sells the engineering
  // theme without faking real circuit data.
  return (
    <svg
      className={styles.schematic}
      viewBox="0 0 800 200"
      preserveAspectRatio="xMidYMid meet"
      role="img"
      aria-label="circuit schematic placeholder"
    >
      <rect x="40" y="40" width="720" height="120" fill="none" stroke="currentColor" strokeWidth="1" />
      <rect x="60" y="60" width="680" height="80" fill="none" stroke="currentColor" strokeWidth="1" strokeDasharray="2 4" opacity="0.5" />
      <g fontFamily="JetBrains Mono, monospace" fontSize="9" letterSpacing="1.5" fill="currentColor">
        <text x="50" y="34">T1</text>
        <text x="750" y="34" textAnchor="end">T2</text>
        <text x="750" y="180" textAnchor="end">T3</text>
        <text x="50" y="180">T4</text>
        <text x="400" y="34" textAnchor="middle" opacity="0.7">— SCHEMATIC NOT TO SCALE —</text>
      </g>
    </svg>
  );
}

function UpcomingForm({
  race,
  drivers,
  existing,
  authed,
}: {
  race: Race;
  drivers: Driver[];
  existing: Prediction | null;
  authed: boolean;
}) {
  const [p1, setP1] = useState<number | null>(existing?.p1_driver_id ?? null);
  const [p2, setP2] = useState<number | null>(existing?.p2_driver_id ?? null);
  const [p3, setP3] = useState<number | null>(existing?.p3_driver_id ?? null);
  const [dnf, setDnf] = useState<number>(existing?.dnf_count ?? 2);
  const [submitting, setSubmitting] = useState(false);
  const [err, setErr] = useState<string | null>(null);
  const [savedAt, setSavedAt] = useState<string | null>(existing?.submitted_at ?? null);
  const [showWhat, setShowWhat] = useState(false);

  useEffect(() => {
    if (existing) {
      setP1(existing.p1_driver_id);
      setP2(existing.p2_driver_id);
      setP3(existing.p3_driver_id);
      setDnf(existing.dnf_count ?? 2);
      setSavedAt(existing.submitted_at);
    }
  }, [existing]);

  const exclude = useMemo(
    () => [p1, p2, p3].filter((x): x is number => x != null),
    [p1, p2, p3],
  );

  const canSubmit =
    authed &&
    p1 != null &&
    p2 != null &&
    p3 != null &&
    new Set([p1, p2, p3]).size === 3 &&
    !submitting;

  async function onSubmit(e: FormEvent) {
    e.preventDefault();
    if (!canSubmit) return;
    setSubmitting(true);
    setErr(null);
    try {
      const saved = await submitPrediction({
        race_id: race.id,
        p1_driver_id: p1!,
        p2_driver_id: p2!,
        p3_driver_id: p3!,
        dnf_count: dnf,
      });
      setSavedAt(saved.submitted_at);
    } catch (e) {
      setErr(e instanceof ApiError ? e.message : 'Could not submit.');
    } finally {
      setSubmitting(false);
    }
  }

  if (!authed) {
    return (
      <section className={styles.formPane}>
        <TechCallout title="Note —— sign in required">
          Submitting a prediction requires a credentialed account.
          <br />
          <Link to="/login" className={styles.inlineLink}>Request access →</Link>
        </TechCallout>
      </section>
    );
  }

  return (
    <section className={styles.formPane}>
      <form className={styles.form} onSubmit={onSubmit}>
        <div className={styles.formHead}>
          <h2 className={styles.formTitle}>Your call</h2>
          <Annotation>P1 · P2 · P3 · DNF</Annotation>
        </div>

        <div className={styles.pickers}>
          <DriverPicker
            label="P1 driver"
            position="P1"
            metalLabel="gold"
            drivers={drivers}
            value={p1}
            exclude={exclude.filter((id) => id !== p1)}
            onChange={setP1}
          />
          <DriverPicker
            label="P2 driver"
            position="P2"
            metalLabel="silver"
            drivers={drivers}
            value={p2}
            exclude={exclude.filter((id) => id !== p2)}
            onChange={setP2}
          />
          <DriverPicker
            label="P3 driver"
            position="P3"
            metalLabel="bronze"
            drivers={drivers}
            value={p3}
            exclude={exclude.filter((id) => id !== p3)}
            onChange={setP3}
          />
        </div>

        <DnfStepper value={dnf} onChange={setDnf} />

        <div className={styles.submitRow}>
          <button
            type="submit"
            className={styles.cta}
            disabled={!canSubmit}
            onMouseEnter={() => setShowWhat(true)}
            onMouseLeave={() => setShowWhat(false)}
            onFocus={() => setShowWhat(true)}
            onBlur={() => setShowWhat(false)}
          >
            {savedAt ? 'update the call' : 'make the call'}
          </button>
          <Annotation tone="accent">DRS</Annotation>
        </div>

        {showWhat && (
          <TechCallout title="Note —— box, box">
            In F1, "box, box" is the radio call to pit now. Once you submit, your
            prediction is committed for this round.
          </TechCallout>
        )}

        {savedAt && !err && (
          <Annotation tone="positive">CALL ON FILE · {new Date(savedAt).toLocaleString()}</Annotation>
        )}
        {err && <div className={styles.formError}>{err}</div>}
      </form>

      <aside className={styles.scoringNote}>
        <TechCallout title="Note —— scoring">
          P1 exact +25 · P2 exact +18 · P3 exact +15
          <br />
          Driver on podium, wrong position +5 each
          <br />
          DNF exact +20 · off by 1 +10 · off by 2 +3
          <br />
          Perfect podium bonus +15
        </TechCallout>
      </aside>
    </section>
  );
}

function LockedView({
  race,
  drivers,
  predictions,
  visibility,
  authed,
}: {
  race: Race;
  drivers: Driver[];
  predictions: Prediction[] | null;
  visibility: 'self' | 'all' | null;
  authed: boolean;
}) {
  if (!authed) {
    return (
      <TechCallout title="Note —— predictions locked">
        Predictions for {race.name} are locked. <Link to="/login" className={styles.inlineLink}>Sign in</Link> to view what users picked.
      </TechCallout>
    );
  }

  if (!predictions || visibility !== 'all') {
    return <LoadingLine label="loading tally" />;
  }

  const codeMap = new Map(drivers.map((d) => [d.id, d.code]));
  const tally = computeTally(predictions, drivers);

  return (
    <section className={styles.locked}>
      <h2 className={styles.formTitle}>Predictions tally</h2>
      <p className={styles.lockedLead}>
        {predictions.length} predictors · podium picks distribution
      </p>

      <div className={styles.tally}>
        {tally.map((row) => (
          <div key={row.code} className={styles.tallyRow}>
            <span className={styles.tallyCode}>{row.code}</span>
            <span className={styles.tallyName}>{row.name}</span>
            <div className={styles.tallyBar} aria-hidden>
              <span style={{ width: `${row.percent}%` }} />
            </div>
            <span className={styles.tallyPct}>{row.percent}%</span>
          </div>
        ))}
        {tally.length === 0 && <Annotation>NO PREDICTIONS RECORDED</Annotation>}
      </div>

      <h3 className={styles.subhead}>Predictions roll</h3>
      <table className={styles.predTable}>
        <thead>
          <tr>
            <th>predictor</th>
            <th>P1</th>
            <th>P2</th>
            <th>P3</th>
            <th>DNF</th>
          </tr>
        </thead>
        <tbody>
          {predictions.map((p) => (
            <tr key={p.id}>
              <td>{p.user?.name ?? '—'}</td>
              <td className={styles.tdMono}>{p.p1_driver_id ? codeMap.get(p.p1_driver_id) : '—'}</td>
              <td className={styles.tdMono}>{p.p2_driver_id ? codeMap.get(p.p2_driver_id) : '—'}</td>
              <td className={styles.tdMono}>{p.p3_driver_id ? codeMap.get(p.p3_driver_id) : '—'}</td>
              <td className={styles.tdMono}>{p.dnf_count ?? '—'}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </section>
  );
}

function FinishedView({
  race,
  drivers,
  predictions,
  visibility,
}: {
  race: Race;
  drivers: Driver[];
  predictions: Prediction[] | null;
  visibility: 'self' | 'all' | null;
}) {
  const codeMap = new Map(drivers.map((d) => [d.id, d]));
  const result = race.result;

  if (!result) {
    return <TechCallout title="Note —— pending">Awaiting classification.</TechCallout>;
  }

  return (
    <section className={styles.finished}>
      <div className={styles.classification}>
        <div className={styles.classHead}>
          <h2 className={styles.formTitle}>Classification</h2>
          <StatusStamp status="finished" />
        </div>
        <ol className={styles.podiumList}>
          {[result.p1_driver_id, result.p2_driver_id, result.p3_driver_id].map((id, i) => {
            const d = codeMap.get(id);
            const labels = ['P1', 'P2', 'P3'] as const;
            return (
              <li key={i} className={styles.podiumItem}>
                <span className={styles.podiumPos}>{labels[i]}</span>
                <span className={styles.podiumName}>
                  {d ? `${d.code} · ${d.name}` : `#${id}`}
                </span>
                <span className={styles.podiumTeam}>{d?.team ?? ''}</span>
              </li>
            );
          })}
        </ol>
        <div className={styles.dnfRow}>
          <Annotation>DNF</Annotation>
          <span className={styles.dnfValue}>{result.dnf_count}</span>
        </div>
      </div>

      {predictions && visibility === 'all' && (
        <YourScore
          predictions={predictions}
          result={result}
          drivers={drivers}
        />
      )}
    </section>
  );
}

function YourScore({
  predictions,
  result,
  drivers,
}: {
  predictions: Prediction[];
  result: NonNullable<Race['result']>;
  drivers: Driver[];
}) {
  const { user } = useAuth();
  const own = predictions.find((p) => p.user?.id === user?.id);

  if (!user) {
    return null;
  }
  if (!own) {
    return (
      <TechCallout title="Note —— no prediction logged">
        You didn't submit for this round.
      </TechCallout>
    );
  }

  const codeMap = new Map(drivers.map((d) => [d.id, d.code]));
  const rows = breakdown(own, result);
  const total = own.points ?? rows.reduce((s, r) => s + r.points, 0);

  return (
    <div className={styles.yourScore}>
      <div className={styles.classHead}>
        <h2 className={styles.formTitle}>Your call</h2>
        <Annotation>logged</Annotation>
      </div>
      <ol className={styles.podiumList}>
        {[own.p1_driver_id, own.p2_driver_id, own.p3_driver_id].map((id, i) => {
          const labels = ['P1', 'P2', 'P3'] as const;
          return (
            <li key={i} className={styles.podiumItem}>
              <span className={styles.podiumPos}>{labels[i]}</span>
              <span className={styles.podiumName}>
                {id ? codeMap.get(id) ?? `#${id}` : '—'}
              </span>
            </li>
          );
        })}
      </ol>
      <div className={styles.dnfRow}>
        <Annotation>DNF</Annotation>
        <span className={styles.dnfValue}>{own.dnf_count ?? '—'}</span>
      </div>
      <PointsBreakdown rows={rows} total={total} />
    </div>
  );
}

function computeTally(predictions: Prediction[], drivers: Driver[]) {
  const totals = new Map<number, number>();
  let totalPicks = 0;

  predictions.forEach((p) => {
    [p.p1_driver_id, p.p2_driver_id, p.p3_driver_id].forEach((id) => {
      if (id == null) return;
      totals.set(id, (totals.get(id) ?? 0) + 1);
      totalPicks++;
    });
  });

  if (totalPicks === 0) return [];

  return drivers
    .map((d) => {
      const count = totals.get(d.id) ?? 0;
      return {
        code: d.code,
        name: d.name,
        count,
        percent: Math.round((count / predictions.length) * 100),
      };
    })
    .filter((r) => r.count > 0)
    .sort((a, b) => b.count - a.count);
}
