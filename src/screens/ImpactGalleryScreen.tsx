import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'motion/react';
import { useFoundationData } from '../context/FoundationDataContext';
import { GalleryPhoto } from '../types';
import { Icon } from '../components/Icon';

export const ImpactGalleryScreen: React.FC = () => {
  const { gallery, testimonials } = useFoundationData();
  const [activeCategory, setActiveCategory] = useState<string>('all');
  const [selectedPhotoIndex, setSelectedPhotoIndex] = useState<number | null>(null);

  const categories = [
    { id: 'all', label: 'All Photos', icon: 'auto_awesome' },
    { id: 'scholarships', label: 'Higher Education', icon: 'school' },
    { id: 'mentorship', label: 'Mentorship', icon: 'groups' },
    { id: 'health', label: 'Public Health', icon: 'health_and_safety' },
  ];

  const filteredPhotos =
    activeCategory === 'all'
      ? gallery
      : gallery.filter((p) => p.category === activeCategory);

  const activeModalPhoto =
    selectedPhotoIndex !== null ? filteredPhotos[selectedPhotoIndex] : null;

  // Keyboard navigation for lightbox
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (selectedPhotoIndex === null) return;
      if (e.key === 'ArrowRight') {
        setSelectedPhotoIndex((prev) =>
          prev !== null ? (prev + 1) % filteredPhotos.length : 0
        );
      } else if (e.key === 'ArrowLeft') {
        setSelectedPhotoIndex((prev) =>
          prev !== null ? (prev - 1 + filteredPhotos.length) % filteredPhotos.length : 0
        );
      } else if (e.key === 'Escape') {
        setSelectedPhotoIndex(null);
      }
    };
    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [selectedPhotoIndex, filteredPhotos.length]);

  return (
    <div className="flex flex-col w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-16 gap-12 sm:gap-16">
      {/* Header */}
      <div className="flex flex-col gap-4 max-w-3xl">
        <div className="inline-flex items-center gap-2 py-1 px-3.5 rounded-full liquid-glass-pill-active text-[#D97706] text-xs uppercase font-bold self-start border border-amber-200/60 shadow-xs">
          <Icon name="photo_library" size={15} className="text-[#D97706]" />
          <span>Photographic Records &amp; Voices</span>
        </div>
        <h1 className="font-serif text-3xl sm:text-5xl font-bold text-[#1E1B4B] leading-tight">
          Visualizing Change Across Nigerian Communities
        </h1>
        <p className="text-base sm:text-lg text-[#4B485A] leading-relaxed">
          Explore verified moments of scholarship matriculation, executive mentorship, and community health interventions from the Karl Peace Legacy Foundation.
        </p>
      </div>

      {/* Impact Numbers Section with Liquid Glass Styling */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
        <div className="p-6 rounded-3xl liquid-glass-card card-hover-lift border border-white/80 shadow-xs flex flex-col gap-1.5">
          <span className="font-serif text-3xl sm:text-4xl font-bold text-[#1E1B4B]">100%</span>
          <span className="text-xs uppercase tracking-wider font-bold text-[#D97706]">
            Direct Allocation
          </span>
          <span className="text-xs text-[#6E6B7E] leading-relaxed">
            Direct student tuition payments without administrative deductions.
          </span>
        </div>

        <div className="p-6 rounded-3xl liquid-glass-card card-hover-lift border border-white/80 shadow-xs flex flex-col gap-1.5">
          <span className="font-serif text-3xl sm:text-4xl font-bold text-[#1E1B4B]">Tertiary</span>
          <span className="text-xs uppercase tracking-wider font-bold text-[#1E1B4B]">
            Federal &amp; State
          </span>
          <span className="text-xs text-[#6E6B7E] leading-relaxed">
            Targeting accredited Nigerian colleges, universities, and polytechnics.
          </span>
        </div>

        <div className="p-6 rounded-3xl liquid-glass-card card-hover-lift border border-white/80 shadow-xs flex flex-col gap-1.5">
          <span className="font-serif text-3xl sm:text-4xl font-bold text-[#0D9488]">1-on-1</span>
          <span className="text-xs uppercase tracking-wider font-bold text-[#0D9488]">
            Mentorship
          </span>
          <span className="text-xs text-[#6E6B7E] leading-relaxed">
            Paired with established executives across industry and academia.
          </span>
        </div>

        <div className="p-6 rounded-3xl liquid-glass-card card-hover-lift border border-white/80 shadow-xs flex flex-col gap-1.5">
          <span className="font-serif text-3xl sm:text-4xl font-bold text-[#904d00]">Abuja &amp; Lagos</span>
          <span className="text-xs uppercase tracking-wider font-bold text-[#904d00]">
            Active Hubs
          </span>
          <span className="text-xs text-[#6E6B7E] leading-relaxed">
            Strategic secretariat and outreach hubs serving Nigerian youth.
          </span>
        </div>
      </div>

      {/* Category Filter Dock with Spring Capsule */}
      <div className="flex items-center gap-1.5 p-1.5 rounded-2xl liquid-glass-dock overflow-x-auto scrollbar-none border border-white/80 shadow-sm self-start">
        {categories.map((cat) => {
          const isActive = activeCategory === cat.id;
          return (
            <button
              key={cat.id}
              onClick={() => {
                setActiveCategory(cat.id);
                setSelectedPhotoIndex(null);
              }}
              className={`relative flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all select-none ${
                isActive
                  ? 'text-[#1E1B4B] font-bold shadow-xs'
                  : 'text-[#4B485A] hover:text-[#1E1B4B] hover:bg-white/40'
              }`}
            >
              {isActive && (
                <motion.div
                  layoutId="active-gallery-tab-pill"
                  className="absolute inset-0 rounded-xl liquid-glass-pill-active -z-10"
                  transition={{ type: 'spring', damping: 26, stiffness: 350 }}
                />
              )}
              <Icon name={cat.icon} size={17} className={isActive ? 'text-[#D97706]' : 'text-[#6E6B7E]'} />
              <span>{cat.label}</span>
            </button>
          );
        })}
      </div>

      {/* Gallery Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
        {filteredPhotos.map((item, index) => (
          <motion.div
            key={item.id}
            initial={{ opacity: 0, y: 12 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.25, delay: index * 0.04 }}
            onClick={() => setSelectedPhotoIndex(index)}
            className="group liquid-glass-card card-hover-lift rounded-3xl overflow-hidden border border-white/80 shadow-sm cursor-pointer"
          >
            <div className="relative aspect-[16/10] overflow-hidden bg-[#161338]">
              <img
                src={item.image}
                alt={item.title}
                className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                referrerPolicy="no-referrer"
              />
              <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-5">
                <span className="text-white text-xs font-semibold flex items-center gap-1.5 px-3 py-1.5 rounded-full liquid-glass-pill-active text-[#1E1B4B]">
                  <Icon name="zoom_in" size={16} className="text-[#D97706]" />
                  <span>Inspect Full Resolution</span>
                </span>
              </div>
            </div>
            <div className="p-5 sm:p-6 flex flex-col gap-2">
              <div className="flex items-center justify-between text-xs text-[#6E6B7E]">
                <span className="flex items-center gap-1 font-medium">
                  <Icon name="location_on" size={15} className="text-[#D97706]" />
                  {item.location}
                </span>
                <span className="font-mono text-[11px]">{item.date}</span>
              </div>
              <h3 className="font-serif text-lg font-bold text-[#1E1B4B] group-hover:text-[#D97706] transition-colors">
                {item.title}
              </h3>
              <p className="text-xs sm:text-sm text-[#4B485A] leading-relaxed">{item.caption}</p>
            </div>
          </motion.div>
        ))}
      </div>

      {/* Scholar Voices & Testimonials */}
      <section className="flex flex-col gap-6 pt-4">
        <div className="flex flex-col gap-1 max-w-2xl">
          <span className="text-xs uppercase tracking-wider text-[#D97706] font-bold">
            Voices of Impact
          </span>
          <h2 className="font-serif text-2xl sm:text-3xl font-bold text-[#1E1B4B]">
            From Our Scholars &amp; Beneficiaries
          </h2>
          <p className="text-sm text-[#4B485A]">
            Real accounts from Nigerian tertiary students supported through our programs.
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {testimonials.map((t) => (
            <div
              key={t.id}
              className="p-6 rounded-3xl liquid-glass-card card-hover-lift border border-white/80 shadow-xs flex flex-col justify-between gap-4 relative"
            >
              <div className="flex items-center justify-between">
                <Icon name="format_quote" size={32} className="text-[#F59E0B] opacity-70" />
                <span className="px-2.5 py-0.5 rounded-full bg-amber-50 text-[#904d00] text-[10px] uppercase font-bold border border-amber-200/60">
                  Verified Scholar
                </span>
              </div>
              <p className="text-xs sm:text-sm text-[#4B485A] leading-relaxed italic">
                &ldquo;{t.quote}&rdquo;
              </p>
              <div className="pt-3 border-t border-black/5">
                <div className="font-serif font-bold text-[#1E1B4B] text-sm">{t.name}</div>
                <div className="text-xs text-[#6E6B7E]">{t.field}</div>
                <div className="text-[11px] text-[#D97706] font-semibold">{t.institution}</div>
              </div>
            </div>
          ))}
        </div>
      </section>

      {/* High-Resolution Photo Lightbox Modal with Keyboard & Prev/Next Navigation */}
      <AnimatePresence>
        {activeModalPhoto && selectedPhotoIndex !== null && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-50 flex items-center justify-center bg-[#161338]/85 backdrop-blur-md p-4"
            onClick={() => setSelectedPhotoIndex(null)}
          >
            <motion.div
              initial={{ scale: 0.94, opacity: 0, y: 12 }}
              animate={{ scale: 1, opacity: 1, y: 0 }}
              exit={{ scale: 0.94, opacity: 0, y: 12 }}
              transition={{ type: 'spring', damping: 26, stiffness: 320 }}
              className="w-full max-w-4xl liquid-glass-modal rounded-3xl overflow-hidden shadow-2xl border border-white/80 flex flex-col relative"
              onClick={(e) => e.stopPropagation()}
            >
              <div className="relative aspect-[16/10] bg-black">
                <img
                  src={activeModalPhoto.image}
                  alt={activeModalPhoto.title}
                  className="w-full h-full object-contain"
                  referrerPolicy="no-referrer"
                />

                {/* Close Button */}
                <button
                  onClick={() => setSelectedPhotoIndex(null)}
                  className="absolute top-4 right-4 w-10 h-10 rounded-full bg-black/60 hover:bg-black text-white flex items-center justify-center backdrop-blur-md transition-all active:scale-90"
                >
                  <Icon name="close" size={20} />
                </button>

                {/* Prev Button */}
                <button
                  onClick={() =>
                    setSelectedPhotoIndex(
                      (selectedPhotoIndex - 1 + filteredPhotos.length) % filteredPhotos.length
                    )
                  }
                  className="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/60 hover:bg-black text-white flex items-center justify-center backdrop-blur-md transition-all active:scale-90"
                >
                  <Icon name="chevron_left" size={22} />
                </button>

                {/* Next Button */}
                <button
                  onClick={() =>
                    setSelectedPhotoIndex((selectedPhotoIndex + 1) % filteredPhotos.length)
                  }
                  className="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/60 hover:bg-black text-white flex items-center justify-center backdrop-blur-md transition-all active:scale-90"
                >
                  <Icon name="chevron_right" size={22} />
                </button>
              </div>

              {/* Photo Details */}
              <div className="p-6 sm:p-7 flex flex-col gap-2 bg-white/90 backdrop-blur-md">
                <div className="flex items-center justify-between text-xs text-[#6E6B7E]">
                  <span className="flex items-center gap-1 font-bold text-[#1E1B4B]">
                    <Icon name="location_on" size={16} className="text-[#D97706]" />
                    {activeModalPhoto.location}
                  </span>
                  <span className="font-mono text-xs">{activeModalPhoto.date}</span>
                </div>
                <h3 className="font-serif text-xl sm:text-2xl font-bold text-[#1E1B4B]">
                  {activeModalPhoto.title}
                </h3>
                <p className="text-sm text-[#4B485A] leading-relaxed">
                  {activeModalPhoto.caption}
                </p>
                <div className="pt-2 flex items-center justify-between text-xs text-[#6E6B7E] border-t border-black/5 mt-2">
                  <span>Photo {selectedPhotoIndex + 1} of {filteredPhotos.length}</span>
                  <span className="font-medium">Use &larr; &rarr; arrows to browse</span>
                </div>
              </div>
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
};
