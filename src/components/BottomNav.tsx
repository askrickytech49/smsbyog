import React from 'react';
import { motion } from 'motion/react';
import { ScreenType } from '../types';
import { Home, GraduationCap, Medal, Images, Heart } from 'lucide-react';

interface BottomNavProps {
  currentScreen: ScreenType;
  onNavigate: (screen: ScreenType) => void;
}

export const BottomNav: React.FC<BottomNavProps> = ({ currentScreen, onNavigate }) => {
  const tabs: { id: ScreenType; label: string; Icon: React.ComponentType<any>; activeFor: ScreenType[] }[] = [
    { id: 'home',             label: 'Home',     Icon: Home,          activeFor: ['home'] },
    { id: 'our-programs',     label: 'Programs', Icon: GraduationCap, activeFor: ['our-programs'] },
    { id: 'scholarships',     label: 'Grants',   Icon: Medal,         activeFor: ['scholarships', 'apply'] },
    { id: 'impact-and-gallery', label: 'Impact', Icon: Images,        activeFor: ['impact-and-gallery'] },
    { id: 'get-involved',     label: 'Support',  Icon: Heart,         activeFor: ['get-involved', 'contact-us'] },
  ];

  return (
    <nav className="fixed bottom-0 left-0 right-0 z-40 pb-safe liquid-glass-nav border-t border-white/70 shadow-[0_-8px_28px_rgba(30,27,75,0.08)] lg:hidden">
      <div className="flex justify-around items-center h-16 px-2 sm:px-6 max-w-md sm:max-w-xl mx-auto">
        {tabs.map((tab) => {
          const isActive = tab.activeFor.includes(currentScreen);
          return (
            <button
              key={tab.id}
              onClick={() => onNavigate(tab.id)}
              className="relative flex flex-col items-center justify-center flex-1 h-13 transition-all active:scale-95"
            >
              {isActive && (
                <motion.div
                  layoutId="active-mobile-tab-pill"
                  className="absolute inset-1 rounded-2xl liquid-glass-pill-active -z-10"
                  transition={{ type: 'spring', damping: 24, stiffness: 320 }}
                />
              )}
              <tab.Icon
                size={22}
                strokeWidth={isActive ? 2.2 : 1.75}
                className={`transition-transform ${isActive ? 'scale-110 text-[#D97706]' : 'text-[#6E6B7E]'}`}
              />
              <span className={`text-[10.5px] sm:text-[11.5px] mt-0.5 tracking-tight ${isActive ? 'font-bold text-[#1E1B4B]' : 'font-medium text-[#6E6B7E]'}`}>
                {tab.label}
              </span>
            </button>
          );
        })}
      </div>
    </nav>
  );
};
