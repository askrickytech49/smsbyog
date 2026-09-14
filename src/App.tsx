import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'motion/react';
import { ScreenType } from './types';
import { Header } from './components/Header';
import { Footer } from './components/Footer';
import { MobileDrawer } from './components/MobileDrawer';
import { BottomNav } from './components/BottomNav';
import { LiquidGlassDock } from './components/LiquidGlassDock';
import { NotificationModal } from './components/NotificationModal';
import { DonateModal } from './components/DonateModal';
import { HomeScreen } from './screens/HomeScreen';
import { AboutScreen } from './screens/AboutScreen';
import { ProgramsScreen } from './screens/ProgramsScreen';
import { ScholarshipsScreen } from './screens/ScholarshipsScreen';
import { ImpactGalleryScreen } from './screens/ImpactGalleryScreen';
import { NewsScreen } from './screens/NewsScreen';
import { ContactScreen } from './screens/ContactScreen';
import { AdminScreen } from './screens/AdminScreen';
import { ApplyScreen } from './screens/ApplyScreen';

export function App() {
  const valid: ScreenType[] = [
    'home', 'about-us', 'our-programs', 'scholarships',
    'impact-and-gallery', 'news-and-updates', 'contact-us',
    'get-involved', 'apply', 'admin',
  ];

  // Initialize from clean URL path — e.g. /admin → 'admin', / → 'home'
  const [currentScreen, setCurrentScreen] = useState<ScreenType>(() => {
    const path = window.location.pathname.replace(/^\//, '').replace(/\/$/, '') as ScreenType;
    return valid.includes(path) ? path : 'home';
  });
  const [isDrawerOpen, setIsDrawerOpen] = useState<boolean>(false);
  const [isNotifyModalOpen, setIsNotifyModalOpen] = useState<boolean>(false);
  const [isDonateModalOpen, setIsDonateModalOpen] = useState<boolean>(false);
  const [selectedArticleId, setSelectedArticleId] = useState<string | null>(null);

  // Navigate using clean URLs — no # needed
  const handleNavigate = (screen: ScreenType) => {
    setCurrentScreen(screen);
    const path = screen === 'home' ? '/' : `/${screen}`;
    window.history.pushState({ screen }, '', path);
    setIsDrawerOpen(false);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleSelectArticleFromHome = (articleId: string) => {
    setSelectedArticleId(articleId);
    setCurrentScreen('news-and-updates');
    window.history.pushState({ screen: 'news-and-updates' }, '', '/news-and-updates');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  // Handle browser back/forward buttons
  useEffect(() => {
    const onPopState = (e: PopStateEvent) => {
      const screen = (e.state?.screen as ScreenType) || (() => {
        const path = window.location.pathname.replace(/^\//, '').replace(/\/$/, '') as ScreenType;
        return valid.includes(path) ? path : 'home';
      })();
      setCurrentScreen(screen);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    };
    window.addEventListener('popstate', onPopState);
    return () => window.removeEventListener('popstate', onPopState);
  }, []);

  // Close modals on escape key
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Escape') {
        setIsDrawerOpen(false);
        setIsNotifyModalOpen(false);
        setIsDonateModalOpen(false);
      }
    };
    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, []);

  return (
    <div className="min-h-screen bg-[#fbf9f5] ambient-bg-glow text-[#1b1c1a] font-sans flex flex-col selection:bg-[#FEF3C7] selection:text-[#904d00] overflow-x-hidden w-full">
      {/* Top Header */}
      <Header
        currentScreen={currentScreen}
        onNavigate={handleNavigate}
        onOpenDrawer={() => setIsDrawerOpen(true)}
        onOpenDonate={() => setIsDonateModalOpen(true)}
      />

      {/* Main Content Area with fluid view transitions */}
      <main className="flex-1 pt-16 flex flex-col relative">
        <AnimatePresence mode="wait">
          <motion.div
            key={currentScreen}
            initial={{ opacity: 0, y: 8 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -8 }}
            transition={{ duration: 0.24, ease: 'easeOut' }}
            className="flex-1 flex flex-col w-full"
          >
            {currentScreen === 'home' && (
              <HomeScreen
                onNavigate={handleNavigate}
                onOpenNotifyModal={() => setIsNotifyModalOpen(true)}
                onOpenDonate={() => setIsDonateModalOpen(true)}
                onSelectArticle={handleSelectArticleFromHome}
              />
            )}

            {currentScreen === 'about-us' && (
              <AboutScreen
                onNavigate={handleNavigate}
                onOpenDonate={() => setIsDonateModalOpen(true)}
              />
            )}

            {currentScreen === 'our-programs' && (
              <ProgramsScreen
                onNavigate={handleNavigate}
                onOpenNotifyModal={() => setIsNotifyModalOpen(true)}
              />
            )}

            {currentScreen === 'scholarships' && (
              <ScholarshipsScreen
                onOpenNotifyModal={() => setIsNotifyModalOpen(true)}
              />
            )}

            {currentScreen === 'impact-and-gallery' && (
              <ImpactGalleryScreen />
            )}

            {currentScreen === 'news-and-updates' && (
              <NewsScreen
                initialArticleId={selectedArticleId}
                onClearInitialArticle={() => setSelectedArticleId(null)}
              />
            )}

            {(currentScreen === 'contact-us' || currentScreen === 'get-involved') && (
              <ContactScreen />
            )}

            {currentScreen === 'admin' && (
              <AdminScreen onNavigate={handleNavigate} />
            )}

            {currentScreen === 'apply' && (
              <ApplyScreen onNavigate={handleNavigate} />
            )}
          </motion.div>
        </AnimatePresence>
      </main>

      {/* Footer */}
      <Footer
        onNavigate={handleNavigate}
        onOpenDonate={() => setIsDonateModalOpen(true)}
      />

      {/* Mobile Drawer */}
      <MobileDrawer
        isOpen={isDrawerOpen}
        currentScreen={currentScreen}
        onClose={() => setIsDrawerOpen(false)}
        onNavigate={handleNavigate}
        onOpenDonate={() => setIsDonateModalOpen(true)}
      />

      {/* Bottom Navigation for Mobile */}
      <BottomNav
        currentScreen={currentScreen}
        onNavigate={handleNavigate}
      />

      {/* Floating Apple-Inspired Liquid Glass Control Dock */}
      {currentScreen !== 'admin' && currentScreen !== 'apply' && (
        <LiquidGlassDock
          currentScreen={currentScreen}
          onNavigate={handleNavigate}
          onOpenNotifyModal={() => setIsNotifyModalOpen(true)}
          onOpenDonate={() => setIsDonateModalOpen(true)}
        />
      )}

      {/* Scholarship Alert & Notification Modal */}
      <NotificationModal
        isOpen={isNotifyModalOpen}
        onClose={() => setIsNotifyModalOpen(false)}
      />

      {/* Support & Donation Modal */}
      <DonateModal
        isOpen={isDonateModalOpen}
        onClose={() => setIsDonateModalOpen(false)}
      />
    </div>
  );
}

export default App;
