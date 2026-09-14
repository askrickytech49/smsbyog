import React from 'react';
import { X, Home, Info, GraduationCap, Medal, Images, Newspaper, Mail } from 'lucide-react';
import { ScreenType } from '../types';
import { BrandLogo } from './BrandLogo';

interface MobileDrawerProps {
  isOpen: boolean;
  currentScreen: ScreenType;
  onClose: () => void;
  onNavigate: (screen: ScreenType) => void;
  onOpenDonate: () => void;
}

export const MobileDrawer: React.FC<MobileDrawerProps> = ({
  isOpen, currentScreen, onClose, onNavigate, onOpenDonate,
}) => {
  const navItems: { id: ScreenType; label: string; Icon: React.ComponentType<any> }[] = [
    { id: 'home',               label: 'Home',          Icon: Home },
    { id: 'about-us',           label: 'About Us',      Icon: Info },
    { id: 'our-programs',       label: 'Our Programs',  Icon: GraduationCap },
    { id: 'scholarships',       label: 'Scholarships',  Icon: Medal },
    { id: 'impact-and-gallery', label: 'Impact & Gallery', Icon: Images },
    { id: 'news-and-updates',   label: 'News & Updates', Icon: Newspaper },
    { id: 'contact-us',         label: 'Contact Us',    Icon: Mail },
  ];

  return (
    <>
      {/* Backdrop */}
      <div
        className={`fixed inset-0 z-50 bg-[#161338]/60 backdrop-blur-sm transition-opacity duration-300 ${
          isOpen ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none'
        }`}
        onClick={onClose}
      />

      {/* Slide-out Drawer */}
      <aside
        className={`fixed top-0 right-0 h-full w-[85%] max-w-sm z-50 bg-[#fbf9f5] shadow-[0_24px_48px_-8px_rgba(22,19,56,0.16)] transform transition-transform duration-300 ease-out flex flex-col pt-safe pb-safe ${
          isOpen ? 'translate-x-0' : 'translate-x-full'
        }`}
      >
        {/* Header */}
        <div className="h-16 px-4 flex items-center justify-between border-b border-[#E8E4DA]">
          <BrandLogo size="header" />
          <button
            aria-label="Close menu"
            className="w-10 h-10 flex items-center justify-center rounded-lg text-[#47464f] hover:bg-[#efeeea] transition-colors"
            onClick={onClose}
          >
            <X size={22} strokeWidth={2} />
          </button>
        </div>

        {/* Nav links */}
        <nav className="flex flex-col px-4 py-4 gap-1 overflow-y-auto flex-1">
          {navItems.map((item) => {
            const isActive = currentScreen === item.id;
            return (
              <button
                key={item.id}
                onClick={() => { onNavigate(item.id); onClose(); }}
                className={`flex items-center gap-3.5 h-12 px-4 rounded-xl text-left transition-all ${
                  isActive ? 'bg-[#FDF1E7] text-[#1E1B4B] font-bold shadow-xs' : 'text-[#4B485A] hover:bg-[#f5f3ef] hover:text-[#1E1B4B]'
                }`}
              >
                <item.Icon
                  size={20}
                  strokeWidth={isActive ? 2.2 : 1.75}
                  className={isActive ? 'text-[#D97706]' : 'text-[#6E6B7E]'}
                />
                <span className="text-sm font-medium">{item.label}</span>
              </button>
            );
          })}
        </nav>

        {/* Bottom CTAs */}
        <div className="p-4 border-t border-[#E8E4DA] flex flex-col gap-2.5">
          <button
            onClick={() => { onNavigate('get-involved'); onClose(); }}
            className="w-full h-11 rounded-lg bg-[#1E1B4B] text-white flex items-center justify-center text-sm font-bold tracking-wide shadow-sm hover:bg-[#312E81] transition-all"
          >
            Get Involved / Partner
          </button>
          <button
            onClick={() => { onClose(); onOpenDonate(); }}
            className="w-full h-11 rounded-lg bg-[#D97706] text-white flex items-center justify-center text-sm font-bold tracking-wide shadow-sm hover:bg-[#F59E0B] transition-all"
          >
            Make a Donation
          </button>
        </div>
      </aside>
    </>
  );
};
