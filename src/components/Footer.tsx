import React, { useState } from 'react';
import { MapPin, Mail, Globe, ShieldCheck, X } from 'lucide-react';
import { ScreenType } from '../types';
import { BrandLogo } from './BrandLogo';
import { useFoundationData } from '../context/FoundationDataContext';

interface FooterProps {
  onNavigate: (screen: ScreenType) => void;
  onOpenDonate: () => void;
}

export const Footer: React.FC<FooterProps> = ({ onNavigate, onOpenDonate }) => {
  const [showPrivacyModal, setShowPrivacyModal] = useState(false);
  const { settings } = useFoundationData();

  const officeAddress = settings?.officeAddress || '25 Ediba Rd, Calabar, Cross River State, Nigeria';
  const contactEmail = settings?.contactEmail || 'admin@karlpeacelegacy.org';
  const regNumber = settings?.registrationNumber || '9622998';

  return (
    <>
      <footer className="w-full bg-[#f5f3ef] border-t border-[#E8E4DA] pt-12 pb-24 lg:pb-12 px-4 sm:px-6 lg:px-8 mt-auto">
        <div className="max-w-7xl mx-auto flex flex-col gap-8">
          {/* Top section */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {/* Col 1: Brand & Mission */}
            <div className="flex flex-col gap-3 lg:col-span-2">
              <div className="flex items-center gap-2.5">
                <BrandLogo size="md" />
              </div>
              <p className="text-sm text-[#4B485A] leading-relaxed max-w-lg">
                Empowering young people in Nigeria through transformative education, mentorship, scholarships, and public health initiatives. Guided by a steadfast vision of opportunity, transparency, and grassroots excellence.
              </p>

              {/* Legal Non-profit Status Badge */}
              <div className="inline-flex items-center gap-2 py-1 px-2.5 rounded-lg bg-white border border-[#E8E4DA] text-xs text-[#1E1B4B] w-fit shadow-2xs">
                <ShieldCheck size={15} className="text-[#D97706]" strokeWidth={2} />
                <span>Registered Non-Profit Organization in Nigeria • Reg No: <strong>{regNumber}</strong></span>
              </div>

              <div className="pt-1 flex flex-col gap-2 text-sm text-[#4B485A]">
                <div className="flex items-start gap-2">
                  <MapPin size={18} className="text-[#D97706] shrink-0 mt-0.5" strokeWidth={1.75} />
                  <span>Registered &amp; Physical Address: <strong>{officeAddress}</strong></span>
                </div>
                <div className="flex items-center gap-2">
                  <Mail size={18} className="text-[#D97706] shrink-0" strokeWidth={1.75} />
                  <a href={`mailto:${contactEmail}`} className="hover:text-[#1E1B4B] hover:underline font-medium">
                    {contactEmail}
                  </a>
                </div>
                <div className="flex items-center gap-2 text-xs text-[#6E6B7E]">
                  <Globe size={16} className="text-emerald-600 shrink-0" strokeWidth={1.75} />
                  <span>Official Website: <strong>karlpeacelegacy.org</strong> (Primary Domain)</span>
                </div>
              </div>
            </div>

            {/* Col 2: Navigation Links */}
            <div className="flex flex-col gap-3">
              <h4 className="font-serif text-sm font-bold text-[#1E1B4B] uppercase tracking-wider">
                Explore
              </h4>
              <div className="grid grid-cols-2 gap-2 text-sm">
                <button
                  onClick={() => onNavigate('about-us')}
                  className="text-left text-[#4B485A] hover:text-[#1E1B4B] py-1 transition-colors"
                >
                  About Us
                </button>
                <button
                  onClick={() => onNavigate('our-programs')}
                  className="text-left text-[#4B485A] hover:text-[#1E1B4B] py-1 transition-colors"
                >
                  Our Programs
                </button>
                <button
                  onClick={() => onNavigate('scholarships')}
                  className="text-left text-[#4B485A] hover:text-[#1E1B4B] py-1 transition-colors"
                >
                  Scholarships
                </button>
                <button
                  onClick={() => onNavigate('impact-and-gallery')}
                  className="text-left text-[#4B485A] hover:text-[#1E1B4B] py-1 transition-colors"
                >
                  Impact & Gallery
                </button>
                <button
                  onClick={() => onNavigate('news-and-updates')}
                  className="text-left text-[#4B485A] hover:text-[#1E1B4B] py-1 transition-colors"
                >
                  News & Updates
                </button>
                <button
                  onClick={() => onNavigate('contact-us')}
                  className="text-left text-[#4B485A] hover:text-[#1E1B4B] py-1 transition-colors"
                >
                  Contact Us
                </button>
              </div>
            </div>

            {/* Col 3: Get Involved & Social */}
            <div className="flex flex-col gap-3">
              <h4 className="font-serif text-sm font-bold text-[#1E1B4B] uppercase tracking-wider">
                Get Involved
              </h4>
              <p className="text-xs text-[#6E6B7E]">
                Support verified Nigerian scholars or volunteer your expertise as an executive mentor.
              </p>
              <div className="flex flex-col gap-2">
                <button
                  onClick={onOpenDonate}
                  className="h-10 px-4 rounded-lg bg-[#D97706] hover:bg-[#F59E0B] text-white text-xs font-bold transition-all shadow-xs text-center"
                >
                  Support Scholarship Fund
                </button>
                <button
                  onClick={() => onNavigate('get-involved')}
                  className="h-10 px-4 rounded-lg bg-[#1E1B4B] hover:bg-[#312E81] text-white text-xs font-bold transition-all text-center"
                >
                  Institutional Partnerships
                </button>
              </div>

              {/* Social Channels */}
              <div className="flex items-center gap-2 pt-2">
                {/* Facebook */}
                <a href="https://facebook.com/karlpeacelegacy" target="_blank" rel="noreferrer" aria-label="Facebook"
                  className="w-9 h-9 flex items-center justify-center rounded-lg bg-white border border-[#E8E4DA] text-[#1E1B4B] hover:bg-[#1877F2] hover:text-white hover:border-[#1877F2] transition-colors">
                  <svg className="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                  </svg>
                </a>
                {/* Instagram */}
                <a href="https://instagram.com/karlpeacelegacy" target="_blank" rel="noreferrer" aria-label="Instagram"
                  className="w-9 h-9 flex items-center justify-center rounded-lg bg-white border border-[#E8E4DA] text-[#1E1B4B] hover:bg-[#E4405F] hover:text-white hover:border-[#E4405F] transition-colors">
                  <svg className="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                  </svg>
                </a>
                {/* X (Twitter) */}
                <a href="https://x.com/karlpeacelegacy" target="_blank" rel="noreferrer" aria-label="X / Twitter"
                  className="w-9 h-9 flex items-center justify-center rounded-lg bg-white border border-[#E8E4DA] text-[#1E1B4B] hover:bg-black hover:text-white hover:border-black transition-colors">
                  <svg className="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                  </svg>
                </a>
              </div>
            </div>
          </div>

          {/* Bottom disclaimer & copyright */}
          <div className="pt-6 border-t border-[#E8E4DA] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs text-[#6E6B7E]">
            <p>
              <strong>karlpeacelegacy.org</strong> is the official website and primary domain of <strong>Karl Peace Legacy Foundation</strong>, a registered non-profit organization in Nigeria (Registration No. {regNumber}). Registered address: {officeAddress}.
            </p>
            <div className="flex items-center gap-3 shrink-0">
              <span>© {new Date().getFullYear()} Karl Peace Legacy Foundation. All rights reserved.</span>
              <span>•</span>
              <button
                onClick={() => setShowPrivacyModal(true)}
                className="underline hover:text-[#1E1B4B]"
              >
                Privacy Policy
              </button>
            </div>
          </div>
        </div>
      </footer>

      {/* Privacy Policy Modal */}
      {showPrivacyModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-[#161338]/70 backdrop-blur-sm p-4">
          <div className="w-full max-w-lg bg-white rounded-2xl p-6 shadow-2xl border border-[#E8E4DA] flex flex-col gap-4 max-h-[85vh] overflow-y-auto">
            <div className="flex items-center justify-between border-b border-[#E8E4DA] pb-3">
              <h3 className="font-serif font-bold text-lg text-[#1E1B4B]">
                Privacy & Data Protection Policy
              </h3>
              <button
                onClick={() => setShowPrivacyModal(false)}
                className="w-8 h-8 rounded-full bg-[#efeeea] hover:bg-[#eae8e4] flex items-center justify-center text-[#6E6B7E]"
              >
                <X size={18} strokeWidth={2} />
              </button>
            </div>

            <div className="text-sm text-[#4B485A] space-y-3 leading-relaxed">
              <p>
                <strong>Karl Peace Legacy Foundation</strong> is committed to safeguarding the privacy and personal data of Nigerian scholarship applicants, student beneficiaries, mentors, donors, and site visitors in accordance with applicable Nigerian Data Protection Regulation (NDPR) standards.
              </p>
              <h4 className="font-bold text-[#1E1B4B]">1. Information We Collect</h4>
              <p>
                We collect information provided directly when students register for scholarship notifications or complete grant eligibility questionnaires (including student name, academic institution, matriculation details, email, and program of study).
              </p>
              <h4 className="font-bold text-[#1E1B4B]">2. Fiduciary Use of Data</h4>
              <p>
                Information submitted is used solely for scholarship vetting, academic verification with tertiary institutions, mentorship cohort pairings, and official foundation dispatches. We do not sell, rent, or lease personal records to third parties.
              </p>
              <h4 className="font-bold text-[#1E1B4B]">3. Verification Safeguards</h4>
              <p>
                All student verification is handled directly by the Foundation Secretariat via encrypted protocols and verified university registrars.
              </p>
              <h4 className="font-bold text-[#1E1B4B]">4. Contact</h4>
              <p>
                For data inquiries or corrections, contact our Data Protection Office at <strong>contact@karlpeacelegacy.org</strong>.
              </p>
            </div>

            <button
              onClick={() => setShowPrivacyModal(false)}
              className="w-full h-10 rounded-lg bg-[#1E1B4B] text-white text-sm font-semibold hover:bg-[#312E81] transition-colors mt-2"
            >
              Close Policy
            </button>
          </div>
        </div>
      )}
    </>
  );
};
