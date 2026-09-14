import React, { createContext, useContext, useEffect, useState } from 'react';
import {
  ProgramItem,
  NewsArticle,
  GalleryPhoto,
  TestimonialItem,
  FAQItem,
  TeamMember,
  SiteSettings,
  SubscriberItem,
  InquiryItem
} from '../types';
import {
  FOUNDATION_PROGRAMS,
  FOUNDATION_NEWS,
  FOUNDATION_LEADERS,
  FOUNDATION_GALLERY,
  FOUNDATION_TESTIMONIALS,
  SCHOLARSHIP_FAQS
} from '../data/foundationData';

export const DEFAULT_SITE_SETTINGS: SiteSettings = {
  heroTitle: 'Empowering Nigerian Youth Through Education, Health, and Mentorship',
  heroSubtitle: 'Honoring Dr. Karl E. Peace’s lifelong legacy in public health and biostatistics by providing tertiary scholarships, executive career guidance, and community health interventions across Nigeria.',
  heroBadge: 'Fostering Excellence • Expanding Horizons',
  contactEmail: 'admin@karlpeacelegacy.org',
  contactPhone: '+234 800 000 0000',
  officeAddress: '25 Ediba Rd, Calabar, Cross River State, Nigeria',
  registeredAddress: '25 Ediba Rd, Calabar, Cross River State, Nigeria',
  registrationNumber: '9622998',
  officialDomain: 'karlpeacelegacy.org',
  scholarshipAlertActive: true,
  scholarshipAlertTitle: '2025/2026 Tertiary Scholarship Framework',
  scholarshipAlertText: 'The official evaluation roadmap has been ratified by the board of trustees. Prospective Nigerian undergraduates may review verified eligibility requirements.',
  scholarshipAlertDeadline: 'Opening for Applications',
  scholarshipAlertCycle: '2025/2026 Academic Session',
};

const LOCAL_STORAGE_DATA_KEY = 'karl_peace_foundation_data_v4';
const LOCAL_STORAGE_API_KEY  = 'karl_peace_php_api_url';
const LOCAL_STORAGE_TOKEN_KEY = 'karl_peace_admin_token';

interface FoundationDataContextType {
  settings: SiteSettings;
  programs: ProgramItem[];
  news: NewsArticle[];
  leaders: TeamMember[];
  gallery: GalleryPhoto[];
  testimonials: TestimonialItem[];
  faqs: FAQItem[];
  subscribers: SubscriberItem[];
  inquiries: InquiryItem[];
  isFirestoreConnected: boolean; // Retained for backwards compatibility in UI badges
  isPhpConnected: boolean;
  isLoading: boolean;
  // Mutators
  updateSettings: (newSettings: Partial<SiteSettings>) => Promise<void>;
  saveProgram: (program: ProgramItem) => Promise<void>;
  deleteProgram: (id: string) => Promise<void>;
  saveNewsArticle: (article: NewsArticle) => Promise<void>;
  deleteNewsArticle: (id: string) => Promise<void>;
  saveLeader: (leader: TeamMember) => Promise<void>;
  deleteLeader: (id: string) => Promise<void>;
  saveGalleryPhoto: (photo: GalleryPhoto) => Promise<void>;
  deleteGalleryPhoto: (id: string) => Promise<void>;
  saveFaq: (faq: FAQItem, originalQuestion?: string) => Promise<void>;
  deleteFaq: (question: string) => Promise<void>;
  saveTestimonial: (testimonial: TestimonialItem) => Promise<void>;
  deleteTestimonial: (id: string) => Promise<void>;
  submitSubscriber: (data: { name: string; email: string; institution?: string; course?: string }) => Promise<void>;
  submitInquiry: (data: { name: string; email: string; subject?: string; message: string; role?: string }) => Promise<void>;
  updateInquiryStatus: (id: string, status: 'unread' | 'replied' | 'archived') => Promise<void>;
  deleteInquiry: (id: string) => Promise<void>;
  deleteSubscriber: (id: string) => Promise<void>;
  seedDefaultData: () => Promise<{ success: boolean; message: string }>;
  syncWithPhpBackend: () => Promise<void>;
}

const FoundationDataContext = createContext<FoundationDataContextType | undefined>(undefined);

export const FoundationDataProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [settings, setSettings] = useState<SiteSettings>(DEFAULT_SITE_SETTINGS);
  const [programs, setPrograms] = useState<ProgramItem[]>(FOUNDATION_PROGRAMS);
  const [news, setNews] = useState<NewsArticle[]>(FOUNDATION_NEWS);
  const [leaders, setLeaders] = useState<TeamMember[]>(FOUNDATION_LEADERS);
  const [gallery, setGallery] = useState<GalleryPhoto[]>(FOUNDATION_GALLERY);
  const [testimonials, setTestimonials] = useState<TestimonialItem[]>(FOUNDATION_TESTIMONIALS);
  const [faqs, setFaqs] = useState<FAQItem[]>(SCHOLARSHIP_FAQS);
  const [subscribers, setSubscribers] = useState<SubscriberItem[]>([]);
  const [inquiries, setInquiries] = useState<InquiryItem[]>([]);
  const [isPhpConnected, setIsPhpConnected] = useState<boolean>(true);
  const [isLoading, setIsLoading] = useState<boolean>(true);

  // Helper to get active PHP API base URL
  const getApiUrl = () => {
    return (localStorage.getItem(LOCAL_STORAGE_API_KEY) || '/api').replace(/\/$/, '');
  };

  // Helper to get auth token
  const getToken = () => localStorage.getItem(LOCAL_STORAGE_TOKEN_KEY) || '';

  // Auth headers helper
  const authHeaders = () => ({
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${getToken()}`,
  });

  // Helper to persist state to local storage
  const persistToLocalStorage = (data: {
    settings: SiteSettings;
    programs: ProgramItem[];
    news: NewsArticle[];
    leaders: TeamMember[];
    gallery: GalleryPhoto[];
    testimonials: TestimonialItem[];
    faqs: FAQItem[];
    subscribers: SubscriberItem[];
    inquiries: InquiryItem[];
  }) => {
    try {
      localStorage.setItem(LOCAL_STORAGE_DATA_KEY, JSON.stringify(data));
    } catch (e) {
      console.warn('Could not persist foundation data to localStorage:', e);
    }
  };

  // Push a single section update to the MySQL backend
  const pushToPhpBackend = async (dataPayload: Record<string, unknown>) => {
    const apiUrl = getApiUrl();
    try {
      const res = await fetch(`${apiUrl}/data.php`, {
        method: 'POST',
        headers: authHeaders(),
        body: JSON.stringify(dataPayload),
      });
      if (res.ok) setIsPhpConnected(true);
    } catch (err) {
      console.warn('PHP API push failed (offline?):', err);
    }
  };

  // Delete a single item from the MySQL backend
  const deleteFromPhpBackend = async (section: string, id: string) => {
    const apiUrl = getApiUrl();
    try {
      await fetch(`${apiUrl}/data.php?section=${section}&id=${encodeURIComponent(id)}`, {
        method: 'DELETE',
        headers: authHeaders(),
      });
    } catch (err) {
      console.warn('PHP API delete failed:', err);
    }
  };

  // Initial Data Load
  useEffect(() => {
    const initData = async () => {
      // 1. Try loading from localStorage first for immediate rendering
      let loadedFromLocal = false;
      try {
        const cached = localStorage.getItem(LOCAL_STORAGE_DATA_KEY);
        if (cached) {
          const parsed = JSON.parse(cached);
          if (parsed.settings) {
            setSettings(prev => ({
              ...DEFAULT_SITE_SETTINGS,
              ...parsed.settings,
              // Ensure official updated defaults if old placeholder was stored
              contactEmail: (!parsed.settings.contactEmail || parsed.settings.contactEmail === 'contact@karlpeacelegacy.org') ? 'admin@karlpeacelegacy.org' : parsed.settings.contactEmail,
              officeAddress: (!parsed.settings.officeAddress || parsed.settings.officeAddress.includes('Abuja & Lagos')) ? '25 Ediba Rd, Calabar, Cross River State, Nigeria' : parsed.settings.officeAddress,
              registeredAddress: '25 Ediba Rd, Calabar, Cross River State, Nigeria',
              registrationNumber: '9622998',
              officialDomain: 'karlpeacelegacy.org'
            }));
          }
          if (Array.isArray(parsed.programs)) setPrograms(parsed.programs);
          if (Array.isArray(parsed.news)) setNews(parsed.news);
          if (Array.isArray(parsed.leaders)) setLeaders(parsed.leaders);
          if (Array.isArray(parsed.gallery)) setGallery(parsed.gallery);
          if (Array.isArray(parsed.testimonials)) setTestimonials(parsed.testimonials);
          if (Array.isArray(parsed.faqs)) setFaqs(parsed.faqs);
          if (Array.isArray(parsed.subscribers)) setSubscribers(parsed.subscribers);
          if (Array.isArray(parsed.inquiries)) setInquiries(parsed.inquiries);
          loadedFromLocal = true;
        }
      } catch (e) {
        console.warn('Error reading local cache:', e);
      }

      // 2. Try fetching latest from PHP backend
      try {
        const apiUrl = getApiUrl();
        const res = await fetch(`${apiUrl}/data.php`, { method: 'GET' });
        if (res.ok) {
          const result = await res.json();
          if (result.success && result.data && Object.keys(result.data).length > 0) {
            const d = result.data;
            if (d.settings) setSettings(d.settings);
            if (Array.isArray(d.programs) && d.programs.length > 0) setPrograms(d.programs);
            if (Array.isArray(d.news) && d.news.length > 0) setNews(d.news);
            if (Array.isArray(d.leaders) && d.leaders.length > 0) setLeaders(d.leaders);
            if (Array.isArray(d.gallery) && d.gallery.length > 0) setGallery(d.gallery);
            if (Array.isArray(d.testimonials) && d.testimonials.length > 0) setTestimonials(d.testimonials);
            if (Array.isArray(d.faqs) && d.faqs.length > 0) setFaqs(d.faqs);
            if (Array.isArray(d.subscribers)) setSubscribers(d.subscribers);
            if (Array.isArray(d.inquiries)) setInquiries(d.inquiries);
            setIsPhpConnected(true);
          }
        }
      } catch {
        // PHP endpoint not reached, fallback to local storage / memory
      } finally {
        setIsLoading(false);
      }
    };

    initData();
  }, []);

  const syncWithPhpBackend = async () => {
    setIsLoading(true);
    try {
      const apiUrl = getApiUrl();
      const res = await fetch(`${apiUrl}/data.php`, { method: 'GET' });
      if (res.ok) {
        const result = await res.json();
        if (result.data) {
          const d = result.data;
          if (d.settings) setSettings(d.settings);
          if (Array.isArray(d.programs)) setPrograms(d.programs);
          if (Array.isArray(d.news)) setNews(d.news);
          if (Array.isArray(d.leaders)) setLeaders(d.leaders);
          if (Array.isArray(d.gallery)) setGallery(d.gallery);
          if (Array.isArray(d.testimonials)) setTestimonials(d.testimonials);
          if (Array.isArray(d.faqs)) setFaqs(d.faqs);
          if (Array.isArray(d.subscribers)) setSubscribers(d.subscribers);
          if (Array.isArray(d.inquiries)) setInquiries(d.inquiries);
          setIsPhpConnected(true);
        }
      }
    } finally {
      setIsLoading(false);
    }
  };

  // --- Mutator Functions ---

  const updateSettings = async (newSettings: Partial<SiteSettings>) => {
    const updated: SiteSettings = {
      ...settings,
      ...newSettings,
      updatedAt: new Date().toISOString(),
    };
    setSettings(updated);
    persistToLocalStorage({
      settings: updated,
      programs,
      news,
      leaders,
      gallery,
      testimonials,
      faqs,
      subscribers,
      inquiries
    });
    await pushToPhpBackend({ settings: updated });
  };

  const saveProgram = async (program: ProgramItem) => {
    const exists = programs.some(p => p.id === program.id);
    const updated = exists
      ? programs.map(p => p.id === program.id ? program : p)
      : [program, ...programs];
    setPrograms(updated);
    persistToLocalStorage({
      settings,
      programs: updated,
      news,
      leaders,
      gallery,
      testimonials,
      faqs,
      subscribers,
      inquiries
    });
    await pushToPhpBackend({ programs: updated });
  };

  const deleteProgram = async (id: string) => {
    const updated = programs.filter(p => p.id !== id);
    setPrograms(updated);
    persistToLocalStorage({
      settings,
      programs: updated,
      news,
      leaders,
      gallery,
      testimonials,
      faqs,
      subscribers,
      inquiries
    });
    await pushToPhpBackend({ programs: updated });
  };

  const saveNewsArticle = async (article: NewsArticle) => {
    const exists = news.some(n => n.id === article.id);
    const updated = exists
      ? news.map(n => n.id === article.id ? article : n)
      : [article, ...news];
    setNews(updated);
    persistToLocalStorage({
      settings,
      programs,
      news: updated,
      leaders,
      gallery,
      testimonials,
      faqs,
      subscribers,
      inquiries
    });
    await pushToPhpBackend({ news: updated });
  };

  const deleteNewsArticle = async (id: string) => {
    const updated = news.filter(n => n.id !== id);
    setNews(updated);
    persistToLocalStorage({
      settings,
      programs,
      news: updated,
      leaders,
      gallery,
      testimonials,
      faqs,
      subscribers,
      inquiries
    });
    await pushToPhpBackend({ news: updated });
  };

  const saveLeader = async (leader: TeamMember) => {
    const exists = leaders.some(l => l.id === leader.id);
    const updated = exists
      ? leaders.map(l => l.id === leader.id ? leader : l)
      : [...leaders, leader];
    setLeaders(updated);
    persistToLocalStorage({
      settings,
      programs,
      news,
      leaders: updated,
      gallery,
      testimonials,
      faqs,
      subscribers,
      inquiries
    });
    await pushToPhpBackend({ leaders: updated });
  };

  const deleteLeader = async (id: string) => {
    const updated = leaders.filter(l => l.id !== id);
    setLeaders(updated);
    persistToLocalStorage({
      settings,
      programs,
      news,
      leaders: updated,
      gallery,
      testimonials,
      faqs,
      subscribers,
      inquiries
    });
    await pushToPhpBackend({ leaders: updated });
  };

  const saveGalleryPhoto = async (photo: GalleryPhoto) => {
    const exists = gallery.some(g => g.id === photo.id);
    const updated = exists
      ? gallery.map(g => g.id === photo.id ? photo : g)
      : [photo, ...gallery];
    setGallery(updated);
    persistToLocalStorage({
      settings,
      programs,
      news,
      leaders,
      gallery: updated,
      testimonials,
      faqs,
      subscribers,
      inquiries
    });
    await pushToPhpBackend({ gallery: updated });
  };

  const deleteGalleryPhoto = async (id: string) => {
    const updated = gallery.filter(g => g.id !== id);
    setGallery(updated);
    persistToLocalStorage({
      settings,
      programs,
      news,
      leaders,
      gallery: updated,
      testimonials,
      faqs,
      subscribers,
      inquiries
    });
    await pushToPhpBackend({ gallery: updated });
  };

  const saveFaq = async (faq: FAQItem, originalQuestion?: string) => {
    const targetQ = originalQuestion || faq.question;
    const exists = faqs.some(f => f.question === targetQ);
    const updated = exists
      ? faqs.map(f => f.question === targetQ ? faq : f)
      : [...faqs, faq];
    setFaqs(updated);
    persistToLocalStorage({
      settings,
      programs,
      news,
      leaders,
      gallery,
      testimonials,
      faqs: updated,
      subscribers,
      inquiries
    });
    await pushToPhpBackend({ faqs: updated });
  };

  const deleteFaq = async (question: string) => {
    const updated = faqs.filter(f => f.question !== question);
    setFaqs(updated);
    persistToLocalStorage({
      settings,
      programs,
      news,
      leaders,
      gallery,
      testimonials,
      faqs: updated,
      subscribers,
      inquiries
    });
    await pushToPhpBackend({ faqs: updated });
  };

  const saveTestimonial = async (testimonial: TestimonialItem) => {
    const exists = testimonials.some(t => t.id === testimonial.id);
    const updated = exists
      ? testimonials.map(t => t.id === testimonial.id ? testimonial : t)
      : [...testimonials, testimonial];
    setTestimonials(updated);
    persistToLocalStorage({
      settings,
      programs,
      news,
      leaders,
      gallery,
      testimonials: updated,
      faqs,
      subscribers,
      inquiries
    });
    await pushToPhpBackend({ testimonials: updated });
  };

  const deleteTestimonial = async (id: string) => {
    const updated = testimonials.filter(t => t.id !== id);
    setTestimonials(updated);
    persistToLocalStorage({
      settings,
      programs,
      news,
      leaders,
      gallery,
      testimonials: updated,
      faqs,
      subscribers,
      inquiries
    });
    await pushToPhpBackend({ testimonials: updated });
  };

  const submitSubscriber = async (data: { name: string; email: string; institution?: string; course?: string }) => {
    const apiUrl = getApiUrl();
    const res = await fetch(`${apiUrl}/subscribers.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data),
    });
    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      throw new Error(err.error || 'Subscription failed.');
    }
    const result = await res.json();
    if (result.item) setSubscribers(prev => [result.item, ...prev]);
  };

  const submitInquiry = async (data: { name: string; email: string; subject?: string; message: string; role?: string }) => {
    const apiUrl = getApiUrl();
    const res = await fetch(`${apiUrl}/inquiries.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data),
    });
    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      throw new Error(err.error || 'Could not send inquiry.');
    }
    const result = await res.json();
    if (result.item) setInquiries(prev => [result.item, ...prev]);
  };

  const updateInquiryStatus = async (id: string, status: 'unread' | 'replied' | 'archived') => {
    const apiUrl = getApiUrl();
    await fetch(`${apiUrl}/inquiries.php?id=${encodeURIComponent(id)}`, {
      method: 'PATCH',
      headers: authHeaders(),
      body: JSON.stringify({ status }),
    });
    setInquiries(prev => prev.map(inq => inq.id === id ? { ...inq, status } : inq));
  };

  const deleteInquiry = async (id: string) => {
    await deleteFromPhpBackend('inquiries', id);
    setInquiries(prev => prev.filter(inq => inq.id !== id));
  };

  const deleteSubscriber = async (id: string) => {
    const apiUrl = getApiUrl();
    await fetch(`${apiUrl}/subscribers.php?id=${encodeURIComponent(id)}`, {
      method: 'DELETE',
      headers: authHeaders(),
    });
    setSubscribers(prev => prev.filter(s => s.id !== id));
  };

  const seedDefaultData = async () => {
    const freshData = {
      settings: DEFAULT_SITE_SETTINGS,
      programs: FOUNDATION_PROGRAMS,
      news: FOUNDATION_NEWS,
      leaders: FOUNDATION_LEADERS,
      gallery: FOUNDATION_GALLERY,
      testimonials: FOUNDATION_TESTIMONIALS,
      faqs: SCHOLARSHIP_FAQS,
      subscribers: [],
      inquiries: []
    };

    setSettings(freshData.settings);
    setPrograms(freshData.programs);
    setNews(freshData.news);
    setLeaders(freshData.leaders);
    setGallery(freshData.gallery);
    setTestimonials(freshData.testimonials);
    setFaqs(freshData.faqs);
    setSubscribers([]);
    setInquiries([]);

    persistToLocalStorage(freshData);
    await pushToPhpBackend(freshData);

    return {
      success: true,
      message: 'Foundation data successfully reset and synchronized with PHP backend store.'
    };
  };

  return (
    <FoundationDataContext.Provider
      value={{
        settings,
        programs,
        news,
        leaders,
        gallery,
        testimonials,
        faqs,
        subscribers,
        inquiries,
        isFirestoreConnected: isPhpConnected, // Aligned with PHP connection
        isPhpConnected,
        isLoading,
        updateSettings,
        saveProgram,
        deleteProgram,
        saveNewsArticle,
        deleteNewsArticle,
        saveLeader,
        deleteLeader,
        saveGalleryPhoto,
        deleteGalleryPhoto,
        saveFaq,
        deleteFaq,
        saveTestimonial,
        deleteTestimonial,
        submitSubscriber,
        submitInquiry,
        updateInquiryStatus,
        deleteInquiry,
        deleteSubscriber,
        seedDefaultData,
        syncWithPhpBackend
      }}
    >
      {children}
    </FoundationDataContext.Provider>
  );
};

export const useFoundationData = (): FoundationDataContextType => {
  const context = useContext(FoundationDataContext);
  if (!context) {
    throw new Error('useFoundationData must be used within a FoundationDataProvider');
  }
  return context;
};
