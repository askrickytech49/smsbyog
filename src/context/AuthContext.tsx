import React, { createContext, useContext, useEffect, useState } from 'react';
import { AdminUser } from '../types';

export interface AuthUser {
  uid: string;
  email: string;
  displayName: string;
  photoURL?: string;
  role: 'admin' | 'editor' | 'viewer';
  isSuperAdmin?: boolean;
}

interface AuthContextType {
  user: AuthUser | null;
  adminUser: AdminUser | null;
  loading: boolean;
  isAdmin: boolean;
  isSuperAdmin: boolean;
  authError: string | null;
  phpApiUrl: string;
  setPhpApiUrl: (url: string) => void;
  loginWithCredentials: (email: string, password: string) => Promise<boolean>;
  loginWithGoogle: () => Promise<void>;
  logout: () => Promise<void>;
  clearAuthError: () => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

const LOCAL_STORAGE_USER_KEY = 'karl_peace_admin_user';
const LOCAL_STORAGE_TOKEN_KEY = 'karl_peace_admin_token';
const LOCAL_STORAGE_API_URL_KEY = 'karl_peace_php_api_url';

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [adminUser, setAdminUser] = useState<AdminUser | null>(null);
  const [loading, setLoading] = useState<boolean>(true);
  const [authError, setAuthError] = useState<string | null>(null);
  const [phpApiUrl, setPhpApiUrlState] = useState<string>('/api');

  // Load persisted session on startup — always use /api as the endpoint
  useEffect(() => {
    try {
      // Clear any stale custom API URL from localStorage — always use proxy default
      localStorage.removeItem(LOCAL_STORAGE_API_URL_KEY);

      const savedUserStr = localStorage.getItem(LOCAL_STORAGE_USER_KEY);
      if (savedUserStr) {
        const parsed = JSON.parse(savedUserStr) as AuthUser;
        setUser(parsed);
        setAdminUser({
          uid: parsed.uid,
          email: parsed.email,
          displayName: parsed.displayName,
          photoURL: parsed.photoURL,
          role: parsed.role,
        });
      }
    } catch (e) {
      console.warn('Error reading saved admin session:', e);
    } finally {
      setLoading(false);
    }
  }, []);

  const setPhpApiUrl = (url: string) => {
    const cleanUrl = url.trim().replace(/\/$/, '');
    setPhpApiUrlState(cleanUrl);
    localStorage.setItem(LOCAL_STORAGE_API_URL_KEY, cleanUrl);
  };

  /**
   * Primary Authentication: Email/Password against PHP Backend (MySQL)
   */
  const loginWithCredentials = async (emailInput: string, passwordInput: string): Promise<boolean> => {
    setAuthError(null);
    const email = emailInput.trim().toLowerCase();
    const password = passwordInput.trim();

    if (!email || !password) {
      setAuthError('Please provide both email and password.');
      return false;
    }

    try {
      const response = await fetch(`${phpApiUrl}/login.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password }),
      });

      const data = await response.json();

      if (response.ok && data.success && data.user && data.token) {
        const authUser: AuthUser = {
          uid:          data.user.uid || 'php_admin_' + Date.now(),
          email:        data.user.email || email,
          displayName:  data.user.displayName || 'Foundation Admin',
          role:         data.user.role || 'admin',
          isSuperAdmin: Boolean(data.user.isSuperAdmin),
        };

        localStorage.setItem(LOCAL_STORAGE_USER_KEY,  JSON.stringify(authUser));
        localStorage.setItem(LOCAL_STORAGE_TOKEN_KEY, data.token);

        setUser(authUser);
        setAdminUser({
          uid:         authUser.uid,
          email:       authUser.email,
          displayName: authUser.displayName,
          role:        authUser.role,
        });
        return true;
      }

      // Surface server error message
      setAuthError(data.error || 'Invalid credentials.');
      return false;

    } catch (fetchErr) {
      console.warn('PHP API unreachable:', fetchErr);
      setAuthError('Unable to connect. Please try again shortly.');
      return false;
    }
  };

  /** Not used with MySQL backend — kept for interface compatibility */
  const loginWithGoogle = async () => {
    setAuthError('Google sign-in is not configured. Please use email and password.');
  };

  const logout = async () => {
    const token = localStorage.getItem(LOCAL_STORAGE_TOKEN_KEY);
    try {
      await fetch(`${phpApiUrl}/logout.php`, {
        method: 'POST',
        headers: token ? { 'Authorization': `Bearer ${token}` } : {},
      }).catch(() => {});
    } finally {
      localStorage.removeItem(LOCAL_STORAGE_USER_KEY);
      localStorage.removeItem(LOCAL_STORAGE_TOKEN_KEY);
      setUser(null);
      setAdminUser(null);
    }
  };

  const isSuperAdmin = Boolean(user?.isSuperAdmin);

  const isAdmin = Boolean(
    isSuperAdmin ||
    user?.role === 'admin' ||
    user?.role === 'editor'
  );

  return (
    <AuthContext.Provider
      value={{
        user,
        adminUser,
        loading,
        isAdmin,
        isSuperAdmin,
        authError,
        phpApiUrl,
        setPhpApiUrl,
        loginWithCredentials,
        loginWithGoogle,
        logout,
        clearAuthError: () => setAuthError(null),
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = (): AuthContextType => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
