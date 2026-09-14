import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import App from './App.tsx';
import { AuthProvider } from './context/AuthContext';
import { FoundationDataProvider } from './context/FoundationDataContext';
import './index.css';

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <AuthProvider>
      <FoundationDataProvider>
        <App />
      </FoundationDataProvider>
    </AuthProvider>
  </StrictMode>,
);
