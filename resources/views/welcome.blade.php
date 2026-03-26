<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Clinova — {{ __('Smart Clinic Management') }}</title>
<meta name="description" content="{{ __('The all-in-one platform for modern medical practices.') }}">
<link rel="icon" type="image/png" href="{{ asset('Clinova Logo.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
<style>
/* ===== RESET & BASE ===== */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body {
  font-family: {{ app()->getLocale() === 'ar' ? "'Cairo'" : "'Outfit'" }}, sans-serif;
  background: #f8f9ff;
  color: #1a1a2e;
  overflow-x: hidden;
}
a { text-decoration: none; color: inherit; }
img { display: block; max-width: 100%; }

/* ===== CSS VARIABLES ===== */
:root {
  --purple: #7C3AED;
  --indigo: #4338CA;
  --purple-light: #ede9fe;
  --purple-dark: #5B21B6;
  --text-muted: #6b7280;
  --radius-xl: 24px;
  --radius-2xl: 32px;
}

/* ===== UTILITIES ===== */
.container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
.gradient-text {
  background: linear-gradient(135deg, #7C3AED 0%, #4338CA 100%);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
.btn-primary {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  background: linear-gradient(135deg, #7C3AED, #4338CA);
  color: #fff; font-weight: 700; border-radius: 14px; border: none; cursor: pointer;
  transition: all 0.3s ease; text-align: center;
  box-shadow: 0 8px 30px rgba(124,58,237,0.35);
}
.btn-primary:hover { transform: translateY(-3px); box-shadow: 0 16px 40px rgba(124,58,237,0.5); }
.btn-outline {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  background: #fff; color: #374151; font-weight: 700; border-radius: 14px;
  border: 2px solid #e5e7eb; cursor: pointer; transition: all 0.3s ease; text-align: center;
}
.btn-outline:hover { border-color: #c4b5fd; background: var(--purple-light); color: var(--purple); }
.section-badge {
  display: inline-flex; align-items: center; gap: 8px;
  background: var(--purple-light); color: var(--purple);
  font-size: 11px; font-weight: 800; padding: 6px 16px; border-radius: 999px;
  border: 1px solid #c4b5fd; letter-spacing: 0.08em; text-transform: uppercase;
}
.section-title {
  font-size: clamp(2rem, 4vw, 3rem); font-weight: 900; line-height: 1.15;
  color: #111827; margin: 16px 0;
}
.section-sub { color: var(--text-muted); font-size: 1.1rem; line-height: 1.7; }
.text-center { text-align: center; }

/* ===== NAVBAR ===== */
#navbar {
  position: fixed; top: 0; width: 100%; z-index: 1000;
  background: rgba(255,255,255,0.9); backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(124,58,237,0.08);
  transition: box-shadow 0.3s ease;
}
#navbar.scrolled { box-shadow: 0 4px 30px rgba(0,0,0,0.08); }
.nav-inner {
  display: flex; align-items: center; justify-content: space-between;
  height: 95px; gap: 24px;
}
.nav-logo { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
.nav-logo img { height: 94px; width: auto; object-fit: contain; }
.nav-links { display: flex; align-items: center; gap: 32px; }
.nav-links a {
  font-size: 14px; font-weight: 600; color: #4b5563;
  transition: color 0.2s;
}
.nav-links a:hover { color: var(--purple); }
.nav-cta { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
.nav-login { font-size: 14px; font-weight: 600; color: #374151; transition: color 0.2s; }
.nav-login:hover { color: var(--purple); }
.nav-btn { padding: 10px 22px; font-size: 14px; }

/* ===== HERO ===== */
.hero {
  padding: 120px 0 80px;
  background: linear-gradient(140deg, #fdfcff 0%, #ede9fe 35%, #e0e7ff 65%, #f0f4ff 100%);
  position: relative; overflow: hidden; min-height: 100vh;
  display: flex; align-items: center;
}
.hero-blob1 {
  position: absolute; top: -120px; right: -150px;
  width: 600px; height: 600px; border-radius: 50%;
  background: radial-gradient(circle, rgba(167,139,250,0.3) 0%, transparent 70%);
  filter: blur(60px); pointer-events: none;
}
.hero-blob2 {
  position: absolute; bottom: -100px; left: -100px;
  width: 500px; height: 500px; border-radius: 50%;
  background: radial-gradient(circle, rgba(99,102,241,0.2) 0%, transparent 70%);
  filter: blur(60px); pointer-events: none;
}
[dir="rtl"] .hero-blob1 { right: auto; left: -150px; }
[dir="rtl"] .hero-blob2 { left: auto; right: -100px; }
.hero-grid {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 60px; align-items: center; position: relative; z-index: 1;
}
.hero-text { }
[dir="rtl"] .hero-text { text-align: right; }
[dir="ltr"] .hero-text { text-align: left; }
.hero-badge { margin-bottom: 24px; }
.hero-h1 {
  font-size: clamp(2.4rem, 4.5vw, 3.8rem);
  font-weight: 900; line-height: 1.1; color: #0f0a2e; margin-bottom: 20px;
}
.hero-desc {
  font-size: 1.1rem; color: #4b5563; line-height: 1.8;
  max-width: 480px; margin-bottom: 36px;
}
.hero-btns {
  display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 48px;
}
.hero-btn-main { padding: 16px 36px; font-size: 16px; }
.hero-btn-sec { padding: 16px 28px; font-size: 16px; }
.hero-stats {
  display: flex; gap: 0; border-top: 1px solid rgba(124,58,237,0.15);
  padding-top: 32px;
}
.hero-stat {
  flex: 1; text-align: center; padding: 0 20px;
  border-inline-end: 1px solid rgba(124,58,237,0.15);
}
.hero-stat:last-child { border-inline-end: none; }
.stat-val {
  font-size: 2rem; font-weight: 900; line-height: 1;
  background: linear-gradient(135deg, #7C3AED, #4338CA);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
.stat-lbl { font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.1em; margin-top: 6px; }
.hero-image-wrap { position: relative; }
.hero-img-card {
  border-radius: var(--radius-2xl); overflow: hidden;
  box-shadow: 0 30px 80px rgba(124,58,237,0.25);
  border: 4px solid rgba(255,255,255,0.8);
  position: relative;
}
.hero-img-card img { width: 100%; height: 480px; object-fit: cover; }
.hero-glow {
  position: absolute; inset: -20px;
  background: linear-gradient(135deg, rgba(124,58,237,0.15), rgba(99,102,241,0.15));
  border-radius: 40px; filter: blur(30px); z-index: -1;
}
.float-badge {
  position: absolute; background: #fff;
  border-radius: 18px; padding: 12px 18px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.12);
  border: 1px solid #f3f4f6;
  display: flex; align-items: center; gap: 10px;
  animation: floating 4s ease-in-out infinite;
}
.float-badge-1 { bottom: -16px; inset-inline-start: -16px; }
.float-badge-2 { top: -16px; inset-inline-end: -16px; animation-delay: 2s; }
.float-badge-icon {
  width: 40px; height: 40px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.float-badge-icon svg { width: 20px; height: 20px; }
.float-badge-lbl { font-size: 11px; color: #9ca3af; font-weight: 600; }
.float-badge-val { font-size: 17px; font-weight: 900; color: #111827; }
@keyframes floating {
  0%,100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

/* ===== TRUST BAR ===== */
.trust-bar {
  background: #fff; border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6;
  padding: 24px 0; text-align: center;
}
.trust-label { font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 20px; }
.trust-logos { display: flex; justify-content: center; align-items: center; gap: 40px; flex-wrap: wrap; }
.trust-logo {
  display: flex; align-items: center; gap: 8px;
  font-size: 13px; font-weight: 700; color: #d1d5db;
  transition: color 0.3s;
}
.trust-logo:hover { color: var(--purple); }
.trust-logo span:first-child { font-size: 24px; }

/* ===== FEATURES ===== */
.features { padding: 100px 0; background: #f8f9ff; }
.section-header { margin-bottom: 64px; }
.features-grid {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;
}
.feature-card {
  background: #fff; border-radius: var(--radius-xl); padding: 36px 32px;
  border: 1px solid #e5e7eb;
  transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}
.feature-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 24px 60px rgba(124,58,237,0.15);
  border-color: #c4b5fd;
}
.feature-card.featured {
  background: linear-gradient(135deg, #7C3AED 0%, #4338CA 100%);
  color: #fff; border-color: transparent;
}
.feature-icon {
  width: 60px; height: 60px; border-radius: 16px;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 20px;
}
.feature-icon svg { width: 28px; height: 28px; }
.feature-name { font-size: 1.15rem; font-weight: 800; margin-bottom: 10px; color: #111827; }
.feature-card.featured .feature-name { color: #fff; }
.feature-desc { font-size: 0.9rem; color: #6b7280; line-height: 1.7; }
.feature-card.featured .feature-desc { color: rgba(255,255,255,0.8); }

/* ===== SHOWCASE ===== */
.showcase { padding: 100px 0; background: #fff; overflow: hidden; }
.showcase-grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: 72px; align-items: center;
}
.showcase-img-wrap { position: relative; }
.showcase-img-card {
  border-radius: var(--radius-2xl); overflow: hidden;
  box-shadow: 0 20px 60px rgba(124,58,237,0.18);
  border: 4px solid #fff;
}
.showcase-img-card img { width: 100%; object-fit: cover; }
.showcase-glow {
  position: absolute; inset: -20px;
  background: linear-gradient(135deg, rgba(124,58,237,0.1), rgba(99,102,241,0.1));
  border-radius: 50px; filter: blur(40px); z-index: -1;
}
.showcase-checks { margin-top: 32px; display: flex; flex-direction: column; gap: 14px; }
.check-item {
  display: flex; align-items: flex-start; gap: 14px;
  padding: 16px 20px; border-radius: 16px;
  background: #f9fafb; border: 1px solid transparent;
  transition: all 0.3s;
}
.check-item:hover { background: var(--purple-light); border-color: #c4b5fd; }
.check-icon {
  width: 36px; height: 36px; border-radius: 10px;
  background: linear-gradient(135deg, #7C3AED, #4338CA);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; margin-top: 2px;
  box-shadow: 0 4px 12px rgba(124,58,237,0.3);
}
.check-icon svg { width: 16px; height: 16px; stroke: #fff; }
.check-title { font-weight: 800; color: #111827; margin-bottom: 2px; font-size: 0.95rem; }
.check-desc { font-size: 0.85rem; color: #6b7280; }

/* ===== HOW IT WORKS ===== */
.hiw { padding: 100px 0; background: #f8f9ff; }
.hiw-steps { display: flex; flex-direction: column; gap: 20px; margin-top: 56px; }
.hiw-step {
  display: flex; align-items: flex-start; gap: 20px;
  background: #fff; border-radius: var(--radius-xl); padding: 28px 32px;
  border: 1px solid #e5e7eb; transition: all 0.3s;
}
.hiw-step:hover { border-color: #c4b5fd; box-shadow: 0 8px 30px rgba(124,58,237,0.1); }
.hiw-num {
  width: 52px; height: 52px; border-radius: 50%;
  background: linear-gradient(135deg, #7C3AED, #4338CA);
  color: #fff; font-size: 18px; font-weight: 900;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  box-shadow: 0 6px 20px rgba(124,58,237,0.35);
  transition: transform 0.3s;
}
.hiw-step:hover .hiw-num { transform: scale(1.1); }
.hiw-content { flex: 1; }
.hiw-title { font-size: 1.05rem; font-weight: 800; color: #111827; margin-bottom: 6px; }
.hiw-desc { font-size: 0.9rem; color: #6b7280; line-height: 1.7; }

/* ===== TESTIMONIALS ===== */
.testimonials { padding: 100px 0; background: #fff; }
.testimonials-grid {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 56px;
}
.testi-card {
  background: #f9fafb; border-radius: var(--radius-xl); padding: 32px;
  border: 1px solid #e5e7eb; transition: all 0.3s;
}
.testi-card:hover { background: #fff; box-shadow: 0 16px 50px rgba(124,58,237,0.12); border-color: #c4b5fd; }
.testi-stars { font-size: 20px; margin-bottom: 16px; }
.testi-quote { font-size: 0.95rem; color: #374151; line-height: 1.8; margin-bottom: 24px; font-style: italic; }
.testi-author { display: flex; align-items: center; gap: 12px; }
.testi-avatar {
  width: 44px; height: 44px; border-radius: 50%;
  background: linear-gradient(135deg, #7C3AED, #4338CA);
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 18px; font-weight: 900; flex-shrink: 0;
}
.testi-name { font-weight: 800; color: #111827; font-size: 0.95rem; }
.testi-role { font-size: 0.8rem; color: #9ca3af; margin-top: 2px; }

/* ===== CTA ===== */
.cta-section { padding: 80px 0; }
.cta-card {
  background: linear-gradient(135deg, #7C3AED 0%, #4338CA 100%);
  border-radius: 40px; padding: 80px 60px;
  text-align: center; position: relative; overflow: hidden;
  box-shadow: 0 30px 80px rgba(124,58,237,0.4);
}
.cta-blob1 {
  position: absolute; top: -80px; left: -80px;
  width: 300px; height: 300px; border-radius: 50%;
  background: rgba(255,255,255,0.08); filter: blur(40px);
}
.cta-blob2 {
  position: absolute; bottom: -80px; right: -80px;
  width: 300px; height: 300px; border-radius: 50%;
  background: rgba(255,255,255,0.08); filter: blur(40px);
}
.cta-badge {
  display: inline-block; background: rgba(255,255,255,0.2);
  color: #fff; font-size: 11px; font-weight: 700;
  padding: 6px 18px; border-radius: 999px; border: 1px solid rgba(255,255,255,0.3);
  text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 20px;
}
.cta-h2 {
  font-size: clamp(2rem, 3.5vw, 3rem); font-weight: 900;
  color: #fff; line-height: 1.2; margin-bottom: 16px;
}
.cta-sub { color: rgba(255,255,255,0.8); font-size: 1.1rem; margin-bottom: 36px; }
.cta-btns { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }
.cta-btn-white {
  display: inline-flex; align-items: center; gap: 8px;
  background: #fff; color: var(--purple); font-weight: 800; font-size: 15px;
  padding: 16px 36px; border-radius: 14px;
  transition: all 0.3s; box-shadow: 0 4px 20px rgba(0,0,0,0.2);
}
.cta-btn-white:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(0,0,0,0.25); }
.cta-btn-outline {
  display: inline-flex; align-items: center; gap: 8px;
  background: transparent; color: #fff; font-weight: 700; font-size: 15px;
  padding: 14px 32px; border-radius: 14px;
  border: 2px solid rgba(255,255,255,0.4); transition: all 0.3s;
}
.cta-btn-outline:hover { background: rgba(255,255,255,0.15); border-color: rgba(255,255,255,0.6); }

/* ===== FOOTER ===== */
footer {
  background: #0a0619; padding: 72px 0 32px; color: #fff;
}
.footer-grid {
  display: grid; grid-template-columns: 1.5fr 1fr 1fr 1fr; gap: 48px;
  padding-bottom: 56px; border-bottom: 1px solid rgba(255,255,255,0.08);
  margin-bottom: 32px;
}
.footer-brand { }
.footer-logo { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
.footer-logo img { height: 40px; width: auto; object-fit: contain; }
.footer-tagline { color: #6b7280; font-size: 0.9rem; line-height: 1.7; margin-bottom: 24px; }
.footer-socials { display: flex; gap: 10px; }
.social-btn {
  width: 36px; height: 36px; border-radius: 10px;
  background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);
  display: flex; align-items: center; justify-content: center;
  color: #9ca3af; transition: all 0.3s;
}
.social-btn:hover { background: var(--purple); color: #fff; border-color: var(--purple); }
.social-btn svg { width: 16px; height: 16px; }
.footer-col h5 {
  font-size: 11px; font-weight: 800; color: #a78bfa;
  text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 20px;
}
.footer-col ul { list-style: none; display: flex; flex-direction: column; gap: 12px; }
.footer-col a { color: #6b7280; font-size: 14px; transition: color 0.2s; }
.footer-col a:hover { color: #fff; }
.footer-bottom {
  display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;
  color: #4b5563; font-size: 13px;
}
.status-dot { display: flex; align-items: center; gap: 6px; }
.dot-green {
  width: 8px; height: 8px; border-radius: 50%; background: #10b981;
  box-shadow: 0 0 0 3px rgba(16,185,129,0.2);
  animation: pulse 2s infinite;
}
@keyframes pulse { 0%,100% { box-shadow: 0 0 0 3px rgba(16,185,129,0.2); } 50% { box-shadow: 0 0 0 6px rgba(16,185,129,0.1); } }

/* ===== RESPONSIVE ===== */
@media (max-width: 960px) {
  .hero-grid { grid-template-columns: 1fr; gap: 48px; }
  .hero-text { text-align: center !important; }
  .hero-desc { margin-left: auto; margin-right: auto; }
  .hero-btns { justify-content: center; }
  .hero-stats { max-width: 380px; margin-left: auto; margin-right: auto; }
  .features-grid { grid-template-columns: repeat(2, 1fr); }
  .showcase-grid { grid-template-columns: 1fr; gap: 48px; }
  .testimonials-grid { grid-template-columns: 1fr; }
  .footer-grid { grid-template-columns: 1fr 1fr; }
  .nav-links { display: none; }
  .hero { min-height: auto; padding: 100px 0 60px; }
}
@media (max-width: 600px) {
  .features-grid { grid-template-columns: 1fr; }
  .footer-grid { grid-template-columns: 1fr; }
  .cta-card { padding: 48px 24px; border-radius: 24px; }
  .hero-btns { flex-direction: column; }
  .hero-btn-main, .hero-btn-sec { width: 100%; padding: 14px 24px; }
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav id="navbar">
  <div class="container">
    <div class="nav-inner">
      <a href="/" class="nav-logo">
        <img src="{{ asset('Clinova Logo 2.png') }}" alt="Clinova">
      </a>
      <div class="nav-links">
        <a href="#features">{{ __('Features') }}</a>
        <a href="#hiw">{{ __('How It Works') }}</a>
        <a href="#testimonials">{{ __('Testimonials') }}</a>
        <a href="#cta">{{ __('Contact') }}</a>
      </div>
      <div class="nav-cta">
        @auth
          <a href="{{ url('/dashboard') }}" class="btn-primary nav-btn">{{ __('Dashboard') }}</a>
        @else
          <a href="{{ route('login') }}" class="nav-login">{{ __('Log in') }}</a>
          @if(Route::has('register'))
            <a href="{{ route('register') }}" class="btn-primary nav-btn">{{ __('Start Free Trial') }}</a>
          @endif
        @endauth
      </div>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-blob1"></div>
  <div class="hero-blob2"></div>
  <div class="container">
    <div class="hero-grid">

      <!-- Left: Text -->
      <div class="hero-text">
        <div class="hero-badge">
          <span class="section-badge">
            <span style="width:8px;height:8px;border-radius:50%;background:#7C3AED;animation:pulse 2s infinite;display:inline-block;"></span>
            {{ __('Clinova — Smart Clinic Platform') }}
          </span>
        </div>
        <h1 class="hero-h1">
          {{ __('Manage Your Clinic') }}<br>
          <span class="gradient-text">{{ __('Smarter. Faster. Better.') }}</span>
        </h1>
        <p class="hero-desc">
          {{ __('The all-in-one platform for modern medical practices. Streamline appointments, patient records, and financial analytics with elegance and ease.') }}
        </p>
        <div class="hero-btns">
          <a href="{{ route('register') }}" class="btn-primary hero-btn-main" style="padding:16px 40px;font-size:16px;">
            {{ __('Start Free Trial') }}
          </a>
          <a href="#hiw" class="btn-outline hero-btn-sec" style="padding:14px 28px;font-size:16px;">
            {{ __('See How It Works') }} &rarr;
          </a>
        </div>
        <div class="hero-stats">
          <div class="hero-stat">
            <div class="stat-val">500+</div>
            <div class="stat-lbl">{{ __('Doctors') }}</div>
          </div>
          <div class="hero-stat">
            <div class="stat-val">50k+</div>
            <div class="stat-lbl">{{ __('Patients') }}</div>
          </div>
          <div class="hero-stat">
            <div class="stat-val">99.9%</div>
            <div class="stat-lbl">{{ __('Uptime') }}</div>
          </div>
        </div>
      </div>

      <!-- Right: Image -->
      <div class="hero-image-wrap">
        <div class="hero-glow"></div>
        <div class="hero-img-card">
          <img src="{{ asset('images/hero.png') }}" alt="{{ __('Clinova Dashboard') }}">
        </div>
        <div class="float-badge float-badge-1">
          <div class="float-badge-icon" style="background:linear-gradient(135deg,#7C3AED,#4338CA);">
            <svg fill="none" stroke="#fff" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div>
            <div class="float-badge-lbl">{{ __('Appointments Today') }}</div>
            <div class="float-badge-val">24 {{ __('Booked') }}</div>
          </div>
        </div>
        <div class="float-badge float-badge-2">
          <div class="float-badge-icon" style="background:#10b981;">
            <svg fill="none" stroke="#fff" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
          </div>
          <div>
            <div class="float-badge-lbl">{{ __('Monthly Revenue') }}</div>
            <div class="float-badge-val">+18%</div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- TRUST BAR -->
<div class="trust-bar">
  <div class="container">
    <div class="trust-label">{{ __('Trusted by leading clinics and medical centers') }}</div>
    <div class="trust-logos">
      @foreach([['🏥', __('General Clinic')], ['🦷', __('Dental Center')], ['👁️', __('Eye Clinic')], ['🫀', __('Heart Center')], ['🧠', __('Neurology')], ['🦴', __('Orthopedics')]] as $logo)
      <div class="trust-logo">
        <span>{{ $logo[0] }}</span>
        <span>{{ $logo[1] }}</span>
      </div>
      @endforeach
    </div>
  </div>
</div>

<!-- FEATURES -->
<section class="features" id="features">
  <div class="container">
    <div class="section-header text-center">
      <span class="section-badge">{{ __('Core Modules') }}</span>
      <h2 class="section-title">
        {{ __('Everything you need to run') }}<br>
        <span class="gradient-text">{{ __('a successful practice') }}</span>
      </h2>
      <p class="section-sub">{{ __('Designed by medical professionals for efficiency and clinical excellence.') }}</p>
    </div>
    <div class="features-grid">

      <div class="feature-card">
        <div class="feature-icon" style="background:#ede9fe;">
          <svg fill="none" stroke="#7C3AED" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div class="feature-name">{{ __('Smart Appointments') }}</div>
        <div class="feature-desc">{{ __('Automated booking, queue management, and real-time availability tracking to eliminate waiting rooms.') }}</div>
      </div>

      <div class="feature-card">
        <div class="feature-icon" style="background:#e0e7ff;">
          <svg fill="none" stroke="#4338CA" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <div class="feature-name">{{ __('Electronic Records') }}</div>
        <div class="feature-desc">{{ __('Secure, digital patient histories with file uploads for labs and investigations. Accessible anywhere, anytime.') }}</div>
      </div>

      <div class="feature-card">
        <div class="feature-icon" style="background:#d1fae5;">
          <svg fill="none" stroke="#059669" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        </div>
        <div class="feature-name">{{ __('Finance Insights') }}</div>
        <div class="feature-desc">{{ __('Comprehensive revenue dashboards, billing management, and financial growth analytics built-in.') }}</div>
      </div>

      <div class="feature-card">
        <div class="feature-icon" style="background:#fce7f3;">
          <svg fill="none" stroke="#db2777" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <div class="feature-name">{{ __('Multi-Role Access') }}</div>
        <div class="feature-desc">{{ __('Doctor, Secretary, and Admin roles — each with a tailored dashboard, permissions, and workflow.') }}</div>
      </div>

      <div class="feature-card">
        <div class="feature-icon" style="background:#fef3c7;">
          <svg fill="none" stroke="#d97706" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </div>
        <div class="feature-name">{{ __('Patient Profiles') }}</div>
        <div class="feature-desc">{{ __('Complete patient history, visit records, medical files, tags, and rapid booking — all in one place.') }}</div>
      </div>

      <div class="feature-card featured">
        <div class="feature-icon" style="background:rgba(255,255,255,0.2);">
          <svg fill="none" stroke="#fff" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        </div>
        <div class="feature-name">{{ __('Secure & Reliable') }}</div>
        <div class="feature-desc">{{ __('Enterprise-grade security with encrypted data storage, role-based access, and 99.9% uptime guarantee.') }}</div>
      </div>

    </div>
  </div>
</section>

<!-- SHOWCASE -->
<section class="showcase">
  <div class="container">
    <div class="showcase-grid">
      <div class="showcase-img-wrap">
        <div class="showcase-glow"></div>
        <div class="showcase-img-card">
          <img src="{{ asset('images/features.png') }}" alt="{{ __('Feature Overview') }}">
        </div>
      </div>
      <div>
        <span class="section-badge">{{ __('Why Clinova?') }}</span>
        <h2 class="section-title">
          {{ __('Your Vision,') }}<br>
          <span class="gradient-text">{{ __('Our Platform.') }}</span>
        </h2>
        <p class="section-sub" style="margin-bottom:8px;">
          {{ __('Clinova was built from the ground up by a team of developers working closely with real doctors. Every feature is designed to minimize admin work and maximize time with patients.') }}
        </p>
        <div class="showcase-checks">
          @foreach([
            [__('Instant Setup'), __('Get your clinic online in under 10 minutes. No IT team needed.')],
            [__('Arabic & English'), __('Full bilingual support with proper RTL layout for Arabic users.')],
            [__('Multi-Doctor Support'), __('Manage multiple doctors, schedules, and independent income reports.')],
          ] as $c)
          <div class="check-item">
            <div class="check-icon">
              <svg viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/></svg>
            </div>
            <div>
              <div class="check-title">{{ $c[0] }}</div>
              <div class="check-desc">{{ $c[1] }}</div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="hiw" id="hiw">
  <div class="container">
    <div class="text-center">
      <span class="section-badge">{{ __('Simple Process') }}</span>
      <h2 class="section-title">
        {{ __('How to get started') }}<br>
        <span class="gradient-text">{{ __('with Clinova?') }}</span>
      </h2>
    </div>
    <div class="hiw-steps" style="max-width:800px;margin-left:auto;margin-right:auto;">
      @php $steps = [
        ['01', __('Create Your Account'), __('Register in seconds. No credit card required. Set up your clinic profile with your name, specialty, and fees.')],
        ['02', __('Customize Your Settings'), __('Configure consultation fees, add doctors and secretary accounts, and personalize the system to your workflow.')],
        ['03', __('Add Patients & Book Appointments'), __('Start adding patient records and booking appointments. The system handles the waitlist, visit records, and billing automatically.')],
        ['04', __('Track & Grow'), __('Monitor your clinic performance, revenue trends, and patient statistics from a beautiful analytics dashboard.')],
      ]; @endphp
      @foreach($steps as $s)
      <div class="hiw-step">
        <div class="hiw-num">{{ $s[0] }}</div>
        <div class="hiw-content">
          <div class="hiw-title">{{ $s[1] }}</div>
          <div class="hiw-desc">{{ $s[2] }}</div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="testimonials" id="testimonials">
  <div class="container">
    <div class="text-center">
      <span class="section-badge">{{ __('Testimonials') }}</span>
      <h2 class="section-title">
        {{ __('What doctors say') }}<br>
        <span class="gradient-text">{{ __('about Clinova') }}</span>
      </h2>
    </div>
    <div class="testimonials-grid">
      @foreach([
        ['Dr. Ahmed Hassan', __('General Practitioner'), '⭐⭐⭐⭐⭐', __('Clinova completely transformed how I run my clinic. The waitlist management is flawless, and patients love the experience.'), 'A'],
        ['Dr. Sara Mohammed', __('Pediatrician'), '⭐⭐⭐⭐⭐', __('The Arabic interface is perfect. My team adapted to it instantly. The financial reports save me hours every month.'), 'S'],
        ['Dr. Khaled Ibrahim', __('Orthopedic Surgeon'), '⭐⭐⭐⭐⭐', __('I manage 3 doctors in my clinic now. Clinova handles all the complexity—separate income, separate waitlists—effortlessly.'), 'K'],
      ] as $t)
      <div class="testi-card">
        <div class="testi-stars">{{ $t[2] }}</div>
        <p class="testi-quote">"{{ $t[3] }}"</p>
        <div class="testi-author">
          <div class="testi-avatar">{{ $t[4] }}</div>
          <div>
            <div class="testi-name">{{ $t[0] }}</div>
            <div class="testi-role">{{ $t[1] }}</div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section" id="cta">
  <div class="container">
    <div class="cta-card">
      <div class="cta-blob1"></div>
      <div class="cta-blob2"></div>
      <div style="position:relative;z-index:1;">
        <div class="cta-badge">{{ __('Limited Time Offer') }}</div>
        <h2 class="cta-h2">{{ __('Ready to transform your clinic?') }}</h2>
        <p class="cta-sub">{{ __('Join hundreds of doctors who already use Clinova to deliver better care, faster.') }}</p>
        <div class="cta-btns">
          <a href="{{ route('register') }}" class="cta-btn-white">{{ __('Start Free Trial') }}</a>
          <a href="{{ route('login') }}" class="cta-btn-outline">{{ __('Sign In') }}</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <div class="footer-logo">
          <img src="{{ asset('Clinova Logo.png') }}" alt="Clinova">
        </div>
        <p class="footer-tagline">{{ __('Transforming medical practice management with modern technology.') }}</p>
        <div class="footer-socials">
          <a href="#" class="social-btn"><svg fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878V14.89h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg></a>
          <a href="#" class="social-btn"><svg fill="currentColor" viewBox="0 0 24 24"><path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"/></svg></a>
          <a href="#" class="social-btn"><svg fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg></a>
        </div>
      </div>
      <div class="footer-col">
        <h5>{{ __('Product') }}</h5>
        <ul>
          <li><a href="#features">{{ __('Features') }}</a></li>
          <li><a href="#hiw">{{ __('How It Works') }}</a></li>
          <li><a href="{{ route('login') }}">{{ __('Log in') }}</a></li>
          <li><a href="{{ route('register') }}">{{ __('Register') }}</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h5>{{ __('Company') }}</h5>
        <ul>
          <li><a href="#">{{ __('About') }}</a></li>
          <li><a href="#">{{ __('Blog') }}</a></li>
          <li><a href="#cta">{{ __('Contact') }}</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h5>{{ __('Legal') }}</h5>
        <ul>
          <li><a href="#">{{ __('Privacy') }}</a></li>
          <li><a href="#">{{ __('Terms') }}</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© {{ date('Y') }} Clinova. {{ __('All rights reserved.') }}</span>
      <div class="status-dot">
        <div class="dot-green"></div>
        <span style="color:#10b981;font-weight:600;">{{ __('All systems operational') }}</span>
      </div>
    </div>
  </div>
</footer>

<script>
const nav = document.getElementById('navbar');
window.addEventListener('scroll', () => {
  nav.classList.toggle('scrolled', window.scrollY > 40);
});
</script>
</body>
</html>
