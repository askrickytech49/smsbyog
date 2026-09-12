<?php
/**
 * service_icons.php
 * Returns a reliable icon URL for any OTP service by name or code.
 * Strategy: curated map of well-known services → stable public CDN URLs.
 * Fallback: Google favicon API (works for 95%+ of services).
 */

function getServiceIcon(string $serviceName, string $serviceCode = ''): string {

    // Normalise for matching
    $name = strtolower(trim($serviceName));
    $code = strtolower(trim($serviceCode));

    // ── CURATED HIGH-QUALITY ICONS ────────────────────────────────────────────
    // Using official or Wikipedia SVG/PNG sources where possible
    static $icons = [
        // Messaging
        'whatsapp'    => 'https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/whatsapp.png',
        'telegram'    => 'https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/telegram.png',
        'signal'      => 'https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/signal.png',
        'viber'       => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e2/Viber_logo.svg/240px-Viber_logo.svg.png',
        'wechat'      => 'https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/wechat.png',
        'line'        => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/41/LINE_logo.svg/240px-LINE_logo.svg.png',
        'discord'     => 'https://assets-global.website-files.com/6257adef93867e50d84d30e2/636e0a6918e57475a843f59f_icon_clyde_black_RGB.png',
        'kakao'       => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e3/KakaoTalk_logo.svg/240px-KakaoTalk_logo.svg.png',
        'kakaotalk'   => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e3/KakaoTalk_logo.svg/240px-KakaoTalk_logo.svg.png',

        // Social Media
        'facebook'    => 'https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/facebook.png',
        'instagram'   => 'https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/instagram.png',
        'twitter'     => 'https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/twitter.png',
        'tiktok'      => 'https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/tiktok.png',
        'snapchat'    => 'https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/snapchat.png',
        'pinterest'   => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/08/Pinterest-logo.png/240px-Pinterest-logo.png',
        'linkedin'    => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/ca/LinkedIn_logo_initials.png/240px-LinkedIn_logo_initials.png',
        'vk'          => 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f3/VK_Compact_Logo_%282021-present%29.svg/240px-VK_Compact_Logo_%282021-present%29.svg.png',
        'vkontakte'   => 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f3/VK_Compact_Logo_%282021-present%29.svg/240px-VK_Compact_Logo_%282021-present%29.svg.png',
        'reddit'      => 'https://upload.wikimedia.org/wikipedia/en/thumb/5/58/Reddit_logo_new.svg/240px-Reddit_logo_new.svg.png',
        'tumblr'      => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/43/Tumblr.svg/240px-Tumblr.svg.png',
        'twitch'      => 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/26/Twitch_logo.svg/240px-Twitch_logo.svg.png',

        // Google services
        'google'      => 'https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/google.png',
        'gmail'       => 'https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/gmail.png',
        'youtube'     => 'https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/youtube.png',
        'google/youtube/gmail' => 'https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/google.png',
        'googlevoice' => 'https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/google.png',
        'google voice' => 'https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/google.png',

        // AI / Modern Tech
        'openai'      => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/04/ChatGPT_logo.svg/240px-ChatGPT_logo.svg.png',
        'chatgpt'     => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/04/ChatGPT_logo.svg/240px-ChatGPT_logo.svg.png',

        // Shopping/delivery
        'amazon'      => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a9/Amazon_logo.svg/240px-Amazon_logo.svg.png',
        'ebay'        => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/1b/EBay_logo.svg/240px-EBay_logo.svg.png',
        'aliexpress'  => 'https://ae01.alicdn.com/kf/H93b1e5b2df144b5a9f2ab80ee5acca23N.png',
        'doordash'    => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6c/DoorDash_Logo.svg/240px-DoorDash_Logo.svg.png',
        'ubereats'    => 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/76/Uber_Eats_2020_logo.svg/240px-Uber_Eats_2020_logo.svg.png',
        'grubhub'     => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/18/Grubhub_logo_2020.svg/240px-Grubhub_logo_2020.svg.png',
        'postmates'   => 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/7c/Postmates_logo.svg/240px-Postmates_logo.svg.png',

        // Ride/transport
        'uber'        => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/58/Uber_logo_2018.svg/240px-Uber_logo_2018.svg.png',
        'lyft'        => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a0/Lyft_logo.svg/240px-Lyft_logo.svg.png',

        // Finance
        'paypal'      => 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b5/PayPal.svg/240px-PayPal.svg.png',
        'cashapp'     => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c5/Square_Cash_app_logo.svg/240px-Square_Cash_app_logo.svg.png',
        'venmo'       => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6e/Venmo_logo_2021.svg/240px-Venmo_logo_2021.svg.png',
        'zelle'       => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4e/Zelle_logo.svg/240px-Zelle_logo.svg.png',
        'stripe'      => 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/ba/Stripe_Logo%2C_revised_2016.svg/240px-Stripe_Logo%2C_revised_2016.svg.png',
        'revolut'     => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8d/Revolut_logo.svg/240px-Revolut_logo.svg.png',

        // Tech/software
        'microsoft'   => 'https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/microsoft.png',
        'apple'       => 'https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/apple.png',
        'yahoo'       => 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/24/Yahoo%21_logo.svg/240px-Yahoo%21_logo.svg.png',
        'microsoft/outlook' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/df/Microsoft_Office_Outlook_%282018%E2%80%93present%29.svg/240px-Microsoft_Office_Outlook_%282018%E2%80%93present%29.svg.png',
        'outlook'     => 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/df/Microsoft_Office_Outlook_%282018%E2%80%93present%29.svg/240px-Microsoft_Office_Outlook_%282018%E2%80%93present%29.svg.png',
        'netflix'     => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/08/Netflix_2015_logo.svg/240px-Netflix_2015_logo.svg.png',
        'spotify'     => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/19/Spotify_logo_without_text.svg/240px-Spotify_logo_without_text.svg.png',
        'airbnb'      => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/69/Airbnb_Logo_B%C3%A9lo.svg/240px-Airbnb_Logo_B%C3%A9lo.svg.png',
        'zoom'        => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/11/Zoom_Logo_2022.svg/240px-Zoom_Logo_2022.svg.png',
        'steam'       => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/83/Steam_icon_logo.svg/240px-Steam_icon_logo.svg.png',
        'roblox'      => 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/20/Roblox_Logo_2022.svg/240px-Roblox_Logo_2022.svg.png',
        'bumble'      => 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f9/Bumble-yellow-logo.svg/240px-Bumble-yellow-logo.svg.png',
        'tinder'      => 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b4/Tinder_logo.png/240px-Tinder_logo.png',
        'hinge'       => 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/da/Hinge_logo_%28app%29.png/240px-Hinge_logo_%28app%29.png',
        'poshmark'    => 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/33/Poshmark_logo.png/240px-Poshmark_logo.png',
        'crowdtap'    => 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f6/Question_mark_alternate.svg/240px-Question_mark_alternate.svg.png',

        // VerifySMS specific short codes
        'df'          => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6c/DoorDash_Logo.svg/240px-DoorDash_Logo.svg.png',
        'dj'          => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6c/DoorDash_Logo.svg/240px-DoorDash_Logo.svg.png',
        'dm'          => 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/33/Poshmark_logo.png/240px-Poshmark_logo.png',
        'ei'          => 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f9/Bumble-yellow-logo.svg/240px-Bumble-yellow-logo.svg.png',

        // Triumph (DinoMMO)
        'triumph'     => 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f6/Question_mark_alternate.svg/240px-Question_mark_alternate.svg.png',
    ];

    // Try exact service name match
    if (isset($icons[$name])) return $icons[$name];

    // Try service code match
    if ($code && isset($icons[$code])) return $icons[$code];

    // Try partial keyword match in service name
    foreach ($icons as $keyword => $url) {
        if (str_contains($name, $keyword) || str_contains($keyword, $name)) {
            return $url;
        }
    }

    // Comprehensive API Fallback: TigerSMS hosts high-quality icons for almost every single service code (wa, tg, alibaba, acz, etc.)
    // We return this URL, and if it occasionally 404s, the frontend JS `onerror` handler will catch it and swap to the SVG question mark.
    if (!empty($code)) {
        $cleanCode = urlencode(strtolower(trim($code)));
        return "https://tigersms.com/assets/images/services/{$cleanCode}.png";
    }

    // Ultimate fallback: Question mark logo if the brand is unknown (using robust inline SVG)
    return "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23cbd5e1'%3E%3Cpath d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z'/%3E%3C/svg%3E";
}

function getServicePriority(string $serviceName): int {
    $name = strtolower(trim($serviceName));
    static $priorities = [
        'whatsapp' => 1,
        'telegram' => 2,
        'facebook' => 3,
        'instagram' => 4,
        'google' => 5,
        'gmail' => 5,
        'twitter' => 6,
        'x' => 6,
        'tiktok' => 7,
        'snapchat' => 8,
        'tinder' => 9,
        'discord' => 10,
        'apple' => 11,
        'netflix' => 12,
        'microsoft' => 13,
        'amazon' => 14,
        'linkedin' => 15,
        'viber' => 16,
        'line' => 17,
        'paypal' => 18,
        'cashapp' => 19,
        'venmo' => 20,
    ];
    
    foreach ($priorities as $key => $priority) {
        if (strpos($name, $key) !== false) {
            return $priority;
        }
    }
    
    return 999;
}

