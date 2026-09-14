import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'motion/react';
import { CheckCircle, AlertCircle, ChevronRight, ChevronLeft, Upload, User, BookOpen, FileText, Send } from 'lucide-react';
import { useFoundationData } from '../context/FoundationDataContext';

interface ApplyScreenProps {
  onNavigate: (screen: any) => void;
}

type Step = 'personal' | 'academic' | 'statement' | 'documents' | 'review';

const STEPS: { key: Step; label: string; icon: React.ReactNode }[] = [
  { key: 'personal',   label: 'Personal Info',  icon: <User size={16} /> },
  { key: 'academic',   label: 'Academic Info',  icon: <BookOpen size={16} /> },
  { key: 'statement',  label: 'Statement',      icon: <FileText size={16} /> },
  { key: 'documents',  label: 'Documents',      icon: <Upload size={16} /> },
  { key: 'review',     label: 'Review & Submit',icon: <Send size={16} /> },
];

const NIGERIAN_STATES = [
  'Abia','Adamawa','Akwa Ibom','Anambra','Bauchi','Bayelsa','Benue','Borno',
  'Cross River','Delta','Ebonyi','Edo','Ekiti','Enugu','FCT (Abuja)',
  'Gombe','Imo','Jigawa','Kaduna','Kano','Katsina','Kebbi','Kogi',
  'Kwara','Lagos','Nasarawa','Niger','Ogun','Ondo','Osun','Oyo',
  'Plateau','Rivers','Sokoto','Taraba','Yobe','Zamfara',
];

const CURRENT_CYCLE = '2025/2026';

export function ApplyScreen({ onNavigate }: ApplyScreenProps) {
  const { settings } = useFoundationData();
  const [step, setStep]       = useState<Step>('personal');
  const [submitting, setSubmitting] = useState(false);
  const [submitted, setSubmitted]   = useState(false);
  const [serverError, setServerError] = useState('');
  const [alreadyApplied, setAlreadyApplied] = useState<{status: string; id: string} | null>(null);

  const [form, setForm] = useState({
    // Personal
    firstName: '', lastName: '', email: '', phone: '',
    dateOfBirth: '', gender: '', stateOfOrigin: '', lga: '', homeAddress: '',
    // Academic
    institution: '', institutionType: 'university', faculty: '', department: '',
    matricNumber: '', academicLevel: '100', cgpa: '', cgpaScale: '5.0',
    academicYear: '2025/2026', scholarshipCycle: CURRENT_CYCLE,
    // Statement
    personalStatement: '', whyDeserve: '', careerGoals: '',
    // Documents (base64 or URLs)
    transcriptUrl: '', idCardUrl: '', admissionLetterUrl: '',
    passportPhotoUrl: '', recommendationUrl: '',
  });

  const [errors, setErrors] = useState<Record<string, string>>({});

  const apiUrl = (window as any).__KPLF_API_URL__ || '/api';

  // Check for existing application on email blur
  const checkExisting = async () => {
    if (!form.email || !form.email.includes('@')) return;
    try {
      const res = await fetch(`${apiUrl}/apply.php?email=${encodeURIComponent(form.email)}&cycle=${encodeURIComponent(CURRENT_CYCLE)}`);
      const data = await res.json();
      if (data.hasApplied) setAlreadyApplied({ status: data.status, id: data.applicationId });
      else setAlreadyApplied(null);
    } catch { /* ignore */ }
  };

  const set = (field: string, value: string) => {
    setForm(prev => ({ ...prev, [field]: value }));
    if (errors[field]) setErrors(prev => { const e = {...prev}; delete e[field]; return e; });
  };

  const validateStep = (): boolean => {
    const e: Record<string, string> = {};
    if (step === 'personal') {
      if (!form.firstName.trim())    e.firstName    = 'First name required.';
      if (!form.lastName.trim())     e.lastName     = 'Last name required.';
      if (!form.email.trim() || !form.email.includes('@')) e.email = 'Valid email required.';
      if (!form.phone.trim())        e.phone        = 'Phone number required.';
      if (!form.stateOfOrigin)       e.stateOfOrigin = 'State of origin required.';
    }
    if (step === 'academic') {
      if (!form.institution.trim())  e.institution  = 'Institution name required.';
      if (!form.department.trim())   e.department   = 'Department required.';
      if (!form.academicLevel)       e.academicLevel = 'Academic level required.';
      if (form.cgpa) {
        const max = form.cgpaScale === '4.0' ? 4.0 : 5.0;
        const min = form.cgpaScale === '4.0' ? 2.5 : 3.0;
        const val = parseFloat(form.cgpa);
        if (isNaN(val) || val < min || val > max) {
          e.cgpa = `CGPA must be between ${min} and ${max} on a ${form.cgpaScale} scale.`;
        }
      }
    }
    if (step === 'statement') {
      if (form.personalStatement.trim().length < 100)
        e.personalStatement = 'Personal statement must be at least 100 characters.';
      if (form.whyDeserve.trim().length < 80)
        e.whyDeserve = 'Please write at least 80 characters.';
    }
    setErrors(e);
    return Object.keys(e).length === 0;
  };

  const next = () => {
    if (!validateStep()) return;
    const idx = STEPS.findIndex(s => s.key === step);
    if (idx < STEPS.length - 1) setStep(STEPS[idx + 1].key);
  };

  const prev = () => {
    const idx = STEPS.findIndex(s => s.key === step);
    if (idx > 0) setStep(STEPS[idx - 1].key);
  };

  // File upload handler (converts to base64)
  const handleFileUpload = (field: string) => async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) {
      setErrors(prev => ({ ...prev, [field]: 'File too large. Maximum 5MB.' }));
      return;
    }

    const reader = new FileReader();
    reader.onload = async (ev) => {
      const base64 = ev.target?.result as string;
      // Try uploading to server
      try {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('entity_type', 'application_' + field.replace('Url', '').toLowerCase());
        const res = await fetch(`${apiUrl}/upload.php`, { method: 'POST', body: formData });
        if (res.ok) {
          const data = await res.json();
          if (data.url) { set(field, data.url); return; }
        }
      } catch { /* fall through to base64 */ }
      set(field, base64);
    };
    reader.readAsDataURL(file);
  };

  const handleSubmit = async (isDraft = false) => {
    if (!isDraft && !validateStep()) return;
    setSubmitting(true);
    setServerError('');
    try {
      const payload = {
        ...form,
        cgpa: form.cgpa ? parseFloat(form.cgpa) : undefined,
        isDraft,
      };
      const res = await fetch(`${apiUrl}/apply.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const data = await res.json();
      if (data.success) {
        setSubmitted(true);
      } else {
        setServerError(data.error || 'Submission failed. Please try again.');
        if (data.applicationId) {
          setAlreadyApplied({ status: data.status, id: data.applicationId });
        }
      }
    } catch {
      setServerError('Network error. Please check your connection and try again.');
    } finally {
      setSubmitting(false);
    }
  };

  const stepIdx = STEPS.findIndex(s => s.key === step);

  // ── Success screen ─────────────────────────────────────────────
  if (submitted) {
    return (
      <div className="min-h-screen flex items-center justify-center p-6">
        <motion.div
          initial={{ opacity: 0, scale: 0.9 }}
          animate={{ opacity: 1, scale: 1 }}
          className="liquid-glass-card max-w-lg w-full p-10 text-center"
        >
          <div className="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <CheckCircle size={40} className="text-green-600" />
          </div>
          <h2 className="text-2xl font-bold text-[#1e1b4b] mb-3">Application Submitted!</h2>
          <p className="text-gray-600 mb-2">
            Thank you, <strong>{form.firstName}</strong>. Your scholarship application for the{' '}
            <strong>{CURRENT_CYCLE}</strong> academic cycle has been received.
          </p>
          <p className="text-sm text-gray-500 mb-8">
            A confirmation will be sent to <strong>{form.email}</strong>. Our review team will contact you within 6–8 weeks.
          </p>
          <div className="flex gap-3 justify-center flex-wrap">
            <button onClick={() => onNavigate('home')} className="liquid-glass-dark-btn px-6 py-2.5 text-sm rounded-xl">
              Back to Home
            </button>
            <button onClick={() => onNavigate('scholarships')} className="liquid-glass-amber-btn px-6 py-2.5 text-sm rounded-xl">
              Learn More About Scholarships
            </button>
          </div>
        </motion.div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-[#fbf9f5] pb-24">
      {/* Header */}
      <div className="bg-[#1e1b4b] text-white py-12 px-6 text-center">
        <motion.div initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }}>
          <span className="inline-block bg-amber-400/20 text-amber-300 text-xs font-semibold px-3 py-1 rounded-full mb-4 tracking-wide uppercase">
            {CURRENT_CYCLE} Academic Cycle
          </span>
          <h1 className="text-3xl md:text-4xl font-bold mb-3">Scholarship Application</h1>
          <p className="text-indigo-200 max-w-xl mx-auto text-sm">
            Complete all sections carefully. All information provided will be verified before awards are made.
          </p>
        </motion.div>
      </div>

      {/* Already applied banner */}
      {alreadyApplied && (
        <div className="max-w-2xl mx-auto mt-6 px-4">
          <div className="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-3">
            <AlertCircle size={20} className="text-amber-600 mt-0.5 flex-shrink-0" />
            <div>
              <p className="text-amber-800 font-semibold text-sm">Existing Application Found</p>
              <p className="text-amber-700 text-sm">
                Your application for {CURRENT_CYCLE} is currently <strong>{alreadyApplied.status.replace('_', ' ')}</strong>.
                Please contact us if you need to make changes.
              </p>
            </div>
          </div>
        </div>
      )}

      {/* Progress stepper */}
      <div className="max-w-2xl mx-auto mt-8 px-4">
        <div className="flex items-center gap-1 mb-8">
          {STEPS.map((s, i) => (
            <React.Fragment key={s.key}>
              <button
                onClick={() => i < stepIdx && setStep(s.key)}
                className={`flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-all
                  ${s.key === step
                    ? 'bg-[#1e1b4b] text-white'
                    : i < stepIdx
                      ? 'bg-green-100 text-green-700 cursor-pointer hover:bg-green-200'
                      : 'bg-gray-100 text-gray-400 cursor-default'
                  }`}
              >
                {i < stepIdx ? <CheckCircle size={12} /> : s.icon}
                <span className="hidden sm:inline">{s.label}</span>
                <span className="sm:hidden">{i + 1}</span>
              </button>
              {i < STEPS.length - 1 && (
                <div className={`flex-1 h-0.5 ${i < stepIdx ? 'bg-green-300' : 'bg-gray-200'}`} />
              )}
            </React.Fragment>
          ))}
        </div>

        {/* Form card */}
        <div className="liquid-glass-card p-6 md:p-8">
          <AnimatePresence mode="wait">
            <motion.div
              key={step}
              initial={{ opacity: 0, x: 20 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: -20 }}
              transition={{ duration: 0.2 }}
            >
              {/* ── Step 1: Personal Info ── */}
              {step === 'personal' && (
                <div className="space-y-5">
                  <h2 className="text-xl font-bold text-[#1e1b4b] mb-1">Personal Information</h2>
                  <p className="text-sm text-gray-500 mb-6">All fields marked * are required.</p>

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <Field label="First Name *" error={errors.firstName}>
                      <input className={input(errors.firstName)} value={form.firstName}
                        onChange={e => set('firstName', e.target.value)} placeholder="e.g. Adaeze" />
                    </Field>
                    <Field label="Last Name *" error={errors.lastName}>
                      <input className={input(errors.lastName)} value={form.lastName}
                        onChange={e => set('lastName', e.target.value)} placeholder="e.g. Okonkwo" />
                    </Field>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <Field label="Email Address *" error={errors.email}>
                      <input type="email" className={input(errors.email)} value={form.email}
                        onChange={e => set('email', e.target.value)}
                        onBlur={checkExisting}
                        placeholder="your@email.com" />
                    </Field>
                    <Field label="Phone Number *" error={errors.phone}>
                      <input type="tel" className={input(errors.phone)} value={form.phone}
                        onChange={e => set('phone', e.target.value)} placeholder="+234 800 000 0000" />
                    </Field>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <Field label="Date of Birth" error={errors.dateOfBirth}>
                      <input type="date" className={input(errors.dateOfBirth)} value={form.dateOfBirth}
                        onChange={e => set('dateOfBirth', e.target.value)} />
                    </Field>
                    <Field label="Gender" error={errors.gender}>
                      <select className={input(errors.gender)} value={form.gender}
                        onChange={e => set('gender', e.target.value)}>
                        <option value="">Select</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="prefer_not_to_say">Prefer not to say</option>
                      </select>
                    </Field>
                    <Field label="State of Origin *" error={errors.stateOfOrigin}>
                      <select className={input(errors.stateOfOrigin)} value={form.stateOfOrigin}
                        onChange={e => set('stateOfOrigin', e.target.value)}>
                        <option value="">Select state</option>
                        {NIGERIAN_STATES.map(s => <option key={s} value={s}>{s}</option>)}
                      </select>
                    </Field>
                  </div>

                  <Field label="LGA" error={errors.lga}>
                    <input className={input(errors.lga)} value={form.lga}
                      onChange={e => set('lga', e.target.value)} placeholder="Local Government Area" />
                  </Field>

                  <Field label="Home Address" error={errors.homeAddress}>
                    <textarea rows={2} className={input(errors.homeAddress)} value={form.homeAddress}
                      onChange={e => set('homeAddress', e.target.value)}
                      placeholder="Street address, city, state" />
                  </Field>
                </div>
              )}

              {/* ── Step 2: Academic Info ── */}
              {step === 'academic' && (
                <div className="space-y-5">
                  <h2 className="text-xl font-bold text-[#1e1b4b] mb-1">Academic Information</h2>
                  <p className="text-sm text-gray-500 mb-6">Your current enrollment details.</p>

                  <Field label="Institution Name *" error={errors.institution}>
                    <input className={input(errors.institution)} value={form.institution}
                      onChange={e => set('institution', e.target.value)}
                      placeholder="e.g. University of Nigeria, Nsukka" />
                  </Field>

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <Field label="Institution Type *" error={errors.institutionType}>
                      <select className={input(errors.institutionType)} value={form.institutionType}
                        onChange={e => set('institutionType', e.target.value)}>
                        <option value="university">University</option>
                        <option value="polytechnic">Polytechnic</option>
                        <option value="college_of_education">College of Education</option>
                        <option value="other">Other Accredited</option>
                      </select>
                    </Field>
                    <Field label="Faculty / College" error={errors.faculty}>
                      <input className={input(errors.faculty)} value={form.faculty}
                        onChange={e => set('faculty', e.target.value)}
                        placeholder="e.g. Faculty of Sciences" />
                    </Field>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <Field label="Department *" error={errors.department}>
                      <input className={input(errors.department)} value={form.department}
                        onChange={e => set('department', e.target.value)}
                        placeholder="e.g. Biochemistry" />
                    </Field>
                    <Field label="Matric / Registration Number" error={errors.matricNumber}>
                      <input className={input(errors.matricNumber)} value={form.matricNumber}
                        onChange={e => set('matricNumber', e.target.value)}
                        placeholder="e.g. 2021/1/12345UE" />
                    </Field>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <Field label="Current Level *" error={errors.academicLevel}>
                      <select className={input(errors.academicLevel)} value={form.academicLevel}
                        onChange={e => set('academicLevel', e.target.value)}>
                        {['100','200','300','400','500'].map(l =>
                          <option key={l} value={l}>{l} Level</option>
                        )}
                        <option value="postgraduate">Postgraduate</option>
                      </select>
                    </Field>
                    <Field label="CGPA" error={errors.cgpa}>
                      <input type="number" step="0.01" min="0" max="5" className={input(errors.cgpa)}
                        value={form.cgpa} onChange={e => set('cgpa', e.target.value)}
                        placeholder="e.g. 3.75" />
                    </Field>
                    <Field label="CGPA Scale" error={errors.cgpaScale}>
                      <select className={input(errors.cgpaScale)} value={form.cgpaScale}
                        onChange={e => set('cgpaScale', e.target.value)}>
                        <option value="5.0">Out of 5.0</option>
                        <option value="4.0">Out of 4.0</option>
                      </select>
                    </Field>
                  </div>

                  <div className="bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm text-amber-800">
                    <strong>Eligibility reminder:</strong> Minimum CGPA of 3.0/5.0 or 2.5/4.0 required. Applications below this threshold will not proceed.
                  </div>
                </div>
              )}

              {/* ── Step 3: Personal Statement ── */}
              {step === 'statement' && (
                <div className="space-y-5">
                  <h2 className="text-xl font-bold text-[#1e1b4b] mb-1">Personal Statement</h2>
                  <p className="text-sm text-gray-500 mb-6">
                    Tell the selection committee about yourself. Be specific and honest.
                  </p>

                  <Field
                    label="Personal Statement *"
                    hint="Describe your background, achievements, and what drives you. (min 100 characters)"
                    error={errors.personalStatement}
                  >
                    <textarea rows={6} className={input(errors.personalStatement)}
                      value={form.personalStatement}
                      onChange={e => set('personalStatement', e.target.value)}
                      placeholder="I am a third-year student of..." />
                    <p className="text-xs text-gray-400 mt-1 text-right">{form.personalStatement.length} chars</p>
                  </Field>

                  <Field
                    label="Why do you deserve this scholarship? *"
                    hint="Explain your financial need and what this award would enable. (min 80 characters)"
                    error={errors.whyDeserve}
                  >
                    <textarea rows={5} className={input(errors.whyDeserve)}
                      value={form.whyDeserve}
                      onChange={e => set('whyDeserve', e.target.value)}
                      placeholder="The scholarship would help me..." />
                  </Field>

                  <Field
                    label="Career Goals"
                    hint="Where do you see yourself in 5–10 years? How will this scholarship help you get there?"
                    error={errors.careerGoals}
                  >
                    <textarea rows={4} className={input(errors.careerGoals)}
                      value={form.careerGoals}
                      onChange={e => set('careerGoals', e.target.value)}
                      placeholder="My goal is to become..." />
                  </Field>
                </div>
              )}

              {/* ── Step 4: Documents ── */}
              {step === 'documents' && (
                <div className="space-y-5">
                  <h2 className="text-xl font-bold text-[#1e1b4b] mb-1">Supporting Documents</h2>
                  <p className="text-sm text-gray-500 mb-6">
                    Upload clear scans or photos. Accepted: JPG, PNG, PDF. Max 5MB per file.
                  </p>

                  {[
                    { field: 'transcriptUrl',       label: 'Academic Transcript *',    required: true  },
                    { field: 'idCardUrl',            label: 'Student / National ID *',  required: true  },
                    { field: 'admissionLetterUrl',   label: 'Admission / Enrollment Letter', required: false },
                    { field: 'passportPhotoUrl',     label: 'Passport Photograph',      required: false },
                    { field: 'recommendationUrl',    label: 'Recommendation Letter (optional)', required: false },
                  ].map(({ field, label, required }) => (
                    <div key={field} className="border border-gray-200 rounded-xl p-4">
                      <p className="text-sm font-semibold text-gray-700 mb-2">{label}</p>
                      {(form as any)[field] ? (
                        <div className="flex items-center gap-3">
                          <CheckCircle size={18} className="text-green-500" />
                          <span className="text-sm text-green-700 truncate max-w-xs">
                            {(form as any)[field].startsWith('data:') ? 'File attached (base64)' : (form as any)[field]}
                          </span>
                          <button onClick={() => set(field, '')}
                            className="text-xs text-red-500 hover:underline ml-auto flex-shrink-0">Remove</button>
                        </div>
                      ) : (
                        <label className="flex items-center gap-3 cursor-pointer group">
                          <div className="flex items-center gap-2 px-4 py-2 border-2 border-dashed border-gray-300 group-hover:border-indigo-400 rounded-lg text-sm text-gray-500 transition-colors">
                            <Upload size={16} />
                            <span>Choose file</span>
                          </div>
                          <input type="file" className="hidden"
                            accept="image/jpeg,image/png,image/webp,application/pdf"
                            onChange={handleFileUpload(field)} />
                          {!required && <span className="text-xs text-gray-400">Optional</span>}
                        </label>
                      )}
                      {errors[field] && <p className="text-xs text-red-500 mt-1">{errors[field]}</p>}
                    </div>
                  ))}
                </div>
              )}

              {/* ── Step 5: Review ── */}
              {step === 'review' && (
                <div className="space-y-6">
                  <h2 className="text-xl font-bold text-[#1e1b4b] mb-1">Review Your Application</h2>
                  <p className="text-sm text-gray-500 mb-4">
                    Please verify your details below before submitting.
                  </p>

                  <ReviewSection title="Personal Information">
                    <ReviewRow label="Name"       value={`${form.firstName} ${form.lastName}`} />
                    <ReviewRow label="Email"      value={form.email} />
                    <ReviewRow label="Phone"      value={form.phone} />
                    <ReviewRow label="State"      value={form.stateOfOrigin} />
                  </ReviewSection>

                  <ReviewSection title="Academic Details">
                    <ReviewRow label="Institution"   value={form.institution} />
                    <ReviewRow label="Department"    value={form.department} />
                    <ReviewRow label="Level"         value={form.academicLevel + ' Level'} />
                    <ReviewRow label="CGPA"          value={form.cgpa ? `${form.cgpa} / ${form.cgpaScale}` : 'Not provided'} />
                    <ReviewRow label="Cycle"         value={form.scholarshipCycle} />
                  </ReviewSection>

                  <ReviewSection title="Documents">
                    {[
                      ['Transcript',         form.transcriptUrl],
                      ['ID Card',            form.idCardUrl],
                      ['Admission Letter',   form.admissionLetterUrl],
                      ['Passport Photo',     form.passportPhotoUrl],
                      ['Recommendation',     form.recommendationUrl],
                    ].map(([label, val]) => (
                      <React.Fragment key={label as string}>
                        <ReviewRow label={label as string}
                          value={val ? '✓ Uploaded' : '— Not provided'} />
                      </React.Fragment>
                    ))}
                  </ReviewSection>

                  {serverError && (
                    <div className="bg-red-50 border border-red-200 rounded-lg p-4 flex items-start gap-3">
                      <AlertCircle size={18} className="text-red-500 mt-0.5 flex-shrink-0" />
                      <p className="text-red-700 text-sm">{serverError}</p>
                    </div>
                  )}

                  <div className="bg-indigo-50 border border-indigo-200 rounded-xl p-4 text-sm text-indigo-800">
                    By submitting this application, you confirm that all information provided is accurate and complete.
                    Falsified applications will be disqualified.
                  </div>

                  <div className="flex flex-col sm:flex-row gap-3">
                    <button
                      onClick={() => handleSubmit(true)}
                      disabled={submitting}
                      className="flex-1 py-3 border-2 border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:border-indigo-300 hover:text-indigo-700 transition-all disabled:opacity-50"
                    >
                      Save as Draft
                    </button>
                    <button
                      onClick={() => handleSubmit(false)}
                      disabled={submitting}
                      className="flex-1 liquid-glass-amber-btn py-3 rounded-xl text-sm font-semibold disabled:opacity-50 flex items-center justify-center gap-2"
                    >
                      {submitting ? (
                        <><span className="animate-spin inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full" /> Submitting...</>
                      ) : (
                        <><Send size={16} /> Submit Application</>
                      )}
                    </button>
                  </div>
                </div>
              )}
            </motion.div>
          </AnimatePresence>

          {/* Navigation buttons */}
          {step !== 'review' && (
            <div className="flex justify-between mt-8 pt-6 border-t border-gray-100">
              <button
                onClick={prev}
                disabled={stepIdx === 0}
                className="flex items-center gap-1.5 px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-all disabled:opacity-30 disabled:cursor-not-allowed"
              >
                <ChevronLeft size={16} /> Previous
              </button>
              <button
                onClick={next}
                className="liquid-glass-dark-btn flex items-center gap-1.5 px-6 py-2.5 text-sm rounded-xl"
              >
                Next <ChevronRight size={16} />
              </button>
            </div>
          )}
          {step !== 'review' && stepIdx > 0 && (
            <div className="text-center mt-4">
              <button
                onClick={() => handleSubmit(true)}
                className="text-xs text-gray-400 hover:text-gray-600 underline"
              >
                Save progress as draft
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

// ── Utility sub-components ─────────────────────────────────────
function Field({ label, hint, error, children }: {
  label: string; hint?: string; error?: string; children: React.ReactNode;
}) {
  return (
    <div>
      <label className="block text-sm font-semibold text-gray-700 mb-1">{label}</label>
      {hint && <p className="text-xs text-gray-400 mb-1.5">{hint}</p>}
      {children}
      {error && <p className="text-xs text-red-500 mt-1">{error}</p>}
    </div>
  );
}

function ReviewSection({ title, children }: { title: string; children: React.ReactNode }) {
  return (
    <div className="border border-gray-200 rounded-xl overflow-hidden">
      <div className="bg-gray-50 px-4 py-2 border-b border-gray-200">
        <h3 className="text-sm font-semibold text-gray-700">{title}</h3>
      </div>
      <div className="divide-y divide-gray-100">{children}</div>
    </div>
  );
}

function ReviewRow({ label, value }: { label: string; value: string }) {
  return (
    <div className="flex px-4 py-2.5 gap-4">
      <span className="text-xs text-gray-500 w-32 flex-shrink-0">{label}</span>
      <span className="text-sm text-gray-800 font-medium">{value || '—'}</span>
    </div>
  );
}

function input(error?: string) {
  return `w-full px-3 py-2 border rounded-lg text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-400 ${
    error ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-white hover:border-gray-300'
  }`;
}
