import { Component, type ReactNode } from 'react';
import styles from './ErrorBoundary.module.css';

interface State {
  error: Error | null;
}

export class ErrorBoundary extends Component<{ children: ReactNode }, State> {
  state: State = { error: null };

  static getDerivedStateFromError(error: Error): State {
    return { error };
  }

  componentDidCatch(error: Error, info: React.ErrorInfo) {
    if (import.meta.env.DEV) {
      console.error('[ErrorBoundary]', error, info);
    }
  }

  render() {
    if (!this.state.error) return this.props.children;

    return (
      <div className={styles.wrap}>
        <div className={styles.stamp}>FAULT</div>
        <p className={styles.msg}>{this.state.error.message || 'Unexpected error.'}</p>
        <button
          type="button"
          className={styles.reload}
          onClick={() => window.location.reload()}
        >
          reload
        </button>
      </div>
    );
  }
}
