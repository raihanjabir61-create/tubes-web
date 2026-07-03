<?php
session_start();
require_once __DIR__ . '/config/db.php';

$conn = get_db_connection();

// Fetch Stats
$res_donors = mysqli_query($conn, "SELECT COUNT(*) as total FROM tabel_users WHERE role = 'user'");
$row_donors = mysqli_fetch_assoc($res_donors);
$total_donors = $row_donors['total'] ?? 0;

$res_books = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM tabel_donasi WHERE status = 'diterima'");
$row_books = mysqli_fetch_assoc($res_books);
$total_books = $row_books['total'] ?? 0;
if ($total_books === null) $total_books = 0;

$res_titles = mysqli_query($conn, "SELECT COUNT(*) as total FROM tabel_donasi WHERE status = 'diterima'");
$row_titles = mysqli_fetch_assoc($res_titles);
$total_titles = $row_titles['total'] ?? 0;

// Fetch Latest Accepted Books for Gallery
$gallery_query = "SELECT d.*, u.nama as donor_nama FROM tabel_donasi d 
                  JOIN tabel_users u ON d.id_user = u.id 
                  WHERE d.status = 'diterima' 
                  ORDER BY d.tanggal DESC LIMIT 8";
$gallery_res = mysqli_query($conn, $gallery_query);

// Fetch recently pending for activity feed
$recent_query = "SELECT d.judul_buku, u.nama, d.tanggal FROM tabel_donasi d 
                 JOIN tabel_users u ON d.id_user = u.id 
                 ORDER BY d.tanggal DESC LIMIT 3";
$recent_res = mysqli_query($conn, $recent_query);
$recent_books = [];
while($r = mysqli_fetch_assoc($recent_res)) {
    $recent_books[] = $r;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonasiBuku — Berbagi Pengetahuan, Terangi Masa Depan</title>
    <meta name="description" content="Platform donasi buku layak baca terpercaya. Donasikan buku Anda untuk menyokong sarana belajar anak-anak di penjuru nusantara.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* =========================================
           LANDING PAGE — PREMIUM REDESIGN
        ========================================= */

        /* ---- NAV: Purple start → White on scroll ---- */
        header.navbar-container {
            background: linear-gradient(130deg, hsl(224,60%,10%) 0%, hsl(256,60%,18%) 55%, hsl(280,55%,22%) 100%);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            transition: background 0.35s ease, box-shadow 0.35s ease;
        }

        /* White text on dark navbar */
        header.navbar-container:not(.scrolled) .nav-brand {
            color: #fff;
        }
        header.navbar-container:not(.scrolled) .nav-brand i {
            background: linear-gradient(135deg, #fff, rgba(255,255,255,0.7));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        header.navbar-container:not(.scrolled) .nav-item a {
            color: rgba(255,255,255,0.85);
        }
        header.navbar-container:not(.scrolled) .nav-item a:hover {
            color: #fff;
        }
        header.navbar-container:not(.scrolled) .nav-item a::after {
            background-color: #fff;
        }
        header.navbar-container:not(.scrolled) .mobile-nav-toggle {
            color: #fff;
        }
        /* Masuk button: ghost white */
        header.navbar-container:not(.scrolled) .btn-secondary {
            background: rgba(255,255,255,0.12);
            color: #fff;
            border-color: rgba(255,255,255,0.3);
        }
        header.navbar-container:not(.scrolled) .btn-secondary:hover {
            background: rgba(255,255,255,0.22);
        }

        /* ---- Scrolled: white frosted glass ---- */
        header.navbar-container.scrolled {
            background: rgba(255,255,255,0.92) !important;
            border-bottom: 1px solid var(--border-color) !important;
            box-shadow: var(--shadow-sm);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }
        header.navbar-container.scrolled .nav-brand { color: var(--primary); }
        header.navbar-container.scrolled .nav-brand i {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        header.navbar-container.scrolled .nav-item a { color: var(--text-main); }
        header.navbar-container.scrolled .nav-item a:hover { color: var(--primary); }
        header.navbar-container.scrolled .nav-item a::after { background-color: var(--primary); }
        header.navbar-container.scrolled .mobile-nav-toggle { color: var(--text-main); }
        header.navbar-container.scrolled .btn-secondary {
            background: white;
            color: var(--primary);
            border-color: var(--border-color);
        }

        /* ---- HERO ---- */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(130deg, hsl(224,60%,10%) 0%, hsl(256,60%,18%) 55%, hsl(280,55%,22%) 100%);
            position: relative;
            overflow: hidden;
            padding: 7rem 2rem 4rem;
        }

        /* Animated background orbs */
        .hero::before, .hero::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.35;
            animation: float 8s ease-in-out infinite alternate;
        }
        .hero::before {
            width: 520px; height: 520px;
            background: radial-gradient(circle, hsl(256,82%,65%), transparent);
            top: -120px; left: -80px;
        }
        .hero::after {
            width: 400px; height: 400px;
            background: radial-gradient(circle, hsl(190,80%,60%), transparent);
            bottom: -100px; right: 5%;
            animation-delay: -4s;
        }

        @keyframes float {
            0%   { transform: translateY(0) scale(1); }
            100% { transform: translateY(30px) scale(1.08); }
        }

        .hero-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            gap: 4rem;
            position: relative;
            z-index: 2;
            width: 100%;
        }

        /* Left: Text */
        .hero-text {}

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.25);
            color: rgba(255,255,255,0.9);
            padding: 0.45rem 1.1rem;
            border-radius: 50px;
            font-size: 0.82rem;
            font-weight: 600;
            margin-bottom: 1.75rem;
            backdrop-filter: blur(8px);
            letter-spacing: 0.03em;
        }
        .hero-badge i { color: hsl(48,100%,67%); }

        .hero-tag {
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: hsl(256,82%,80%);
            margin-bottom: 0.85rem;
            display: block;
        }

        .hero-title {
            font-family: 'Outfit', sans-serif;
            font-size: clamp(2.4rem, 4.5vw, 3.75rem);
            font-weight: 800;
            line-height: 1.13;
            color: #ffffff;
            margin-bottom: 1.5rem;
        }

        .hero-title .highlight {
            background: linear-gradient(135deg, hsl(48,100%,67%), hsl(280,80%,75%));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-desc {
            color: rgba(255,255,255,0.7);
            font-size: 1.08rem;
            line-height: 1.75;
            max-width: 520px;
            margin-bottom: 2.25rem;
        }

        .hero-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-hero-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            background: linear-gradient(135deg, hsl(256,82%,58%), hsl(280,80%,62%));
            color: white;
            padding: 0.9rem 2rem;
            border-radius: 50px;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 0.97rem;
            box-shadow: 0 8px 25px rgba(114,46,209,0.4);
            transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
            text-decoration: none;
        }
        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 35px rgba(114,46,209,0.5);
        }

        .btn-hero-ghost {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            background: rgba(255,255,255,0.1);
            color: white;
            padding: 0.9rem 1.8rem;
            border-radius: 50px;
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            font-size: 0.97rem;
            border: 1px solid rgba(255,255,255,0.3);
            transition: all 0.3s;
            text-decoration: none;
        }
        .btn-hero-ghost:hover {
            background: rgba(255,255,255,0.2);
        }

        /* Right: Visual floating cards */
        .hero-visual {
            position: relative;
            height: 460px;
        }

        .floating-card {
            position: absolute;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.35);
            transition: transform 0.5s ease;
        }

        .fc-main {
            width: 260px;
            height: 340px;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 3;
            animation: bobMain 6s ease-in-out infinite;
        }
        .fc-main img { width:100%; height:100%; object-fit: cover; }

        .fc-sm1 {
            width: 170px;
            height: 210px;
            top: 60px;
            left: 0;
            z-index: 2;
            animation: bobSm1 7s ease-in-out infinite;
        }
        .fc-sm1 img { width:100%; height:100%; object-fit: cover; }

        .fc-sm2 {
            width: 160px;
            height: 200px;
            bottom: 30px;
            right: 0;
            z-index: 2;
            animation: bobSm2 8s ease-in-out infinite;
        }
        .fc-sm2 img { width:100%; height:100%; object-fit: cover; }

        @keyframes bobMain {
            0%,100% { transform: translateX(-50%) translateY(0); }
            50%      { transform: translateX(-50%) translateY(-14px); }
        }
        @keyframes bobSm1 {
            0%,100% { transform: translateY(0) rotate(-4deg); }
            50%      { transform: translateY(-10px) rotate(-4deg); }
        }
        @keyframes bobSm2 {
            0%,100% { transform: translateY(0) rotate(3deg); }
            50%      { transform: translateY(-12px) rotate(3deg); }
        }

        /* Pill badges on visual */
        .pill-badge {
            position: absolute;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: white;
            padding: 0.65rem 1.1rem;
            border-radius: 50px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.18);
            font-size: 0.82rem;
            font-weight: 700;
            color: hsl(222,47%,11%);
            z-index: 10;
            animation: pillFloat 5s ease-in-out infinite;
        }
        .pill-badge i { font-size: 1rem; }
        .pill-badge.pill-1 { top: 10px; right: 15px; animation-delay: 0s; }
        .pill-badge.pill-2 { bottom: 55px; left: 5px; animation-delay: -2.5s; }

        @keyframes pillFloat {
            0%,100% { transform: translateY(0); }
            50%      { transform: translateY(-8px); }
        }

        /* ---- STATS BAR ---- */
        .stats-bar {
            background: white;
            border-bottom: 1px solid var(--border-color);
            padding: 2rem;
        }
        .stats-bar-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            divide: '';
        }
        .stat-item {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            padding: 0 2rem;
            border-right: 1px solid var(--border-color);
        }
        .stat-item:last-child { border-right: none; }
        .stat-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            background: var(--primary-light);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
            color: var(--primary);
            flex-shrink: 0;
        }
        .stat-num {
            font-family: 'Outfit', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            color: hsl(222,47%,11%);
            line-height: 1;
        }
        .stat-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-top: 0.2rem;
        }

        /* ---- HOW IT WORKS ---- */
        .section {
            padding: 6rem 2rem;
        }
        .section-inner {
            max-width: 1200px;
            margin: 0 auto;
        }
        .section-chip {
            display: inline-block;
            background: var(--primary-light);
            color: var(--primary);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 0.35rem 1rem;
            border-radius: 50px;
            margin-bottom: 1rem;
        }
        .section-title {
            font-family: 'Outfit', sans-serif;
            font-size: clamp(1.75rem, 3vw, 2.4rem);
            font-weight: 800;
            color: hsl(222,47%,11%);
            margin-bottom: 0.75rem;
        }
        .section-sub {
            color: var(--text-muted);
            font-size: 1.05rem;
            max-width: 560px;
            line-height: 1.7;
        }
        .section-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 2rem;
            margin-bottom: 3.5rem;
            flex-wrap: wrap;
        }

        /* Steps */
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            position: relative;
        }

        /* connector line between steps */
        .steps-grid::before {
            content: '';
            position: absolute;
            top: 42px;
            left: calc(12.5% + 26px);
            right: calc(12.5% + 26px);
            height: 2px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            opacity: 0.2;
            border-radius: 2px;
        }

        .step-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 2.5rem 1.75rem 2rem;
            position: relative;
            text-align: center;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
        }
        .step-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 40px rgba(114,46,209,0.1);
            border-color: rgba(114,46,209,0.3);
        }
        .step-num {
            position: absolute;
            top: -1.35rem;
            left: 50%;
            transform: translateX(-50%);
            width: 2.7rem; height: 2.7rem;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Outfit', sans-serif;
            font-size: 1.05rem;
            font-weight: 800;
            box-shadow: 0 4px 14px rgba(114,46,209,0.35);
            border: 3px solid var(--light-bg);
        }
        .step-icon-wrap {
            width: 64px; height: 64px;
            border-radius: 16px;
            background: var(--primary-light);
            margin: 0.5rem auto 1.5rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem;
            color: var(--primary);
        }
        .step-card h3 {
            font-size: 1.1rem;
            margin-bottom: 0.65rem;
            color: hsl(222,47%,11%);
        }
        .step-card p {
            color: var(--text-muted);
            font-size: 0.9rem;
            line-height: 1.65;
        }

        /* ---- GALLERY ---- */
        .section-bg-light { background: hsl(220,30%,97%); }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 1.5rem;
        }

        .gallery-card {
            background: white;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
            display: flex;
            flex-direction: column;
        }
        .gallery-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 45px rgba(0,0,0,0.10);
        }
        .gallery-img-wrap {
            position: relative;
            padding-top: 125%;
            overflow: hidden;
            background: hsl(220,30%,93%);
        }
        .gallery-img {
            position: absolute;
            inset: 0;
            width: 100%; height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        .gallery-card:hover .gallery-img { transform: scale(1.06); }
        .gallery-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(15,23,42,0.55), transparent 60%);
            opacity: 0;
            transition: opacity 0.3s;
            display: flex; align-items: flex-end; padding: 1rem;
        }
        .gallery-card:hover .gallery-overlay { opacity: 1; }
        .gallery-overlay-text {
            color: white;
            font-size: 0.82rem;
            font-weight: 600;
        }
        .gallery-cond-badge {
            position: absolute;
            top: 0.75rem; left: 0.75rem;
            z-index: 5;
        }
        .gallery-info {
            padding: 1.25rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .gallery-cat {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--primary);
            margin-bottom: 0.35rem;
        }
        .gallery-title {
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.35;
            color: hsl(222,47%,12%);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 0.3rem;
        }
        .gallery-author {
            font-size: 0.82rem;
            color: var(--text-muted);
            margin-bottom: 0.9rem;
        }
        .gallery-footer {
            margin-top: auto;
            padding-top: 0.9rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
        }
        .gallery-donor-name { font-weight: 700; color: var(--text-main); }
        .gallery-jumlah {
            background: var(--primary-light);
            color: var(--primary);
            font-weight: 700;
            padding: 0.2rem 0.6rem;
            border-radius: 50px;
            font-size: 0.75rem;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 5rem 2rem;
            background: white;
            border-radius: 20px;
            border: 1px solid var(--border-color);
        }
        .empty-state-icon {
            font-size: 3.5rem;
            margin-bottom: 1.25rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .empty-state h3 { font-size: 1.4rem; margin-bottom: 0.5rem; }
        .empty-state p { color: var(--text-muted); max-width: 380px; margin: 0 auto; }

        /* ---- CTA BANNER ---- */
        .cta-section {
            padding: 5rem 2rem;
        }
        .cta-banner {
            max-width: 1200px;
            margin: 0 auto;
            background: linear-gradient(130deg, hsl(224,60%,10%) 0%, hsl(256,60%,18%) 55%, hsl(280,55%,22%) 100%);
            border-radius: 28px;
            padding: 5rem 3rem;
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            gap: 2.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(114,46,209,0.25);
        }
        .cta-banner::before {
            content: '';
            position: absolute;
            width: 350px; height: 350px;
            border-radius: 50%;
            background: radial-gradient(circle, hsl(256,82%,65%), transparent);
            opacity: 0.2;
            filter: blur(60px);
            top: -100px; left: -80px;
        }
        .cta-banner::after {
            content: '';
            position: absolute;
            width: 250px; height: 250px;
            border-radius: 50%;
            background: radial-gradient(circle, hsl(48,100%,67%), transparent);
            opacity: 0.15;
            filter: blur(50px);
            bottom: -80px; right: 20%;
        }
        .cta-text { position: relative; z-index: 2; }
        .cta-text h2 {
            font-family: 'Outfit', sans-serif;
            font-size: clamp(1.6rem, 3vw, 2.25rem);
            font-weight: 800;
            color: white;
            margin-bottom: 0.85rem;
        }
        .cta-text p {
            color: rgba(255,255,255,0.68);
            font-size: 1.05rem;
            line-height: 1.7;
            max-width: 520px;
        }
        .cta-actions {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: flex-end;
        }
        .btn-cta {
            display: inline-flex; align-items: center; gap: 0.6rem;
            padding: 1rem 2.2rem;
            border-radius: 50px;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.3s;
            white-space: nowrap;
        }
        .btn-cta-primary {
            background: white;
            color: var(--primary);
            box-shadow: 0 8px 25px rgba(0,0,0,0.18);
        }
        .btn-cta-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 35px rgba(0,0,0,0.25);
        }
        .btn-cta-ghost {
            background: rgba(255,255,255,0.12);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
        }
        .btn-cta-ghost:hover { background: rgba(255,255,255,0.2); }

        /* ---- FOOTER ---- */
        footer.site-footer {
            background: hsl(222,47%,7%);
            color: white;
            padding: 5rem 2rem 2rem;
        }
        .footer-inner {
            max-width: 1200px;
            margin: 0 auto;
        }
        .footer-top {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.2fr;
            gap: 3.5rem;
            padding-bottom: 3.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .footer-brand-name {
            font-family: 'Outfit', sans-serif;
            font-size: 1.6rem;
            font-weight: 800;
            color: white;
            display: flex; align-items: center; gap: 0.6rem;
            margin-bottom: 1.1rem;
        }
        .footer-brand-name i {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .footer-desc {
            color: rgba(255,255,255,0.5);
            font-size: 0.92rem;
            line-height: 1.75;
            max-width: 300px;
        }
        .footer-col h5 {
            color: white;
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
            letter-spacing: 0.02em;
        }
        .footer-col ul { list-style: none; display: flex; flex-direction: column; gap: 0.7rem; }
        .footer-col a {
            color: rgba(255,255,255,0.5);
            font-size: 0.9rem;
            transition: color 0.2s;
            text-decoration: none;
        }
        .footer-col a:hover { color: white; }
        .footer-contact-item {
            display: flex; align-items: flex-start; gap: 0.7rem;
            color: rgba(255,255,255,0.5);
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
        }
        .footer-contact-item i {
            color: var(--primary);
            margin-top: 0.15rem;
            flex-shrink: 0;
        }
        .footer-bottom {
            padding-top: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: rgba(255,255,255,0.35);
            flex-wrap: wrap;
            gap: 1rem;
        }
        .footer-bottom a { color: rgba(255,255,255,0.55); text-decoration: none; }
        .footer-bottom a:hover { color: white; }

        /* Scroll reveal anim helper */
        .reveal {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.65s ease, transform 0.65s ease;
        }
        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* ---- RESPONSIVE ---- */
        @media (max-width: 992px) {
            .hero-inner { grid-template-columns: 1fr; text-align: center; }
            .hero-desc { margin-left: auto; margin-right: auto; }
            .hero-actions { justify-content: center; }
            .hero-visual { display: none; }
            .cta-banner { grid-template-columns: 1fr; text-align: center; }
            .cta-actions { align-items: center; }
            .footer-top { grid-template-columns: 1fr 1fr; gap: 2.5rem; }
        }
        @media (max-width: 768px) {
            .stats-bar-inner { grid-template-columns: 1fr; gap: 1.5rem; }
            .stat-item { border-right: none; border-bottom: 1px solid var(--border-color); padding: 0 0 1.5rem; }
            .stat-item:last-child { border-bottom: none; padding-bottom: 0; }
            .footer-top { grid-template-columns: 1fr; gap: 2rem; }
            .steps-grid::before { display: none; }
            .cta-banner { padding: 3rem 1.75rem; border-radius: 18px; }
        }
    </style>
</head>
<body>

<!-- ====================== NAVBAR ====================== -->
<header class="navbar-container" id="main-header">
    <nav class="navbar">
        <a href="index.php" class="nav-brand">
            <i class="fas fa-book-open-reader"></i>
            <span>DonasiBuku</span>
        </a>

        <button class="mobile-nav-toggle" id="mobile-toggle" aria-label="Toggle Navigation">
            <i class="fas fa-bars"></i>
        </button>

        <ul class="nav-menu" id="nav-menu">
            <li class="nav-item"><a href="#tentang">Tentang</a></li>
            <li class="nav-item"><a href="#tata-cara">Tata Cara</a></li>
            <li class="nav-item"><a href="#galeri">Galeri Buku</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item"><a href="admin/dashboard.php">Dashboard Admin</a></li>
                <?php else: ?>
                    <li class="nav-item"><a href="user/dashboard.php">Dashboard</a></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>

        <div class="nav-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin/dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-user-shield"></i> Dashboard Admin
                    </a>
                <?php else: ?>
                    <a href="user/dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-gauge"></i> Dashboard
                    </a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Keluar
                </a>
            <?php else: ?>
                <a href="login.php" class="btn btn-secondary">
                    <i class="fas fa-sign-in-alt"></i> Masuk
                </a>
                <a href="register.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Daftar Gratis
                </a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<!-- ====================== HERO ====================== -->
<section class="hero" id="tentang">
    <div class="hero-inner">

        <!-- Left: Text -->
        <div class="hero-text">

            <span class="hero-tag">Lebih dari <?php echo number_format($total_books); ?> Buku Disalurkan</span>
            <h1 class="hero-title">
                Bagikan Ilmu,<br>
                Terangi <span class="highlight">Masa Depan</span><br>
                Anak Bangsa
            </h1>
            <p class="hero-desc">
                Donasikan buku bekas layak baca atau baru milikmu. Bersama kami, ribuan buku telah berhasil menjangkau tangan-tangan yang membutuhkan di penjuru nusantara.
            </p>
            <div class="hero-actions">
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user'): ?>
                    <a href="user/donate.php" class="btn-hero-primary">
                        <i class="fas fa-hand-holding-heart"></i> Donasi Sekarang
                    </a>
                    <a href="user/dashboard.php" class="btn-hero-ghost">
                        <i class="fas fa-gauge"></i> Lihat Dashboard
                    </a>
                <?php elseif (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin/dashboard.php" class="btn-hero-primary">
                        <i class="fas fa-user-shield"></i> Kelola Admin
                    </a>
                <?php else: ?>
                    <a href="register.php" class="btn-hero-primary">
                        <i class="fas fa-hand-holding-heart"></i> Mulai Donasi
                    </a>
                    <a href="login.php" class="btn-hero-ghost">
                        <i class="fas fa-sign-in-alt"></i> Sudah Punya Akun?
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Floating Visual Cards -->
        <div class="hero-visual" aria-hidden="true">
            <!-- Main large card -->
            <div class="floating-card fc-main">
                <img src="https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&q=80&w=400&h=520" alt="Buku Donasi">
            </div>
            <!-- Small left card -->
            <div class="floating-card fc-sm1">
                <img src="https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&q=80&w=280&h=360" alt="Buku">
            </div>
            <!-- Small right card -->
            <div class="floating-card fc-sm2">
                <img src="https://images.unsplash.com/photo-1543002588-bfa74002ed7e?auto=format&fit=crop&q=80&w=280&h=340" alt="Buku">
            </div>


        </div>

    </div>
</section>

<!-- ====================== STATS BAR ====================== -->
<div class="stats-bar">
    <div class="stats-bar-inner">
        <div class="stat-item reveal">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div>
                <div class="stat-num"><?php echo number_format($total_donors); ?>+</div>
                <div class="stat-label">Donatur Terdaftar</div>
            </div>
        </div>
        <div class="stat-item reveal" style="transition-delay:0.1s">
            <div class="stat-icon" style="background:hsl(152,76%,93%); color:var(--success)"><i class="fas fa-book"></i></div>
            <div>
                <div class="stat-num"><?php echo number_format($total_books); ?>+</div>
                <div class="stat-label">Buku Tersalurkan</div>
            </div>
        </div>
        <div class="stat-item reveal" style="transition-delay:0.2s">
            <div class="stat-icon" style="background:hsl(38,92%,92%); color:var(--warning)"><i class="fas fa-clipboard-check"></i></div>
            <div>
                <div class="stat-num"><?php echo number_format($total_titles); ?>+</div>
                <div class="stat-label">Ajuan Diverifikasi</div>
            </div>
        </div>
    </div>
</div>

<!-- ====================== HOW IT WORKS ====================== -->
<section class="section" id="tata-cara">
    <div class="section-inner">
        <div class="section-header-row">
            <div>
                <span class="section-chip">Tata Cara</span>
                <h2 class="section-title">Cara Berdonasi Buku<br>Hanya 4 Langkah Mudah</h2>
                <p class="section-sub">Proses yang sederhana dan transparan agar setiap buku Anda sampai ke tangan yang tepat.</p>
            </div>
            <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="btn btn-primary">
                <i class="fas fa-arrow-right"></i> Mulai Sekarang
            </a>
            <?php endif; ?>
        </div>

        <div class="steps-grid">
            <div class="step-card reveal">
                <div class="step-num">1</div>
                <div class="step-icon-wrap"><i class="fas fa-user-check"></i></div>
                <h3>Buat Akun</h3>
                <p>Daftarkan diri sebagai donatur melalui formulir registrasi yang mudah dan cepat.</p>
            </div>
            <div class="step-card reveal" style="transition-delay:0.1s">
                <div class="step-num">2</div>
                <div class="step-icon-wrap"><i class="fas fa-book-medical"></i></div>
                <h3>Isi Data Buku</h3>
                <p>Isi judul, penulis, kategori, kondisi buku, dan unggah foto sampul yang jelas.</p>
            </div>
            <div class="step-card reveal" style="transition-delay:0.2s">
                <div class="step-num">3</div>
                <div class="step-icon-wrap"><i class="fas fa-magnifying-glass-chart"></i></div>
                <h3>Verifikasi Admin</h3>
                <p>Tim admin meninjau kelayakan buku. Pantau statusnya di Dashboard Donatur Anda.</p>
            </div>
            <div class="step-card reveal" style="transition-delay:0.3s">
                <div class="step-num">4</div>
                <div class="step-icon-wrap"><i class="fas fa-truck-ramp-box"></i></div>
                <h3>Kirim Buku</h3>
                <p>Setelah ajuan diterima, kirimkan buku ke alamat gudang penampungan kami.</p>
            </div>
        </div>
    </div>
</section>

<!-- ====================== GALLERY ====================== -->
<section class="section section-bg-light" id="galeri">
    <div class="section-inner">
        <div class="section-header-row">
            <div>
                <span class="section-chip">Galeri Buku</span>
                <h2 class="section-title">Buku Donasi Terbaru<br>yang Telah Terverifikasi</h2>
                <p class="section-sub">Buku-buku yang telah lolos seleksi kelayakan dan siap disalurkan ke perpustakaan tujuan.</p>
            </div>
        </div>

        <?php if (mysqli_num_rows($gallery_res) > 0): ?>
            <div class="gallery-grid">
                <?php while ($book = mysqli_fetch_assoc($gallery_res)): ?>
                    <?php
                        $foto_path = 'uploads/' . htmlspecialchars($book['foto']);
                        if (!file_exists($foto_path) || empty($book['foto'])) {
                            $seed = abs(crc32($book['judul_buku'])) % 1000;
                            $foto_path = "https://picsum.photos/seed/{$seed}/300/400";
                        }
                        $kondisi_text  = ($book['kondisi'] === 'baru') ? 'Baru' : 'Bekas Layak';
                        $kondisi_badge = ($book['kondisi'] === 'baru') ? 'badge-diterima' : 'badge-pending';
                    ?>
                    <div class="gallery-card reveal">
                        <div class="gallery-img-wrap">
                            <a href="#" data-lightbox-src="<?php echo $foto_path; ?>"
                               data-title="<?php echo htmlspecialchars($book['judul_buku']); ?>"
                               data-meta="Kategori: <?php echo htmlspecialchars($book['kategori']); ?> | Penulis: <?php echo htmlspecialchars($book['penulis']); ?>">
                                <img src="<?php echo $foto_path; ?>" class="gallery-img"
                                     alt="<?php echo htmlspecialchars($book['judul_buku']); ?>"
                                     loading="lazy">
                                <div class="gallery-overlay">
                                    <span class="gallery-overlay-text"><i class="fas fa-search-plus"></i> Lihat Detail</span>
                                </div>
                            </a>
                            <div class="gallery-cond-badge">
                                <span class="badge <?php echo $kondisi_badge; ?>"><?php echo $kondisi_text; ?></span>
                            </div>
                        </div>
                        <div class="gallery-info">
                            <div class="gallery-cat"><?php echo htmlspecialchars($book['kategori']); ?></div>
                            <h3 class="gallery-title"><?php echo htmlspecialchars($book['judul_buku']); ?></h3>
                            <div class="gallery-author">Karya: <?php echo htmlspecialchars($book['penulis']); ?></div>
                            <div class="gallery-footer">
                                <span class="gallery-donor-name">
                                    <i class="fas fa-user" style="color:var(--text-muted); margin-right:3px; font-size:0.7rem;"></i>
                                    <?php echo htmlspecialchars($book['donor_nama']); ?>
                                </span>
                                <span class="gallery-jumlah">
                                    <?php echo htmlspecialchars($book['jumlah']); ?> eks
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state reveal">
                <div class="empty-state-icon"><i class="fas fa-book-open"></i></div>
                <h3>Belum Ada Buku Terverifikasi</h3>
                <p>Buku yang telah lolos verifikasi admin akan tampil di galeri publik ini. Jadilah yang pertama berdonasi!</p>
                <br>
                <a href="register.php" class="btn btn-primary" style="margin-top:1rem;">
                    <i class="fas fa-hand-holding-heart"></i> Donasi Pertama
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ====================== CTA BANNER ====================== -->
<section class="cta-section">
    <div class="cta-banner reveal">
        <div class="cta-text">
            <h2>Punya Buku Tak Terpakai di Rumah?</h2>
            <p>Alih-alih menimbun debu, salurkan buku-buku tersebut kepada anak-anak yang membutuhkan untuk membuka cakrawala dunia mereka.</p>
        </div>
        <div class="cta-actions">
            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user'): ?>
                <a href="user/donate.php" class="btn-cta btn-cta-primary">
                    <i class="fas fa-hand-holding-heart"></i> Kirim Donasi Sekarang
                </a>
            <?php else: ?>
                <a href="register.php" class="btn-cta btn-cta-primary">
                    <i class="fas fa-user-plus"></i> Bergabung Sebagai Donatur
                </a>
                <a href="login.php" class="btn-cta btn-cta-ghost">
                    <i class="fas fa-sign-in-alt"></i> Masuk ke Akun
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ====================== FOOTER ====================== -->
<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-top">
            <!-- Brand -->
            <div>
                <div class="footer-brand-name">
                    <i class="fas fa-book-open-reader"></i> DonasiBuku
                </div>
                <p class="footer-desc">
                    Platform donasi buku berbasis web murni untuk mempermudah masyarakat mengumpulkan dan menyalurkan bahan bacaan bermutu ke sekolah dan perpustakaan di daerah tertinggal.
                </p>
            </div>

            <!-- Nav -->
            <div class="footer-col">
                <h5>Navigasi</h5>
                <ul>
                    <li><a href="#tentang">Tentang Program</a></li>
                    <li><a href="#tata-cara">Tata Cara Donasi</a></li>
                    <li><a href="#galeri">Galeri Buku</a></li>
                </ul>
            </div>

            <!-- Portal -->
            <div class="footer-col">
                <h5>Portal</h5>
                <ul>
                    <li><a href="login.php">Masuk (Donatur)</a></li>
                    <li><a href="register.php">Daftar Baru</a></li>
                    <li><a href="login-admin.php">Portal Admin</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="footer-col">
                <h5>Kontak Kami</h5>
                <div class="footer-contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>erje@gmail.com</span>
                </div>
                <div class="footer-contact-item">
                    <i class="fas fa-phone"></i>
                    <span>+6281527426149</span>
                </div>
                <div class="footer-contact-item">
                    <i class="fas fa-location-dot"></i>
                    <span>Jl.poros malino</span>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <span>&copy; <?php echo date('Y'); ?> Donasibuku erje</span>
            <span><i class="fas fa-heart" style="color:hsl(352,82%,60%)"></i></span>
        </div>
    </div>
</footer>

<!-- ====================== LIGHTBOX MODAL ====================== -->
<div class="modal-overlay" id="lightbox-overlay">
    <div class="modal-content">
        <button class="modal-close" id="lightbox-close" aria-label="Tutup">&times;</button>
        <div class="modal-body">
            <img src="" id="lightbox-img" alt="Sampul Buku">
            <div>
                <h3 id="lightbox-title" style="font-size:1.15rem; margin-bottom:0.4rem;"></h3>
                <p id="lightbox-meta" style="color:var(--text-muted); font-size:0.88rem;"></p>
            </div>
        </div>
    </div>
</div>

<!-- JS -->
<script src="assets/js/main.js"></script>
<script>
    // ---- Navbar scroll effect ----
    const header = document.getElementById('main-header');

    // ---- Parallax: hero visual stays in place when scrolling ----
    const heroVisual = document.querySelector('.hero-visual');
    const heroSection = document.querySelector('.hero');

    window.addEventListener('scroll', () => {
        header.classList.toggle('scrolled', window.scrollY > 40);

        if (heroVisual && heroSection) {
            const heroRect   = heroSection.getBoundingClientRect();
            const heroHeight = heroSection.offsetHeight;
            // How far we've scrolled into the hero (0 → heroHeight)
            const scrolled   = Math.max(0, -heroRect.top);
            // Only apply while still inside / just past the hero
            if (scrolled <= heroHeight) {
                // Move the visual upward at 55% the scroll speed → lag-behind parallax
                heroVisual.style.transform = `translateY(${scrolled * 0.45}px)`;
            }
        }
    });

    // ---- Mobile nav ----
    const toggle = document.getElementById('mobile-toggle');
    const navMenu = document.getElementById('nav-menu');
    if (toggle) {
        toggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            toggle.querySelector('i').className = navMenu.classList.contains('active')
                ? 'fas fa-times' : 'fas fa-bars';
        });
    }

    // ---- Scroll reveal ----
    const reveals = document.querySelectorAll('.reveal');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });
    reveals.forEach(el => observer.observe(el));

    // ---- Lightbox ----
    const overlay   = document.getElementById('lightbox-overlay');
    const lbImg     = document.getElementById('lightbox-img');
    const lbTitle   = document.getElementById('lightbox-title');
    const lbMeta    = document.getElementById('lightbox-meta');
    const lbClose   = document.getElementById('lightbox-close');

    document.querySelectorAll('[data-lightbox-src]').forEach(el => {
        el.addEventListener('click', (e) => {
            e.preventDefault();
            lbImg.src   = el.dataset.lightboxSrc;
            lbTitle.textContent = el.dataset.title || '';
            lbMeta.textContent  = el.dataset.meta  || '';
            overlay.classList.add('active');
        });
    });
    lbClose.addEventListener('click', () => overlay.classList.remove('active'));
    overlay.addEventListener('click', (e) => { if (e.target === overlay) overlay.classList.remove('active'); });

    // ---- Smooth scroll for anchor links ----
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                navMenu.classList.remove('active');
            }
        });
    });
</script>
</body>
</html>
