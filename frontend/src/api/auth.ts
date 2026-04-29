import { api } from './client';

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  is_admin: boolean;
}

export async function requestLink(email: string): Promise<{ message: string }> {
  return api('/api/auth/request-link', {
    method: 'POST',
    body: JSON.stringify({ email }),
  });
}

export async function verifyToken(token: string): Promise<{ user: AuthUser; token: string }> {
  return api('/api/auth/verify', {
    method: 'POST',
    body: JSON.stringify({ token }),
  });
}

export async function fetchMe(): Promise<AuthUser> {
  return api('/api/auth/me');
}

export async function logout(): Promise<{ message: string }> {
  return api('/api/auth/logout', { method: 'POST' });
}
