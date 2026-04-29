import { Link } from 'react-router-dom';
import { Annotation } from '../components/Annotation';
import { DimensionLine } from '../components/DimensionLine';
import styles from './NotFound.module.css';

export function NotFound() {
  return (
    <div className={styles.wrap}>
      <DimensionLine label="—" />
      <Annotation>NO SUCH SECTION</Annotation>
      <div className={styles.empty} />
      <Link to="/" className={styles.back}>Return to schedule →</Link>
    </div>
  );
}
