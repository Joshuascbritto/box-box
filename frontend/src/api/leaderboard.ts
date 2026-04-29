import { api } from './client';

export interface LeaderboardRow {
  rank: number;
  user: { id: number; name: string };
  total_points: number;
  races_predicted: number;
  perfect_podiums: number;
}

export async function fetchLeaderboard(): Promise<LeaderboardRow[]> {
  const res = await api<{ data: LeaderboardRow[] }>('/api/leaderboard');
  return res.data;
}
