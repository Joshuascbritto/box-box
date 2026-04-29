import { useEffect, useState } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import { verifyToken } from '../api/auth';
import { ApiError } from '../api/client';
import { useAuth } from '../auth/AuthContext';
import { LoadingLine } from '../components/LoadingLine';
import styles from './Verify.module.css';

type Status = 'verifying' | 'success' | 'failed';

export function Verify() {
  const [params] = useSearchParams();
  const navigate = useNavigate();
  const { signIn } = useAuth();
  const [status, setStatus] = useState<Status>('verifying');
  const [message, setMessage] = useState<string | null>(null);

  useEffect(() => {
    const token = params.get('token');
    if (!token) {
      setStatus('failed');
      setMessage('Missing token.');
      return;
    }

    verifyToken(token)
      .then((res) => {
        signIn(res.token, res.user);
        setStatus('success');
        setTimeout(() => navigate('/', { replace: true }), 600);
      })
      .catch((e) => {
        setStatus('failed');
        setMessage(e instanceof ApiError ? e.message : 'Verification failed.');
      });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  if (status === 'verifying') return <LoadingLine label="verifying credential" />;

  if (status === 'success') {
    return (
      <div className={styles.wrap}>
        <div className={[styles.stamp, styles.ok].join(' ')}>CLEARED</div>
        <p className={styles.msg}>Returning to schedule…</p>
      </div>
    );
  }

  return (
    <div className={styles.wrap}>
      <div className={[styles.stamp, styles.fail].join(' ')}>REJECTED</div>
      <p className={styles.msg}>{message ?? 'The link is invalid, expired, or already used.'}</p>
      <Link to="/login" className={styles.back}>← request a new link</Link>
    </div>
  );
}
