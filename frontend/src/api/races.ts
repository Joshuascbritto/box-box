import { api } from './client';

export type RaceStatus = 'upcoming' | 'locked' | 'finished';

export interface RaceResult {
  p1_driver_id: number;
  p2_driver_id: number;
  p3_driver_id: number;
  dnf_count: number;
  recorded_at: string | null;
}

export interface Race {
  id: number;
  season: number;
  round: number;
  name: string;
  circuit: string;
  country: string;
  race_date: string | null;
  predictions_close_at: string | null;
  status: RaceStatus;
  result: RaceResult | null;
}

export interface Driver {
  id: number;
  name: string;
  code: string;
  team: string;
  number: number;
}

export async function fetchRaces(status?: RaceStatus): Promise<Race[]> {
  const q = status ? `?status=${status}` : '';
  const res = await api<{ data: Race[] }>(`/api/races${q}`);
  return res.data;
}

export async function fetchRace(id: number): Promise<Race> {
  const res = await api<{ data: Race }>(`/api/races/${id}`);
  return res.data;
}

export async function fetchDrivers(): Promise<Driver[]> {
  const res = await api<{ data: Driver[] }>('/api/drivers');
  return res.data;
}
