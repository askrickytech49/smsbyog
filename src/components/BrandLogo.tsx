import React, { useState } from 'react';
import { FOUNDATION_LOGO } from '../data/foundationData';

interface BrandLogoProps {
  size?: 'sm' | 'md' | 'lg' | 'header';
  showText?: boolean;
  textColor?: string;
  className?: string;
}

export const BrandLogo: React.FC<BrandLogoProps> = ({
  size = 'header',
  showText = true,
  textColor = 'text-[#1E1B4B]',
  className = '',
}) => {
  const [imageFailed, setImageFailed] = useState(false);

  // Sizing variants
  const imgClasses = {
    sm: 'h-8 max-w-[140px]',
    header: 'h-10 sm:h-11 max-w-[160px] sm:max-w-[200px]',
    md: 'h-12 sm:h-14 max-w-[220px]',
    lg: 'h-16 sm:h-20 max-w-[280px]',
  }[size];

  return (
    <div className={`flex items-center gap-2.5 select-none ${className}`}>
      {!imageFailed ? (
        <img
          src={FOUNDATION_LOGO}
          alt="Karl Peace Legacy Foundation"
          className={`${imgClasses} w-auto object-contain transition-transform group-hover:scale-102`}
          referrerPolicy="no-referrer"
          onError={() => setImageFailed(true)}
        />
      ) : (
        /* Fallback crest if external image is unavailable */
        <div className="flex items-center gap-2.5">
          <div className="w-10 h-10 rounded-xl bg-[#1E1B4B] flex items-center justify-center shadow-xs">
            <span className="font-serif text-[#F59E0B] font-bold text-base leading-none">KP</span>
          </div>
          {showText && (
            <div className="flex flex-col">
              <span className={`font-serif text-sm sm:text-base font-bold ${textColor} leading-tight`}>
                Karl Peace Legacy
              </span>
              <span className="text-[10px] sm:text-[11px] tracking-widest text-[#6E6B7E] uppercase font-bold">
                Foundation
              </span>
            </div>
          )}
        </div>
      )}
    </div>
  );
};
