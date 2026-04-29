import { useState, type FormEvent } from 'react';
import { requestLink } from '../api/auth';
import { ApiError } from '../api/client';
import { Annotation } from '../components/Annotation';
import { TechCallout } from '../components/TechCallout';
import { DimensionLine } from '../components/DimensionLine';
import styles from './Login.module.css';

type Status = 'idle' | 'loading' | 'sent' | 'error';

export function Login() {
  const [email, setEmail] = useState('');
  const [status, setStatus] = useState<Status>('idle');
  const [error, setError] = useState<string | null>(null);

  async function onSubmit(e: FormEvent) {
    e.preventDefault();
    if (!email) return;
    setStatus('loading');
    setError(null);
    try {
      await requestLink(email.trim());
      setStatus('sent');
    } catch (e) {
      setStatus('error');
      setError(e instanceof ApiError ? e.message : 'Could not request link.');
    }
  }

  return (
    <section className={styles.page}>
      <header className={styles.header}>
        <div className={styles.section}>§ A · paddock entry</div>
        <h1 className={styles.title}>Request access</h1>
        <DimensionLine label="passwordless · single-use · 15 min" />
      </header>

      <div className={styles.grid}>
        {status !== 'sent' ? (
          <form className={styles.form} onSubmit={onSubmit}>
            <label className={styles.label}>
              <span className={styles.labelText}>email</span>
              <input
                type="email"
                inputMode="email"
                autoComplete="email"
                required
                placeholder="driver@box-box.app"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                disabled={status === 'loading'}
              />
            </label>

            <button
              type="submit"
              className={styles.cta}
              disabled={status === 'loading' || !email}
            >
              {status === 'loading' ? 'dispatching…' : 'request access →'}
            </button>

            {error && <div className={styles.error}>{error}</div>}
          </form>
        ) : (
          <div className={styles.dispatched}>
            <div className={styles.stamp}>CREDENTIAL DISPATCHED</div>
            <p>Check <code>{email}</code> for an access link. It expires in 15 minutes.</p>
            <p className={styles.fineprint}>
              Didn't get it? Check spam, or wait a moment and try again.
            </p>
          </div>
        )}

        <TechCallout title="Note —— how it works">
          We send a one-time access link by email. No password to remember;
          no long-lived session you forgot about. Click the link, you're in.
          The token is valid for 15 minutes and can only be used once.
        </TechCallout>
      </div>

      <div className={styles.context}>
        <Annotation>FIA-credentialing · single-issue token</Annotation>
      </div>
    </section>
  );
}
