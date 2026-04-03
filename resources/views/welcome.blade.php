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
  font-size: clamp(1.8rem, 4vw, 3rem); font-weight: 900; line-height: 1.15;
  color: #111827; margin: 16px 0;
}
.section-sub { color: var(--text-muted); font-size: 1rem; line-height: 1.6; }
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
.container { padding: 0 24px; transition: padding 0.3s; }

@media (max-width: 1200px) {
  .container { max-width: 95%; }
}

@media (max-width: 960px) {
  .container { padding: 0 20px; }
  .hero-grid { grid-template-columns: 1fr; gap: 40px; text-align: center; }
  .hero-text { text-align: center !important; }
  .hero-desc { margin-left: auto; margin-right: auto; font-size: 1rem; }
  .hero-btns { justify-content: center; gap: 12px; }
  .hero-stats { max-width: 450px; margin-left: auto; margin-right: auto; padding-top: 24px; }
  .features-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; }
  .showcase-grid { grid-template-columns: 1fr; gap: 40px; }
  .testimonials-grid { grid-template-columns: 1fr; }
  .footer-grid { grid-template-columns: 1fr 1fr; }
  .hero { min-height: auto; padding: 100px 0 50px; }
  .nav-links { display: none !important; }
  .nav-inner { height: 70px; }
  .nav-logo img { height: 50px !important; }
  .mobile-burger-btn { display: flex !important; }
  .nav-cta .nav-login, .nav-cta .nav-btn { display: none !important; }
  .hero-h1 { font-size: clamp(1.8rem, 7vw, 2.8rem); line-height: 1.2; }
}

@media (max-width: 640px) {
  .container { padding: 0 16px; }
  .section-title { font-size: 1.8rem !important; }
  .hero-h1 { font-size: 1.9rem !important; }
  .hero-stats { 
    display: grid; 
    grid-template-columns: 1fr 1fr; 
    gap: 16px; 
    border-top: 1px solid rgba(124,58,237,0.1);
  }
  .hero-stat { border: none !important; padding: 12px 10px; }
  .stat-val { font-size: 1.6rem; }
  
  .float-badge { transform: scale(0.85); white-space: nowrap; }
  .float-badge-1 { bottom: -10px; inset-inline-start: -10px; }
  .float-badge-2 { top: -10px; inset-inline-end: -10px; }
  
  .features-grid { grid-template-columns: 1fr; }
  .footer-grid { grid-template-columns: 1fr; text-align: center; }
  .footer-socials { justify-content: center; }
  .cta-card { padding: 40px 20px; border-radius: 32px; }
  .cta-h2 { font-size: 1.7rem !important; }
  .hero-btns { flex-direction: column; width: 100%; }
  .hero-btn-main, .hero-btn-sec { width: 100%; padding: 14px 24px; font-size: 15px; }
  .hero { padding-top: 90px; }
}
/* Mobile Menu Overlay */
[x-cloak] { display: none !important; }
.mobile-menu {
  position: fixed; inset: 0; z-index: 9999;
  background-color: white !important; padding: 40px 24px;
  display: flex; flex-direction: column; gap: 32px;
}
.mobile-menu-close {
  position: absolute; top: 24px; right: 24px;
  padding: 12px; background: #f3f4f6; border-radius: 12px;
}

/* Reveal Animation */
.reveal {
  opacity: 0;
  transform: translateY(-40px);
  transition: all 1.2s cubic-bezier(0.22, 1, 0.36, 1);
}
.reveal.active {
  opacity: 1;
  transform: translateY(0);
}

/* Floating Widgets */
.floating-container {
    position: fixed;
    bottom: 30px;
    z-index: 999;
    display: flex;
    flex-direction: column;
    gap: 15px;
}
.float-right { right: 30px; }
.float-left { left: 30px; }

.float-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    text-decoration: none;
    border: none;
}
.float-btn:hover { transform: scale(1.1) translateY(-5px); }

.whatsapp-btn {
    background: linear-gradient(135deg, #25D366, #128C7E);
    color: white;
}
.whatsapp-pulse {
    position: absolute;
    width: 100%;
    height: 100%;
    background: #25D366;
    border-radius: 50%;
    z-index: -1;
    animation: pulse-green 2s infinite;
}

@keyframes pulse-green {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 15px rgba(37, 211, 102, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(37, 211, 102, 0); }
}

.social-trigger {
    background: white;
    color: var(--purple);
    font-size: 24px;
}
.social-menu {
    display: flex;
    flex-direction: column-reverse;
    gap: 12px;
    margin-bottom: 12px;
}
.social-item {
    width: 50px;
    height: 50px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    color: #4b5563;
    transition: all 0.3s ease;
    transform: translateY(20px);
    opacity: 0;
}
.social-item:hover { background: var(--purple); color: white; }
.social-menu-open .social-item {
    transform: translateY(0);
    opacity: 1;
}

@media (max-width: 768px) {
    .float-btn { width: 50px; height: 50px; }
    .floating-container { bottom: 20px; }
    .float-right { right: 20px; }
    .float-left { left: 20px; }
}
</style>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body x-data="{ mobileMenu: false }" class="antialiased">

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
        <!-- Lang Switcher -->
        <div style="display:flex; align-items:center; gap:8px; margin-inline-end:16px; border-inline-end:1px solid #e5e7eb; padding-inline-end:16px;">
          <a href="{{ route('lang.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'gradient-text' : 'text-slate-400' }}" style="font-size:12px; font-weight:800; text-transform:uppercase;">En</a>
          <span style="color:#e5e7eb; font-size:12px;">/</span>
          <a href="{{ route('lang.switch', 'ar') }}" class="{{ app()->getLocale() === 'ar' ? 'gradient-text' : 'text-slate-400' }}" style="font-size:12px; font-weight:800;">عربي</a>
        </div>

        @auth
          <a href="{{ url('/dashboard') }}" class="btn-primary nav-btn">{{ __('Dashboard') }}</a>
        @else
          <a href="{{ route('login') }}" class="nav-login">{{ __('Log in') }}</a>
          @if(Route::has('register'))
            <a href="https://wa.me/201004377580" target="_blank" class="btn-primary nav-btn">{{ __('Start Free Trial') }}</a>
          @endif
        @endauth
        
        <!-- Burger Button -->
        <button x-on:click="mobileMenu = true" class="mobile-burger-btn" style="padding:10px; background:#f3f4f6; border-radius:12px; border:none; display:none; cursor:pointer; align-items:center; justify-content:center;">
          <svg style="width:24px;height:24px;stroke:#4b5563;" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
      </div>
    </div>
  </div>
</nav>

<!-- Mobile Menu Overlay -->
<div x-show="mobileMenu" 
     x-cloak 
     class="mobile-menu"
     style="background-color: white !important; opacity: 1 !important;"
     x-transition:enter="transition ease-out duration-300 transform"
     x-transition:enter-start="translate-x-full"
     x-transition:enter-end="translate-x-0"
     x-transition:leave="transition ease-in duration-200 transform"
     x-transition:leave-start="translate-x-0"
     x-transition:leave-end="translate-x-full">
  <button @click="mobileMenu = false" class="mobile-menu-close">
    <svg style="width:24px;height:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
  </button>
  <div class="nav-logo" style="margin-bottom:32px;">
    <img src="{{ asset('Clinova Logo 2.png') }}" alt="Clinova" style="height:60px;">
  </div>
  <div style="display:flex; flex-direction:column; gap:24px;">
    <a href="#features" @click="mobileMenu = false" style="font-size:20px; font-weight:700; color:#111827;">{{ __('Features') }}</a>
    <a href="#hiw" @click="mobileMenu = false" style="font-size:20px; font-weight:700; color:#111827;">{{ __('How It Works') }}</a>
    <a href="#testimonials" @click="mobileMenu = false" style="font-size:20px; font-weight:700; color:#111827;">{{ __('Testimonials') }}</a>
    <a href="#cta" @click="mobileMenu = false" style="font-size:20px; font-weight:700; color:#111827;">{{ __('Contact') }}</a>
    <hr style="border:none; border-top:1px solid #f3f4f6; margin:10px 0;">
    @auth
      <a href="{{ url('/dashboard') }}" style="font-size:20px; font-weight:700; color:var(--purple);">{{ __('Dashboard') }}</a>
    @else
      <a href="{{ route('login') }}" style="font-size:20px; font-weight:700; color:#111827;">{{ __('Log in') }}</a>
      <a href="https://wa.me/201004377580" target="_blank" class="btn-primary" style="padding:16px; margin-top:10px;">{{ __('Start Free Trial') }}</a>
    @endauth
  </div>
</div>

<!-- HERO -->
<section class="hero reveal">
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
          <a href="https://wa.me/201004377580" target="_blank" class="btn-primary hero-btn-main" style="padding:16px 40px;font-size:16px;">
            {{ __('Start Free Trial') }}
          </a>
          <a href="#hiw" class="btn-outline hero-btn-sec" style="padding:14px 28px;font-size:16px;">
            {{ __('See How It Works') }} &rarr;
          </a>
        </div>
        <div style="margin-top: 12px; font-size: 14px; color: #4b5563; opacity: 0.9;">
          {{ __('Contact support to get your free trial.') }}
        </div>
        <div class="hero-stats">
          <div class="hero-stat">
            <div class="stat-val">50+</div>
            <div class="stat-lbl">{{ __('Doctors') }}</div>
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
          <img src="{{ asset('images/welcome.png') }}" alt="{{ __('Clinova Dashboard') }}">
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


<!-- FEATURES -->
<section class="features reveal" id="features">
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
<section class="showcase reveal">
  <div class="container">
    <div class="showcase-grid">
      <div class="showcase-img-wrap">
        <div class="showcase-glow"></div>
        <div class="showcase-img-card">
          <img src="{{ asset('images/doctor.png') }}" alt="{{ __('Feature Overview') }}">
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
<section class="hiw reveal" id="hiw">
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
<section class="testimonials reveal" id="testimonials">
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
        [__('Dr. Mohamed Ali'), __('General Practitioner'), '⭐⭐⭐⭐⭐', __('The website is honestly wonderful; it changed all the chaos and distraction in the clinic. Bravo Dr. David and Dr. Lauren, best of luck.'), 'M'],
        [__('Dr. Mona Mahmoud'), __('Pediatrician'), '⭐⭐⭐⭐⭐', __('Truly respectable work, it saved me time, effort, and many patient problems in my clinic. Best of luck, doctors.'), 'M'],
        [__('Dr. Essam Hassan'), __('Surgeon'), '⭐⭐⭐⭐⭐', __('I didn\'t expect the site to be this easy and organized, but it turned out to be very comfortable for both the doctor and the assistant.'), 'E'],
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
<section class="cta-section reveal" id="cta">
  <div class="container">
    <div class="cta-card">
      <div class="cta-blob1"></div>
      <div class="cta-blob2"></div>
      <div style="position:relative;z-index:1;">
        <div class="cta-badge">{{ __('Limited Time Offer') }}</div>
        <h2 class="cta-h2">{{ __('Ready to transform your clinic?') }}</h2>
        <p class="cta-sub">{{ __('Contact support to get your free trial.') }}</p>
        <div class="cta-btns">
          <a href="https://wa.me/201004377580" target="_blank" class="cta-btn-white">{{ __('Start Free Trial') }}</a>
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
          <img src="{{ asset('Clinova Logo.png') }}" alt="{{ __('Clinova Logo') }}">
        </div>
        <p class="footer-tagline">{{ __('Transforming medical practice management with modern technology.') }}</p>
        <div class="footer-socials">
          <a href="https://www.facebook.com/share/1Gdv1x8azg/" target="_blank" class="social-btn" title="{{ __('Facebook') }}"><svg fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878V14.89h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg></a>
          <a href="https://www.instagram.com/clinoova?utm_source=qr&igsh=YXFjb29iaWtrM2p3" target="_blank" class="social-btn" title="{{ __('Instagram') }}"><svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.668-.072-4.948-.2-4.353-2.612-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg></a>
          <a href="mailto:clinova252@gmail.com" class="social-btn" title="{{ __('Email Us') }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></a>
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
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const observerOptions = {
      threshold: 0.15
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('active');
        }
      });
    }, observerOptions);

    document.querySelectorAll('.reveal').forEach(el => {
      observer.observe(el);
    });
  });
</script>
<!-- Floating Widgets -->
<!-- Right: WhatsApp -->
<div class="floating-container float-right">
    <a href="https://wa.me/201004377580" target="_blank" class="float-btn whatsapp-btn" title="{{ __('Contact us on WhatsApp') }}">
        <div class="whatsapp-pulse"></div>
        <svg style="width:32px;height:32px;fill:currentColor" viewBox="0 0 24 24">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
        </svg>
    </a>
</div>

<!-- Left: Social Expandable -->
<div class="floating-container float-left" x-data="{ open: false }">
    <div class="social-menu" :class="open ? 'social-menu-open' : ''" x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-10" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
        <a href="https://www.facebook.com/share/1Gdv1x8azg/" target="_blank" class="social-item" style="transition-delay: 0.1s" title="{{ __('Facebook') }}">
            <svg style="width:24px;height:24px;fill:currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
        </a>
        <a href="https://www.instagram.com/clinoova?utm_source=qr&igsh=YXFjb29iaWtrM2p3" target="_blank" class="social-item" style="transition-delay: 0.2s" title="{{ __('Instagram') }}">
            <svg style="width:24px;height:24px;fill:currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.668-.072-4.948-.2-4.353-2.612-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
        </a>
        <a href="mailto:clinova252@gmail.com" class="social-item" style="transition-delay: 0.3s" title="{{ __('Email Us') }}">
            <svg style="width:24px;height:24px;fill:none;stroke:currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </a>
    </div>
    <button @click="open = !open" class="float-btn social-trigger" :style="open ? 'transform: rotate(45deg)' : ''" title="{{ __('Social Media') }}">
        <svg style="width:28px;height:28px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
    </button>
</div>

</body>
</html>
