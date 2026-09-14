import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import path from 'path';
import { defineConfig, Plugin } from 'vite';

// ─────────────────────────────────────────────────────────────────────────────
// Dev-mode PHP API simulator
// Mirrors the real PHP/MySQL backend so the Vite dev server works standalone.
// When running against XAMPP (npm run dev:xampp), this plugin is bypassed and
// requests proxy directly to http://localhost/karl-peace/public/api/
// ─────────────────────────────────────────────────────────────────────────────
function phpApiDevPlugin(): Plugin {
  // In-memory stores (mirrors the DB for dev mode)
  const store: Record<string, any> = {
    settings: {
      heroTitle: 'Empowering Nigerian Youth Through Education, Health, and Mentorship',
      heroSubtitle: "Honoring Dr. Karl E. Peace's lifelong legacy in public health and biostatistics.",
      heroBadge: 'Fostering Excellence • Expanding Horizons',
      contactEmail: 'admin@karlpeacelegacy.org',
      contactPhone: '+234 800 000 0000',
      officeAddress: '25 Ediba Rd, Calabar, Cross River State, Nigeria',
      registrationNumber: '9622998',
      officialDomain: 'karlpeacelegacy.org',
      scholarshipAlertActive: true,
      scholarshipAlertTitle: '2025/2026 Tertiary Scholarship Framework',
      scholarshipAlertText: 'The official evaluation roadmap has been ratified by the board of trustees.',
      scholarshipAlertDeadline: 'Opening for Applications',
      scholarshipAlertCycle: '2025/2026 Academic Session',
    },
    programs: [],
    news: [],
    leaders: [],
    gallery: [],
    testimonials: [],
    faqs: [],
    subscribers: [],
    inquiries: [],
    applications: [],
  };

  // Valid dev tokens
  const sessions = new Set<string>();

  function json(res: any, data: object, status = 200) {
    res.statusCode = status;
    res.setHeader('Content-Type', 'application/json');
    res.end(JSON.stringify(data));
  }

  function parseBody(req: any): Promise<Record<string, any>> {
    return new Promise(resolve => {
      let raw = '';
      req.on('data', (c: any) => { raw += c; });
      req.on('end', () => {
        try { resolve(JSON.parse(raw)); } catch { resolve({}); }
      });
    });
  }

  function isAuthed(req: any): boolean {
    const h = req.headers['authorization'] || '';
    const token = h.replace('Bearer ', '').trim();
    return sessions.has(token);
  }

  return {
    name: 'php-api-dev-plugin',
    configureServer(server) {
      server.middlewares.use(async (req, res, next) => {
        const url = (req.url || '').split('?')[0];
        const qs  = new URLSearchParams((req.url || '').split('?')[1] || '');
        const method = req.method || 'GET';

        if (!url.startsWith('/api/')) return next();

        // CORS
        res.setHeader('Access-Control-Allow-Origin', 'http://localhost:5173');
        res.setHeader('Access-Control-Allow-Methods', 'GET,POST,PUT,PATCH,DELETE,OPTIONS');
        res.setHeader('Access-Control-Allow-Headers', 'Content-Type,Authorization');
        res.setHeader('Access-Control-Allow-Credentials', 'true');
        if (method === 'OPTIONS') { res.statusCode = 200; res.end(); return; }

        const body = await parseBody(req);

        // ── /api/login.php ──────────────────────────────────────
        if (url.includes('/api/login.php') && method === 'POST') {
          const email = (body.email || '').toLowerCase().trim();
          const pw    = (body.password || '').trim();
          const validEmail = email === 'admin@karlpeacelegacy.org' || email === 'admin' ||
                             email.endsWith('@karlpeacelegacy.org');
          const validPw    = ['admin', 'karlpeace2026', 'KarlPeace2026!', 'admin123'].includes(pw);

          if (validEmail && validPw) {
            const token = 'kplf_dev_' + Math.random().toString(36).slice(2) + Date.now();
            sessions.add(token);
            return json(res, {
              success: true,
              token,
              user: {
                uid: 'dev_admin',
                email: email === 'admin' ? 'admin@karlpeacelegacy.org' : email,
                displayName: 'Karl Peace Foundation Admin',
                role: 'admin',
                isSuperAdmin: true,
              },
            });
          }
          return json(res, { success: false, error: 'Invalid credentials.' }, 401);
        }

        // ── /api/logout.php ─────────────────────────────────────
        if (url.includes('/api/logout.php')) {
          const h = (req.headers['authorization'] || '').replace('Bearer ', '').trim();
          sessions.delete(h);
          return json(res, { success: true, message: 'Logged out.' });
        }

        // ── /api/check-auth.php ─────────────────────────────────
        if (url.includes('/api/check-auth.php')) {
          if (!isAuthed(req)) return json(res, { success: false, authenticated: false }, 401);
          return json(res, {
            success: true, authenticated: true,
            user: { uid: 'dev_admin', email: 'admin@karlpeacelegacy.org',
                    displayName: 'Foundation Admin', role: 'admin', isSuperAdmin: true },
          });
        }

        // ── /api/data.php ───────────────────────────────────────
        if (url.includes('/api/data.php')) {
          if (method === 'GET') {
            const section = qs.get('section') || 'all';
            const data: Record<string, any> = {};
            if (section === 'all' || section === 'settings')     data.settings     = store.settings;
            if (section === 'all' || section === 'programs')     data.programs     = store.programs;
            if (section === 'all' || section === 'news')         data.news         = store.news;
            if (section === 'all' || section === 'leaders')      data.leaders      = store.leaders;
            if (section === 'all' || section === 'gallery')      data.gallery      = store.gallery;
            if (section === 'all' || section === 'testimonials') data.testimonials = store.testimonials;
            if (section === 'all' || section === 'faqs')         data.faqs         = store.faqs;
            return json(res, { success: true, source: 'dev_simulator', data });
          }
          if (method === 'POST' || method === 'PUT' || method === 'PATCH') {
            if (!isAuthed(req)) return json(res, { success: false, error: 'Unauthorized.' }, 401);
            Object.assign(store, body);
            return json(res, { success: true, message: 'Content saved.' });
          }
          if (method === 'DELETE') {
            if (!isAuthed(req)) return json(res, { success: false, error: 'Unauthorized.' }, 401);
            const section = qs.get('section') as string;
            const id = qs.get('id') as string;
            const keyMap: Record<string, string> = {
              program: 'programs', news: 'news', leader: 'leaders',
              gallery: 'gallery', testimonial: 'testimonials', faq: 'faqs',
            };
            const key = keyMap[section];
            if (key && Array.isArray(store[key])) {
              store[key] = store[key].filter((i: any) => i.id !== id);
            }
            return json(res, { success: true, message: 'Item deleted.' });
          }
        }

        // ── /api/subscribers.php ────────────────────────────────
        if (url.includes('/api/subscribers.php')) {
          if (method === 'POST') {
            const sub = {
              id: 'sub_' + Date.now(),
              name: body.name || '',
              email: body.email || '',
              institution: body.institution || '',
              course: body.course || '',
              status: 'new',
              createdAt: new Date().toISOString(),
            };
            store.subscribers.unshift(sub);
            return json(res, { success: true, item: sub }, 201);
          }
          if (method === 'GET') {
            if (!isAuthed(req)) return json(res, { success: false, error: 'Unauthorized.' }, 401);
            return json(res, { success: true, subscribers: store.subscribers, total: store.subscribers.length });
          }
          if (method === 'PATCH') {
            if (!isAuthed(req)) return json(res, { success: false, error: 'Unauthorized.' }, 401);
            const id = qs.get('id');
            const s = store.subscribers.find((s: any) => s.id === id);
            if (s && body.status) s.status = body.status;
            return json(res, { success: true, message: 'Updated.' });
          }
          if (method === 'DELETE') {
            if (!isAuthed(req)) return json(res, { success: false, error: 'Unauthorized.' }, 401);
            const id = qs.get('id');
            store.subscribers = store.subscribers.filter((s: any) => s.id !== id);
            return json(res, { success: true });
          }
        }

        // ── /api/inquiries.php ──────────────────────────────────
        if (url.includes('/api/inquiries.php')) {
          if (method === 'POST') {
            const inq = {
              id: 'inq_' + Date.now(),
              name: body.name || '',
              email: body.email || '',
              subject: body.subject || 'General Inquiry',
              message: body.message || '',
              role: body.role || '',
              status: 'unread',
              createdAt: new Date().toISOString(),
            };
            store.inquiries.unshift(inq);
            return json(res, { success: true, item: inq }, 201);
          }
          if (method === 'GET') {
            if (!isAuthed(req)) return json(res, { success: false, error: 'Unauthorized.' }, 401);
            const id = qs.get('id');
            if (id) {
              const item = store.inquiries.find((i: any) => i.id === id);
              return item ? json(res, { success: true, inquiry: item })
                          : json(res, { success: false, error: 'Not found.' }, 404);
            }
            return json(res, { success: true, inquiries: store.inquiries, total: store.inquiries.length });
          }
          if (method === 'PATCH') {
            if (!isAuthed(req)) return json(res, { success: false, error: 'Unauthorized.' }, 401);
            const id = qs.get('id');
            const item = store.inquiries.find((i: any) => i.id === id);
            if (item) Object.assign(item, body);
            return json(res, { success: true });
          }
          if (method === 'DELETE') {
            if (!isAuthed(req)) return json(res, { success: false, error: 'Unauthorized.' }, 401);
            const id = qs.get('id');
            store.inquiries = store.inquiries.filter((i: any) => i.id !== id);
            return json(res, { success: true });
          }
        }

        // ── /api/apply.php ──────────────────────────────────────
        if (url.includes('/api/apply.php')) {
          if (method === 'GET') {
            const email = (qs.get('email') || '').toLowerCase();
            const cycle = qs.get('cycle') || '';
            const existing = store.applications.find(
              (a: any) => a.email === email && (!cycle || a.scholarshipCycle === cycle)
            );
            return json(res, {
              success: true,
              hasApplied: !!existing,
              status: existing?.status || null,
              applicationId: existing?.id || null,
            });
          }
          if (method === 'POST') {
            const isDraft = body.isDraft === true || body.isDraft === 'true';
            const app = {
              id: 'app_' + Date.now(),
              ...body,
              status: isDraft ? 'draft' : 'submitted',
              submittedAt: isDraft ? null : new Date().toISOString(),
              createdAt: new Date().toISOString(),
            };
            store.applications.unshift(app);
            return json(res, {
              success: true,
              message: isDraft ? 'Draft saved.' : 'Application submitted successfully.',
              application: app,
            }, 201);
          }
        }

        // ── /api/applications.php ───────────────────────────────
        if (url.includes('/api/applications.php')) {
          if (!isAuthed(req)) return json(res, { success: false, error: 'Unauthorized.' }, 401);
          if (method === 'GET') {
            const id = qs.get('id');
            if (id) {
              const app = store.applications.find((a: any) => a.id === id);
              return app ? json(res, { success: true, application: app })
                         : json(res, { success: false, error: 'Not found.' }, 404);
            }
            const statusFilter = qs.get('status');
            const filtered = statusFilter
              ? store.applications.filter((a: any) => a.status === statusFilter)
              : store.applications;
            const stats = { total: store.applications.length, submitted: 0, under_review: 0,
                            shortlisted: 0, approved: 0, rejected: 0, draft: 0 };
            store.applications.forEach((a: any) => { if (stats[a.status as keyof typeof stats] !== undefined) (stats as any)[a.status]++; });
            return json(res, { success: true, applications: filtered, stats, total: filtered.length });
          }
          if (method === 'PATCH') {
            const id = qs.get('id');
            const app = store.applications.find((a: any) => a.id === id);
            if (app) Object.assign(app, body);
            return json(res, { success: true });
          }
          if (method === 'DELETE') {
            const id = qs.get('id');
            store.applications = store.applications.filter((a: any) => a.id !== id);
            return json(res, { success: true });
          }
        }

        // ── /api/admin-users.php ────────────────────────────────
        if (url.includes('/api/admin-users.php')) {
          if (!isAuthed(req)) return json(res, { success: false, error: 'Unauthorized.' }, 401);
          return json(res, {
            success: true,
            users: [{
              id: 1, uid: 'dev_admin', email: 'admin@karlpeacelegacy.org',
              displayName: 'Foundation Admin', role: 'admin',
              isSuperAdmin: true, isActive: true,
            }],
          });
        }

        // ── /api/audit.php ──────────────────────────────────────
        if (url.includes('/api/audit.php')) {
          if (!isAuthed(req)) return json(res, { success: false, error: 'Unauthorized.' }, 401);
          return json(res, { success: true, entries: [], total: 0 });
        }

        // ── /api/upload.php ─────────────────────────────────────
        if (url.includes('/api/upload.php') && method === 'POST') {
          return json(res, { success: true, url: '', message: 'Dev mode: use base64 data URL directly.' });
        }

        // ── /api/export.php ─────────────────────────────────────
        if (url.includes('/api/export.php')) {
          if (!isAuthed(req)) return json(res, { success: false, error: 'Unauthorized.' }, 401);
          const type = qs.get('type') || 'json';
          if (type === 'json') {
            res.setHeader('Content-Disposition', 'attachment; filename="karl_peace_dev_backup.json"');
            res.statusCode = 200;
            res.end(JSON.stringify({ exportedAt: new Date().toISOString(), data: store }, null, 2));
            return;
          }
          return json(res, { success: false, error: 'CSV export requires real PHP backend.' }, 501);
        }

        next();
      });
    },
  };
}

export default defineConfig(() => {
  const useXampp = process.env.USE_XAMPP === 'true';

  return {
    plugins: [react(), tailwindcss(), ...(useXampp ? [] : [phpApiDevPlugin()])],
    resolve: {
      alias: {
        '@': path.resolve(__dirname, '.'),
      },
    },
    server: {
      port: 5173,
      host: '0.0.0.0',
      strictPort: false,
      hmr: process.env.DISABLE_HMR !== 'true',
      // historyApiFallback handled by .htaccess on Apache — enabling here breaks API proxy
      ...(useXampp ? {
        proxy: {
          '/api': {
            target: 'http://localhost:8080/karl-peace/public',
            changeOrigin: true,
            secure: false,
          },
          '/uploads': {
            target: 'http://localhost:8080/karl-peace/public',
            changeOrigin: true,
            secure: false,
          },
        },
      } : {}),
    },
  };
});
