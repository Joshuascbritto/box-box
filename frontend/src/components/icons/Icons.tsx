/*
 * The full icon vocabulary for box-box. Total: 6 icons.
 * All hand-drawn 1px stroke, no fills, currentColor.
 */

interface IconProps extends React.SVGProps<SVGSVGElement> {
  size?: number;
}

const base = (size: number): React.SVGProps<SVGSVGElement> => ({
  width: size,
  height: size,
  viewBox: '0 0 16 16',
  fill: 'none',
  stroke: 'currentColor',
  strokeWidth: 1,
  strokeLinecap: 'round',
  strokeLinejoin: 'round',
});

export function ChevronRight({ size = 14, ...rest }: IconProps) {
  return (
    <svg {...base(size)} {...rest}>
      <path d="M6 3l5 5-5 5" />
    </svg>
  );
}

export function ChevronDown({ size = 14, ...rest }: IconProps) {
  return (
    <svg {...base(size)} {...rest}>
      <path d="M3 6l5 5 5-5" />
    </svg>
  );
}

export function X({ size = 14, ...rest }: IconProps) {
  return (
    <svg {...base(size)} {...rest}>
      <path d="M3.5 3.5l9 9M12.5 3.5l-9 9" />
    </svg>
  );
}

export function Check({ size = 14, ...rest }: IconProps) {
  return (
    <svg {...base(size)} {...rest}>
      <path d="M3 8.5l3 3 7-7" />
    </svg>
  );
}

export function ArrowUpDown({ size = 14, ...rest }: IconProps) {
  return (
    <svg {...base(size)} {...rest}>
      <path d="M5 4v8M3 6l2-2 2 2M11 12V4M9 10l2 2 2-2" />
    </svg>
  );
}

export function ExternalLink({ size = 14, ...rest }: IconProps) {
  return (
    <svg {...base(size)} {...rest}>
      <path d="M9 3h4v4M13 3l-7 7M11 8.5V13H3V5h4.5" />
    </svg>
  );
}
