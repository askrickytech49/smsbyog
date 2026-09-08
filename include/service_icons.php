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
        'whatsapp'    => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6b/WhatsApp.svg/240px-WhatsApp.svg.png',
        'telegram'    => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/82/Telegram_logo.svg/240px-Telegram_logo.svg.png',
        'signal'      => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8d/Signal-Logo.svg/240px-Signal-Logo.svg.png',
        'viber'       => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e2/Viber_logo.svg/240px-Viber_logo.svg.png',
        'wechat'      => 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/73/WeChat_logo.svg/240px-WeChat_logo.svg.png',
        'line'        => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/41/LINE_logo.svg/240px-LINE_logo.svg.png',
        'discord'     => 'https://assets-global.website-files.com/6257adef93867e50d84d30e2/636e0a6918e57475a843f59f_icon_clyde_black_RGB.png',
        'kakao'       => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e3/KakaoTalk_logo.svg/240px-KakaoTalk_logo.svg.png',
        'kakaotalk'   => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e3/KakaoTalk_logo.svg/240px-KakaoTalk_logo.svg.png',

        // Social Media
        'facebook'    => 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b9/2023_Facebook_icon.svg/240px-2023_Facebook_icon.svg.png',
        'instagram'   => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e7/Instagram_logo_2016.svg/240px-Instagram_logo_2016.svg.png',
        'twitter'     => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/ce/X_logo_2023.svg/240px-X_logo_2023.svg.png',
        'tiktok'      => 'https://upload.wikimedia.org/wikipedia/en/thumb/a/a9/TikTok_logo.svg/240px-TikTok_logo.svg.png',
        'snapchat'    => 'https://upload.wikimedia.org/wikipedia/en/thumb/a/ad/Snapchat_logo.svg/240px-Snapchat_logo.svg.png',
        'pinterest'   => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/08/Pinterest-logo.png/240px-Pinterest-logo.png',
        'linkedin'    => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/ca/LinkedIn_logo_initials.png/240px-LinkedIn_logo_initials.png',
        'vk'          => 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f3/VK_Compact_Logo_%282021-present%29.svg/240px-VK_Compact_Logo_%282021-present%29.svg.png',
        'vkontakte'   => 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f3/VK_Compact_Logo_%282021-present%29.svg/240px-VK_Compact_Logo_%282021-present%29.svg.png',
        'reddit'      => 'https://upload.wikimedia.org/wikipedia/en/thumb/5/58/Reddit_logo_new.svg/240px-Reddit_logo_new.svg.png',
        'tumblr'      => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/43/Tumblr.svg/240px-Tumblr.svg.png',
        'twitch'      => 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/26/Twitch_logo.svg/240px-Twitch_logo.svg.png',

        // Google services
        'google'      => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Google_%22G%22_logo.svg/240px-Google_%22G%22_logo.svg.png',
        'gmail'       => 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/7e/Gmail_icon_%282020%29.svg/240px-Gmail_icon_%282020%29.svg.png',
        'youtube'     => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/09/YouTube_full-color_icon_%282017%29.svg/240px-YouTube_full-color_icon_%282017%29.svg.png',
        'google/youtube/gmail' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Google_%22G%22_logo.svg/240px-Google_%22G%22_logo.svg.png',

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
        'microsoft'   => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/44/Microsoft_logo.svg/240px-Microsoft_logo.svg.png',
        'apple'       => 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/fa/Apple_logo_black.svg/240px-Apple_logo_black.svg.png',
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
        'crowdtap'    => 'https://www.google.com/s2/favicons?sz=64&domain=crowdtap.com',

        // VerifySMS specific short codes
        'df'          => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6c/DoorDash_Logo.svg/240px-DoorDash_Logo.svg.png',
        'dj'          => 'https://www.google.com/s2/favicons?sz=64&domain=doordash.com',
        'dm'          => 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/33/Poshmark_logo.png/240px-Poshmark_logo.png',
        'ei'          => 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f9/Bumble-yellow-logo.svg/240px-Bumble-yellow-logo.svg.png',

        // Triumph (DinoMMO)
        'triumph'     => 'https://www.google.com/s2/favicons?sz=64&domain=triumph.com',
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

    // Ultimate fallback: Google favicon API
    // Clean the service name to get a likely domain
    $domain = preg_replace('/[^a-z0-9]/', '', $name);
    return "https://www.google.com/s2/favicons?sz=64&domain={$domain}.com";
}
