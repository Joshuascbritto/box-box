import { api } from './client';

export interface ArchiveRace {
  id: number;
  round: number;
  name: string;
  circuit: string;
  country: string;
  race_date: string | null;
  p1_driver_id: number | null;
  p2_driver_id: number | null;
  p3_driver_id: number | null;
  dnf_count: number | null;
}

export interface ArchiveSeason {
  season: number;
  race_count: number;
  races: ArchiveRace[];
}

export async function fetchArchive(): Promise<ArchiveSeason[]> {
  const res = await api<{ data: ArchiveSeason[] }>('/api/archive');
  return res.data;
}
