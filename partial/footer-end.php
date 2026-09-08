<!-- Telegram Chat Widget - Add before </body> -->
<style>
  .telegram-widget-container {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  }
  
  .telegram-chat-popup {
    position: absolute;
    bottom: 70px;
    right: 0;
    width: 320px;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    overflow: hidden;
    transform: scale(0.8) translateY(20px);
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
  }
  
  .telegram-chat-popup.active {
    transform: scale(1) translateY(0);
    opacity: 1;
    visibility: visible;
  }
  
  .telegram-header {
    background: #e10700;
    padding: 16px;
    color: white;
  }
  
  .telegram-header-content {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  
  .telegram-avatar {
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .telegram-avatar svg {
    width: 24px;
    height: 24px;
    fill: white;
  }
  
  .telegram-info h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
  }
  
  .telegram-status {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    opacity: 0.9;
  }
  
  .telegram-status-dot {
    width: 8px;
    height: 8px;
    background: #4ade80;
    border-radius: 50%;
    animation: pulse 2s infinite;
  }
  
  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
  }
  
  .telegram-body {
    padding: 16px;
  }
  
  .telegram-welcome {
    background: #f3f4f6;
    border-radius: 12px;
    padding: 12px;
    margin-bottom: 16px;
  }
  
  .telegram-welcome p {
    margin: 0;
    font-size: 14px;
    color: #374151;
  }
  
  .telegram-welcome p:last-child {
    margin-top: 8px;
    color: #6b7280;
  }
  
  .telegram-topics-label {
    font-size: 11px;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
  }
  
  .telegram-topics {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 16px;
  }
  
  .telegram-topic {
    font-size: 12px;
    background: rgba(0, 136, 204, 0.1);
    color: #0088cc;
    padding: 6px 12px;
    border-radius: 20px;
    cursor: pointer;
    transition: background 0.2s;
    border: none;
  }
  
  .telegram-topic:hover {
    background: rgba(0, 136, 204, 0.2);
  }
  
  .telegram-cta {
    width: 100%;
    background: #e10700;
    color: white;
    border: none;
    padding: 12px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: background 0.2s;
  }
  
  .telegram-cta:hover {
    background: #0077b5;
  }
  
  .telegram-cta svg {
    width: 16px;
    height: 16px;
  }
  
  .telegram-footer {
    text-align: center;
    font-size: 12px;
    color: #9ca3af;
    margin-top: 12px;
  }
  
  .telegram-fab {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0088cc, #00a2e8);
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 20px rgba(0, 136, 204, 0.4);
    transition: transform 0.2s, box-shadow 0.2s;
    position: relative;
  }
  
  .telegram-fab:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 25px rgba(0, 136, 204, 0.5);
  }
  
  .telegram-fab svg {
    width: 24px;
    height: 24px;
    fill: white;
    transition: transform 0.3s;
  }
  
  .telegram-fab.active svg.icon-chat {
    transform: rotate(90deg);
    opacity: 0;
  }
  
  .telegram-fab svg.icon-close {
    position: absolute;
    transform: rotate(-90deg);
    opacity: 0;
  }
  
  .telegram-fab.active svg.icon-close {
    transform: rotate(0);
    opacity: 1;
  }
  
  .telegram-fab-badge {
    position: absolute;
    top: 0;
    right: 0;
    width: 12px;
    height: 12px;
    background: #4ade80;
    border-radius: 50%;
    border: 2px solid white;
    animation: pulse 2s infinite;
  }
  
  .telegram-fab.active .telegram-fab-badge {
    display: none;
  }

  /* Dark mode support */
  @media (prefers-color-scheme: dark) {
    .telegram-chat-popup {
      background: #1f2937;
    }
    .telegram-welcome {
      background: #374151;
    }
    .telegram-welcome p {
      color: #e5e7eb;
    }
    .telegram-welcome p:last-child {
      color: #9ca3af;
    }
  }
</style>

<div class="telegram-widget-container">
  <!-- Chat Popup -->
  <div class="telegram-chat-popup" id="telegramPopup">
    <div class="telegram-header">
      <div class="telegram-header-content">
        <div class="telegram-avatar">
          <svg viewBox="0 0 24 24">
            <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
          </svg>
        </div>
        <div class="telegram-info">
          <h3>MyOgSms Support</h3>
          <div class="telegram-status">
            <span class="telegram-status-dot"></span>
            Online 24/7
          </div>
        </div>
      </div>
    </div>
    
    <div class="telegram-body">
      <div class="telegram-welcome">
        <p>👋 Hello! Welcome to MyOgSms Support.</p>
        <p>Do you need any Help/Assistance?, We're available 24/7 to help you Chat With Us Now!</p>
      </div>
      
      <div class="telegram-topics-label">How can we help?</div>
      <div class="telegram-topics">
        <button class="telegram-topic" onclick="openTelegram()">Number Issues</button>
        <button class="telegram-topic" onclick="openTelegram()">Payment Help</button>
        <button class="telegram-topic" onclick="openTelegram()">API Support</button>
        <button class="telegram-topic" onclick="openTelegram()">General Inquiry</button>
      </div>
      
      <button class="telegram-cta" onclick="openTelegram()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
        </svg>
        Chat on Telegram
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left: auto;">
          <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14L21 3"/>
        </svg>
      </button>
      
      <p class="telegram-footer">Average response time: &lt; 5 minutes</p>
    </div>
  </div>
  
  <!-- Floating Action Button -->
  <button class="telegram-fab" id="telegramFab" onclick="toggleChat()">
    <svg class="icon-chat" viewBox="0 0 24 24" fill="none" >
      <path fill="white"
      d="M22.5 2.3L1.6 10.2c-1.4.5-1.4 1.5-.3 1.8l5.4 1.7L19.1 5c.6-.4 1.1-.2.7.2L9.7 15.1l-.4 5.7c.6 0 .9-.3 1.2-.6l2.9-2.8 6.1 4.5c1.1.6 1.9.3 2.2-1L24 3.6c.4-1.5-.6-2.2-1.5-1.3z"/>
    </svg>
    <svg class="icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M18 6L6 18M6 6l12 12"/>
    </svg>
    <span class="telegram-fab-badge"></span>
  </button>
</div>

<script>
  // CHANGE THIS TO YOUR TELEGRAM USERNAME
  const TELEGRAM_USERNAME = 'myogsocial';
  
  function toggleChat() {
    const popup = document.getElementById('telegramPopup');
    const fab = document.getElementById('telegramFab');
    popup.classList.toggle('active');
    fab.classList.toggle('active');
  }
  
  function openTelegram() {
    window.open('https://t.me/' + TELEGRAM_USERNAME, '_blank');
  }
  
  // Close popup when clicking outside
  document.addEventListener('click', function(e) {
    const container = document.querySelector('.telegram-widget-container');
    if (!container.contains(e.target)) {
      document.getElementById('telegramPopup').classList.remove('active');
      document.getElementById('telegramFab').classList.remove('active');
    }
  });
</script>
<!-- End Telegram Chat Widget -->
