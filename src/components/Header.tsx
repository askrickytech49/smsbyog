import React, { useState } from 'react';
import { motion, AnimatePresence } from 'motion/react';
import { Menu, Heart, User, LogOut, Info, ChevronRight, Shield, X, ArrowRight } from 'lucide-react';
import { ScreenType } from '../types';
import { BrandLogo } from './BrandLogo';
import { useAuth } from '../context/AuthContext';
import { Icon } from './Icon';

interface HeaderProps {
  currentScreen: ScreenType;
  onNavigate: (screen: ScreenType) => void;
  onOpenDrawer: () => void;
  onOpenDonate: () => void;
}

export const Header: React.FC<HeaderProps> = ({
  currentScreen,
  onNavigate,
  onOpenDrawer,
  onOpenDonate,
}) => {
  const [showProfileModal, setShowProfileModal] = useState(false);
  const { user, isAdmin, isSuperAdmin, logout } = useAuth();

  const navItems: { id: ScreenType; label: string }[] = [
    { id: 'home', label: 'Home' },
    { id: 'about-us', label: 'About Us' },
    { id: 'our-programs', label: 'Our Programs' },
    { id: 'scholarships', label: 'Scholarships' },
    { id: 'impact-and-gallery', label: 'Impact & Gallery' },
    { id: 'news-and-updates', label: 'News & Updates' },
    { id: 'contact-us', label: 'Contact' },
  ];

  return (
    <>
      <header className="fixed top-0 w-full z-40 pt-safe liquid-glass-nav transition-all duration-300">
        <div className="max-w-7xl mx-auto h-16 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-2">
          {/* Left section: Hamburger button & Logo */}
          <div className="flex items-center gap-2 sm:gap-4">
            <motion.button
              whileTap={{ scale: 0.92 }}
              aria-label="Open menu"
              className="w-10 h-10 flex items-center justify-center rounded-xl text-[#1E1B4B] hover:bg-white/60 active:scale-95 transition-all lg:hidden"
              onClick={onOpenDrawer}
              type="button"
            >
              <Menu size={24} />            </motion.button>

            <button
              onClick={() => onNavigate('home')}
              className="flex items-center gap-2.5 text-left group min-h-[44px]"
              aria-label="Karl Peace Legacy Foundation Home"
            >
              <BrandLogo size="header" />
            </button>
          </div>

          {/* Center Desktop Navigation: Liquid Glass Segmented Bar */}
          <nav className="hidden lg:flex items-center gap-1 p-1 rounded-full bg-white/45 border border-white/70 shadow-2xs backdrop-blur-md">
            {navItems.map((item) => {
              const isActive = currentScreen === item.id;
              return (
                <button
                  key={item.id}
                  onClick={() => onNavigate(item.id)}
                  className={`relative px-3.5 py-1.5 rounded-full text-xs font-semibold tracking-tight transition-all duration-200 select-none ${
                    isActive
                      ? 'text-[#1E1B4B] font-bold shadow-xs'
                      : 'text-[#4B485A] hover:text-[#1E1B4B] hover:bg-white/40'
                  }`}
                >
                  {isActive && (
                    <motion.div
                      layoutId="active-header-pill"
                      className="absolute inset-0 rounded-full liquid-glass-pill-active -z-10"
                      transition={{ type: 'spring', damping: 26, stiffness: 350 }}
                    />
                  )}
                  {item.label}
                </button>
              );
            })}
          </nav>

          {/* Right Action buttons with Liquid Glass Styling */}
          <div className="flex items-center gap-2 sm:gap-3">
            <motion.button
              whileHover={{ scale: 1.03 }}
              whileTap={{ scale: 0.96 }}
              onClick={onOpenDonate}
              className="liquid-glass-amber-btn glass-refraction inline-flex items-center justify-center h-10 px-4 sm:px-5 rounded-full text-white font-bold text-xs sm:text-sm tracking-wide shadow-md"
            >
              <Heart size={17} className="mr-1.5" />
              <span>Donate</span>
            </motion.button>

            <motion.button
              whileHover={{ scale: 1.05 }}
              whileTap={{ scale: 0.94 }}
              onClick={() => setShowProfileModal(true)}
              aria-label="Scholar & Member Portal"
              title={user ? `Signed in as ${user.email}` : "Official Portal Information"}
              className="relative liquid-glass-dark-btn glass-refraction w-9 h-9 rounded-full flex items-center justify-center text-white shadow-xs overflow-hidden"
            >
              {user?.photoURL ? (
                <img
                  src={user.photoURL}
                  alt={user.displayName || 'Account'}
                  className="w-full h-full object-cover"
                />
              ) : user ? (
                <span className="font-bold text-xs text-[#F59E0B]">
                  {user.displayName?.charAt(0) || user.email?.charAt(0)?.toUpperCase() || 'A'}
                </span>
              ) : (
                <Icon name="person" size={19} />
              )}
              {user && isAdmin && (
                <span className="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-[#10B981] ring-2 ring-white" />
              )}
            </motion.button>
          </div>
        </div>
      </header>

      {/* Profile & Portal Information Modal */}
      <AnimatePresence>
        {showProfileModal && (
          <div className="fixed inset-0 z-50 flex items-center justify-center bg-[#161338]/60 backdrop-blur-md p-4">
            <motion.div
              initial={{ opacity: 0, scale: 0.94, y: 16 }}
              animate={{ opacity: 1, scale: 1, y: 0 }}
              exit={{ opacity: 0, scale: 0.94, y: 12 }}
              transition={{ type: 'spring', damping: 26, stiffness: 320 }}
              className="w-full max-w-md liquid-glass-card rounded-3xl p-6 shadow-2xl flex flex-col gap-4 relative"
            >
              <div className="flex items-center justify-between border-b border-white/60 pb-3">
                <div className="flex items-center gap-2.5">
                  <div className="w-9 h-9 rounded-xl bg-[#1E1B4B] text-[#F59E0B] flex items-center justify-center font-bold shadow-xs">
                    KP
                  </div>
                  <div>
                    <h3 className="font-serif font-bold text-[#1E1B4B] text-base leading-tight">
                      Scholar &amp; Partner Portal
                    </h3>
                    <p className="text-xs text-[#6E6B7E]">Karl Peace Legacy Foundation</p>
                  </div>
                </div>
                <button
                  onClick={() => setShowProfileModal(false)}
                  className="w-8 h-8 rounded-full bg-white/70 hover:bg-white flex items-center justify-center text-[#6E6B7E] transition-colors shadow-2xs"
                >
                  <Icon name="close" size={18} />
                </button>
              </div>

              {/* User Identity Card if logged in */}
              {user ? (
                <div className="p-3.5 rounded-2xl bg-white/80 border border-[#E8E4DA] flex items-center justify-between shadow-2xs">
                  <div className="flex items-center gap-3">
                    {user.photoURL ? (
                      <img
                        src={user.photoURL}
                        alt={user.displayName || 'User'}
                        className="w-10 h-10 rounded-full object-cover border border-[#D97706]"
                      />
                    ) : (
                      <div className="w-10 h-10 rounded-full bg-[#1E1B4B] text-[#F59E0B] font-bold text-sm flex items-center justify-center">
                        {user.displayName?.charAt(0) || user.email?.charAt(0)?.toUpperCase()}
                      </div>
                    )}
                    <div className="flex flex-col">
                      <span className="text-xs font-bold text-[#1E1B4B] leading-tight">
                        {user.displayName || 'Authorized User'}
                      </span>
                      <span className="text-[11px] text-[#6E6B7E] truncate max-w-[190px]">
                        {user.email}
                      </span>
                      <div className="mt-0.5">
                        <span className={`inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold ${
                          isSuperAdmin
                            ? 'bg-purple-100 text-purple-800'
                            : isAdmin
                            ? 'bg-emerald-100 text-emerald-800'
                            : 'bg-gray-100 text-gray-700'
                        }`}>
                          {isSuperAdmin ? 'Super Administrator' : isAdmin ? 'Administrator' : 'Account'}
                        </span>
                      </div>
                    </div>
                  </div>

                  <button
                    onClick={async () => {
                      await logout();
                      setShowProfileModal(false);
                    }}
                    title="Sign out of account"
                    className="p-2 rounded-xl text-red-600 hover:bg-red-50 border border-transparent hover:border-red-200 transition-colors"
                  >
                    <Icon name="logout" size={18} />
                  </button>
                </div>
              ) : null}

              <div className="flex flex-col gap-3 text-sm text-[#4B485A]">
                <div className="p-3.5 rounded-2xl bg-white/65 border border-[#FDE68A] flex items-start gap-2.5 shadow-2xs">
                  <Icon name="info" size={20} className="text-[#D97706] shrink-0 mt-0.5" />
                  <div className="text-xs sm:text-sm">
                    <span className="font-bold text-[#904d00] block mb-0.5">
                      2026/2027 Portal Status
                    </span>
                    The central student applicant portal is currently undergoing scheduled upgrade for the upcoming academic cycle. User login and registration will open with the official scholarship bulletin.
                  </div>
                </div>

                <div className="space-y-2">
                  <h4 className="text-xs font-bold uppercase tracking-wider text-[#1E1B4B]">
                    Quick Portal Services
                  </h4>
                  <div className="grid grid-cols-1 gap-2">
                    <button
                      onClick={() => {
                        setShowProfileModal(false);
                        onNavigate('scholarships');
                      }}
                      className="p-3 rounded-xl bg-white/60 hover:bg-white/90 text-left flex items-center justify-between transition-colors border border-white/60 shadow-2xs"
                    >
                      <span className="font-medium text-[#1E1B2E]">Check Scholarship Eligibility Criteria</span>
                      <Icon name="arrow_forward" size={16} className="text-[#D97706]" />
                    </button>
                    <button
                      onClick={() => {
                        setShowProfileModal(false);
                        onNavigate('contact-us');
                      }}
                      className="p-3 rounded-xl bg-white/60 hover:bg-white/90 text-left flex items-center justify-between transition-colors border border-white/60 shadow-2xs"
                    >
                      <span className="font-medium text-[#1E1B2E]">Contact Secretariat &amp; Verification Team</span>
                      <Icon name="arrow_forward" size={16} className="text-[#D97706]" />
                    </button>
                  </div>
                </div>
              </div>

              <button
                onClick={() => setShowProfileModal(false)}
                className="w-full h-11 rounded-xl liquid-glass-dark-btn text-white text-xs sm:text-sm font-semibold transition-colors mt-2 shadow-xs"
              >
                Return to Website
              </button>
            </motion.div>
          </div>
        )}
      </AnimatePresence>
    </>
  );
};
