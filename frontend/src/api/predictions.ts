import { api } from './client';
import type { Race, RaceStatus } from './races';

export interface PredictionInput {
  race_id: number;
  p1_driver_id: number;
  p2_driver_id: number;
  p3_driver_id: number;
  dnf_count: number;
}

export interface Prediction {
  id: number;
  race_id: number;
  p1_driver_id: number | null;
  p2_driver_id: number | null;
  p3_driver_id: number | null;
  dnf_count: number | null;
  points: number | null;
  submitted_at: string | null;
  user?: { id: number; name: string };
  race?: Pick<Race, 'id' | 'season' | 'round' | 'name' | 'race_date' | 'status' | 'result'>;
}

export async function submitPrediction(input: PredictionInput): Promise<Prediction> {
  const res = await api<{ data: Prediction }>('/api/predictions', {
    method: 'POST',
    body: JSON.stringify(input),
  });
  return res.data;
}

export async function fetchMyPredictions(): Promise<Prediction[]> {
  const res = await api<{ data: Prediction[] }>('/api/predictions/me');
  return res.data;
}

export async function fetchPredictionsForRace(
  raceId: number,
): Promise<{ data: Prediction[]; visibility: 'self' | 'all' }> {
  return api(`/api/predictions/race/${raceId}`);
}

export type { RaceStatus };
