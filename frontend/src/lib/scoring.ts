import type { Prediction } from '../api/predictions';
import type { RaceResult } from '../api/races';

export const POINTS = {
  P1_EXACT: 25,
  P2_EXACT: 18,
  P3_EXACT: 15,
  PODIUM_WRONG_POSITION: 5,
  DNF_EXACT: 20,
  DNF_OFF_BY_ONE: 10,
  DNF_OFF_BY_TWO: 3,
  PERFECT_PODIUM_BONUS: 15,
} as const;

export interface BreakdownRow {
  label: string;
  points: number;
}

/**
 * Mirrors backend ScoringService::breakdown — used to render
 * the per-component points display on the result page.
 */
export function breakdown(prediction: Prediction, result: RaceResult): BreakdownRow[] {
  const rows: BreakdownRow[] = [];

  const predicted = [
    { id: prediction.p1_driver_id, exact: POINTS.P1_EXACT, label: 'P1' },
    { id: prediction.p2_driver_id, exact: POINTS.P2_EXACT, label: 'P2' },
    { id: prediction.p3_driver_id, exact: POINTS.P3_EXACT, label: 'P3' },
  ];

  const actual = [result.p1_driver_id, result.p2_driver_id, result.p3_driver_id];

  let exactCount = 0;

  predicted.forEach((row, idx) => {
    if (row.id == null) {
      rows.push({ label: `${row.label} unselected`, points: 0 });
      return;
    }
    if (row.id === actual[idx]) {
      rows.push({ label: `${row.label} exact`, points: row.exact });
      exactCount++;
    } else if (actual.includes(row.id)) {
      rows.push({ label: `${row.label} on podium`, points: POINTS.PODIUM_WRONG_POSITION });
    } else {
      rows.push({ label: `${row.label} miss`, points: 0 });
    }
  });

  if (exactCount === 3) {
    rows.push({ label: 'Perfect podium', points: POINTS.PERFECT_PODIUM_BONUS });
  }

  if (prediction.dnf_count != null) {
    const delta = Math.abs(prediction.dnf_count - result.dnf_count);
    rows.push(
      delta === 0
        ? { label: 'DNF exact', points: POINTS.DNF_EXACT }
        : delta === 1
          ? { label: 'DNF off by 1', points: POINTS.DNF_OFF_BY_ONE }
          : delta === 2
            ? { label: 'DNF off by 2', points: POINTS.DNF_OFF_BY_TWO }
            : { label: 'DNF miss', points: 0 },
    );
  }

  return rows;
}
