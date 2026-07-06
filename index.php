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
if ($total_books === null)
    $total_books = 0;

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
while ($r = mysqli_fetch_assoc($recent_res)) {
    $recent_books[] = $r;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonasiBuku — Berbagi Pengetahuan, Terangi Masa Depan</title>
    <meta name="description"
        content="Platform donasi buku layak baca terpercaya. Donasikan buku Anda untuk menyokong sarana belajar anak-anak di penjuru nusantara.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* =========================================
           LANDING PAGE — WARM EDITORIAL
           ========================================= */

        /* ---- HERO ---- */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: var(--ink);
            position: relative;
            overflow: hidden;
            padding: 7rem 2rem 5rem;
        }

        /* Warm ambient glow — single teal, not purple orbs */
        .hero::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, hsla(172, 56%, 34%, 0.25), transparent 70%);
            top: -200px;
            right: -100px;
            filter: blur(60px);
        }

        .hero::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, hsla(16, 68%, 52%, 0.12), transparent 70%);
            bottom: -150px;
            left: 5%;
            filter: blur(50px);
        }

        /* Navbar over hero: light text */
        header.navbar-container {
            /* background: transparent; */
            background-color: var(--ink);
            border-bottom: 1px solid hsla(0, 0%, 100%, 0.06);
        }

        header.navbar-container:not(.scrolled) .nav-brand {
            color: white;
        }

        header.navbar-container:not(.scrolled) .nav-brand i {
            color: var(--teal);
        }

        header.navbar-container:not(.scrolled) .nav-item a {
            color: hsla(0, 0%, 100%, 0.7);
        }

        header.navbar-container:not(.scrolled) .nav-item a:hover {
            color: white;
        }

        header.navbar-container:not(.scrolled) .nav-item a::after {
            background-color: var(--teal);
        }

        header.navbar-container:not(.scrolled) .mobile-nav-toggle {
            color: white;
        }

        header.navbar-container:not(.scrolled) .btn-secondary {
            background: hsla(0, 0%, 100%, 0.08);
            color: white;
            border-color: hsla(0, 0%, 100%, 0.2);
        }

        header.navbar-container:not(.scrolled) .btn-secondary:hover {
            background: hsla(0, 0%, 100%, 0.15);
        }

        /* Scrolled: warm cream */
        header.navbar-container.scrolled {
            background: hsla(38, 50%, 99%, 0.95) !important;
            border-bottom: 1px solid var(--ash) !important;
            box-shadow: var(--shadow-sm);
            backdrop-filter: blur(10px);
        }

        header.navbar-container.scrolled .nav-brand {
            color: var(--ink);
        }

        header.navbar-container.scrolled .nav-brand i {
            color: var(--teal);
        }

        header.navbar-container.scrolled .nav-item a {
            color: var(--smoke);
        }

        header.navbar-container.scrolled .nav-item a:hover {
            color: var(--ink);
        }

        header.navbar-container.scrolled .nav-item a::after {
            background-color: var(--teal);
        }

        header.navbar-container.scrolled .mobile-nav-toggle {
            color: var(--ink);
        }

        header.navbar-container.scrolled .btn-secondary {
            background: var(--cream);
            color: var(--ink);
            border-color: var(--ash);
        }

        /* Hero Layout */
        .hero-inner {
            max-width: 1120px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            gap: 4rem;
            position: relative;
            z-index: 2;
            width: 100%;
        }

        .hero-text {
            animation: fadeUp 0.8s ease both;
        }

        .hero-tag {
            font-family: var(--font-body);
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--teal);
            margin-bottom: 1rem;
            display: block;
        }

        .hero-title {
            font-family: var(--font-display);
            font-size: clamp(2.2rem, 4.2vw, 3.5rem);
            font-weight: 800;
            line-height: 1.15;
            color: white;
            margin-bottom: 1.5rem;
        }

        .hero-title .highlight {
            color: var(--teal);
            font-style: italic;
        }

        .hero-desc {
            color: hsla(0, 0%, 100%, 0.6);
            font-size: 1.05rem;
            line-height: 1.8;
            max-width: 500px;
            margin-bottom: 2.25rem;
        }

        .hero-actions {
            display: flex;
            gap: 0.85rem;
            flex-wrap: wrap;
        }

        .btn-hero-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--teal);
            color: white;
            padding: 0.85rem 1.85rem;
            border-radius: var(--radius-md);
            font-family: var(--font-body);
            font-weight: 700;
            font-size: 0.92rem;
            box-shadow: 0 4px 16px hsla(172, 56%, 34%, 0.3);
            transition: var(--transition);
            text-decoration: none;
        }

        .btn-hero-primary:hover {
            background: var(--teal-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px hsla(172, 56%, 34%, 0.35);
        }

        .btn-hero-ghost {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: hsla(0, 0%, 100%, 0.06);
            color: hsla(0, 0%, 100%, 0.85);
            padding: 0.85rem 1.65rem;
            border-radius: var(--radius-md);
            font-family: var(--font-body);
            font-weight: 600;
            font-size: 0.92rem;
            border: 1px solid hsla(0, 0%, 100%, 0.15);
            transition: var(--transition);
            text-decoration: none;
        }

        .btn-hero-ghost:hover {
            background: hsla(0, 0%, 100%, 0.12);
            border-color: hsla(0, 0%, 100%, 0.3);
        }

        /* Hero Visual — single large book image */
        .hero-visual {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .hero-book-showcase {
            position: relative;
            max-width: 360px;
            width: 100%;
        }

        .hero-book-showcase img {
            width: 100%;
            height: auto;
            border-radius: 12px;
            box-shadow: 0 24px 60px hsla(0, 0%, 0%, 0.4);
            animation: fadeUp 0.8s ease 0.2s both;
        }

        /* Subtle stat pill on hero image */
        .hero-stat-pill {
            position: absolute;
            bottom: -20px;
            left: -20px;
            background: var(--cream);
            padding: 0.75rem 1.15rem;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--ink);
            z-index: 5;
            animation: fadeUp 0.8s ease 0.4s both;
        }

        .hero-stat-pill i {
            color: var(--teal);
            font-size: 1rem;
        }

        /* ---- STATS BAR ---- */
        .stats-bar {
            background: var(--cream);
            border-bottom: 1px solid var(--ash);
            padding: 2rem;
        }

        .stats-bar-inner {
            max-width: 1120px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 1.15rem;
            padding: 0 1.75rem;
            border-right: 1px solid var(--ash);
        }

        .stat-item:last-child {
            border-right: none;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-sm);
            background: var(--teal-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: var(--teal);
            flex-shrink: 0;
        }

        .stat-num {
            font-family: var(--font-display);
            font-size: 1.85rem;
            font-weight: 800;
            color: var(--ink);
            line-height: 1;
        }

        .stat-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--smoke);
            text-transform: uppercase;
            letter-spacing: 0.07em;
            margin-top: 0.15rem;
        }

        /* ---- SECTION COMMON ---- */
        .section {
            padding: 5rem 2rem;
        }

        .section-inner {
            max-width: 1120px;
            margin: 0 auto;
        }

        .section-chip {
            display: inline-block;
            background: var(--teal-light);
            color: var(--teal);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            padding: 0.3rem 0.85rem;
            border-radius: 50px;
            margin-bottom: 0.85rem;
        }

        .section-title {
            font-family: var(--font-display);
            font-size: clamp(1.6rem, 2.8vw, 2.25rem);
            font-weight: 800;
            color: var(--ink);
            margin-bottom: 0.65rem;
        }

        .section-sub {
            color: var(--smoke);
            font-size: 1rem;
            max-width: 520px;
            line-height: 1.75;
        }

        .section-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 2rem;
            margin-bottom: 3rem;
            flex-wrap: wrap;
        }

        /* ---- HOW IT WORKS STEPS ---- */
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 1.25rem;
            position: relative;
        }

        /* Thin connector line */
        .steps-grid::before {
            content: '';
            position: absolute;
            top: 38px;
            left: calc(12.5% + 24px);
            right: calc(12.5% + 24px);
            height: 1px;
            background: var(--ash);
        }

        .step-card {
            background: var(--cream);
            border: 1px solid var(--ash);
            border-radius: var(--radius-lg);
            padding: 2.25rem 1.5rem 1.75rem;
            position: relative;
            text-align: center;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .step-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-color: var(--teal);
        }

        .step-num {
            position: absolute;
            top: -1.15rem;
            left: 50%;
            transform: translateX(-50%);
            width: 2.4rem;
            height: 2.4rem;
            border-radius: 50%;
            background: var(--teal);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-display);
            font-size: 1rem;
            font-weight: 800;
            box-shadow: 0 3px 10px hsla(172, 56%, 34%, 0.3);
            border: 3px solid var(--parchment);
        }

        .step-icon-wrap {
            width: 56px;
            height: 56px;
            border-radius: var(--radius-sm);
            background: var(--teal-light);
            margin: 0.5rem auto 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: var(--teal);
        }

        .step-card h3 {
            font-family: var(--font-display);
            font-size: 1.05rem;
            margin-bottom: 0.5rem;
            color: var(--ink);
        }

        .step-card p {
            color: var(--smoke);
            font-size: 0.88rem;
            line-height: 1.65;
        }

        /* ---- GALLERY ---- */
        .section-bg-light {
            background: hsla(38, 40%, 96%, 0.5);
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 1.25rem;
        }

        .gallery-card {
            background: var(--cream);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--ash);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }

        .gallery-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .gallery-img-wrap {
            position: relative;
            padding-top: 130%;
            overflow: hidden;
            background: hsla(210, 18%, 88%, 0.4);
        }

        .gallery-img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.35s ease;
        }

        .gallery-card:hover .gallery-img {
            transform: scale(1.04);
        }

        .gallery-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, hsla(210, 30%, 14%, 0.5), transparent 55%);
            opacity: 0;
            transition: opacity 0.3s;
            display: flex;
            align-items: flex-end;
            padding: 0.85rem;
        }

        .gallery-card:hover .gallery-overlay {
            opacity: 1;
        }

        .gallery-overlay-text {
            color: white;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .gallery-cond-badge {
            position: absolute;
            top: 0.6rem;
            left: 0.6rem;
            z-index: 5;
        }

        .gallery-info {
            padding: 1.1rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .gallery-cat {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--teal);
            margin-bottom: 0.3rem;
        }

        .gallery-title {
            font-family: var(--font-display);
            font-size: 0.95rem;
            font-weight: 700;
            line-height: 1.3;
            color: var(--ink);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 0.25rem;
        }

        .gallery-author {
            font-size: 0.8rem;
            color: var(--smoke);
            margin-bottom: 0.75rem;
        }

        .gallery-footer {
            margin-top: auto;
            padding-top: 0.75rem;
            border-top: 1px solid var(--ash);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.78rem;
        }

        .gallery-donor-name {
            font-weight: 700;
            color: var(--ink);
        }

        .gallery-jumlah {
            background: var(--teal-light);
            color: var(--teal);
            font-weight: 700;
            padding: 0.2rem 0.55rem;
            border-radius: 50px;
            font-size: 0.72rem;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 4.5rem 2rem;
            background: var(--cream);
            border-radius: var(--radius-lg);
            border: 1px solid var(--ash);
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--teal);
        }

        .empty-state h3 {
            font-family: var(--font-display);
            font-size: 1.3rem;
            margin-bottom: 0.4rem;
            color: var(--ink);
        }

        .empty-state p {
            color: var(--smoke);
            max-width: 360px;
            margin: 0 auto;
        }

        /* ---- CTA BANNER ---- */
        .cta-section {
            padding: 4.5rem 2rem;
        }

        .cta-banner {
            max-width: 1120px;
            margin: 0 auto;
            background: var(--ink);
            border-radius: var(--radius-lg);
            padding: 4.5rem 3rem;
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            gap: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .cta-banner::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, hsla(172, 56%, 34%, 0.2), transparent);
            filter: blur(50px);
            top: -80px;
            left: -60px;
        }

        .cta-banner::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: radial-gradient(circle, hsla(16, 68%, 52%, 0.12), transparent);
            filter: blur(40px);
            bottom: -60px;
            right: 15%;
        }

        .cta-text {
            position: relative;
            z-index: 2;
        }

        .cta-text h2 {
            font-family: var(--font-display);
            font-size: clamp(1.5rem, 2.8vw, 2.1rem);
            font-weight: 800;
            color: white;
            margin-bottom: 0.75rem;
        }

        .cta-text p {
            color: hsla(0, 0%, 100%, 0.55);
            font-size: 1rem;
            line-height: 1.75;
            max-width: 480px;
        }

        .cta-actions {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            align-items: flex-end;
        }

        .btn-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.85rem 1.85rem;
            border-radius: var(--radius-md);
            font-family: var(--font-body);
            font-weight: 700;
            font-size: 0.92rem;
            text-decoration: none;
            transition: var(--transition);
            white-space: nowrap;
        }

        .btn-cta-primary {
            background: var(--teal);
            color: white;
            box-shadow: 0 4px 16px hsla(172, 56%, 34%, 0.3);
        }

        .btn-cta-primary:hover {
            background: var(--teal-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px hsla(172, 56%, 34%, 0.35);
        }

        .btn-cta-ghost {
            background: hsla(0, 0%, 100%, 0.08);
            color: white;
            border: 1px solid hsla(0, 0%, 100%, 0.15);
        }

        .btn-cta-ghost:hover {
            background: hsla(0, 0%, 100%, 0.14);
        }

        /* ---- FOOTER ---- */
        footer.site-footer {
            background: var(--ink);
            color: white;
            padding: 4.5rem 2rem 2rem;
        }

        .footer-inner {
            max-width: 1120px;
            margin: 0 auto;
        }

        .footer-top {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.2fr;
            gap: 3rem;
            padding-bottom: 3rem;
            border-bottom: 1px solid hsla(0, 0%, 100%, 0.06);
        }

        .footer-brand-name {
            font-family: var(--font-display);
            font-size: 1.45rem;
            font-weight: 800;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.55rem;
            margin-bottom: 1rem;
        }

        .footer-brand-name i {
            color: var(--teal);
        }

        .footer-desc {
            color: hsla(0, 0%, 100%, 0.45);
            font-size: 0.88rem;
            line-height: 1.75;
            max-width: 280px;
        }

        .footer-col h5 {
            color: white;
            font-family: var(--font-display);
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 1.1rem;
        }

        .footer-col ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.55rem;
        }

        .footer-col a {
            color: hsla(0, 0%, 100%, 0.45);
            font-size: 0.88rem;
            transition: var(--transition);
            text-decoration: none;
        }

        .footer-col a:hover {
            color: white;
        }

        .footer-contact-item {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            color: hsla(0, 0%, 100%, 0.45);
            font-size: 0.88rem;
            margin-bottom: 0.65rem;
        }

        .footer-contact-item i {
            color: var(--teal);
            margin-top: 0.15rem;
            flex-shrink: 0;
        }

        .footer-bottom {
            padding-top: 1.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.82rem;
            color: hsla(0, 0%, 100%, 0.3);
            flex-wrap: wrap;
            gap: 0.85rem;
        }

        .footer-bottom a {
            color: hsla(0, 0%, 100%, 0.5);
            text-decoration: none;
        }

        .footer-bottom a:hover {
            color: white;
        }

        /* Scroll reveal */
        .reveal {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.55s ease, transform 0.55s ease;
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* ---- RESPONSIVE ---- */
        @media (max-width: 992px) {
            .hero-inner {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero-desc {
                margin-left: auto;
                margin-right: auto;
            }

            .hero-actions {
                justify-content: center;
            }

            .hero-visual {
                display: none;
            }

            .cta-banner {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .cta-actions {
                align-items: center;
            }

            .footer-top {
                grid-template-columns: 1fr 1fr;
                gap: 2rem;
            }
        }

        @media (max-width: 768px) {
            .stats-bar-inner {
                grid-template-columns: 1fr;
                gap: 1.25rem;
            }

            .stat-item {
                border-right: none;
                border-bottom: 1px solid var(--ash);
                padding: 0 0 1.25rem;
            }

            .stat-item:last-child {
                border-bottom: none;
                padding-bottom: 0;
            }

            .footer-top {
                grid-template-columns: 1fr;
                gap: 1.75rem;
            }

            .steps-grid::before {
                display: none;
            }

            .cta-banner {
                padding: 2.75rem 1.5rem;
                border-radius: var(--radius-md);
            }
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
                    Donasikan buku bekas layak baca atau baru milikmu. Bersama kami, ribuan buku telah berhasil
                    menjangkau tangan-tangan yang membutuhkan di penjuru nusantara.
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

            <!-- Right: Single book showcase -->
            <div class="hero-visual" aria-hidden="true">
                <div class="hero-book-showcase">
                    <img src="https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&q=80&w=480&h=640"
                        alt="Buku Donasi">
                    <div class="hero-stat-pill">
                        <i class="fas fa-users"></i>
                        <span><?php echo number_format($total_donors); ?>+ Donatur Bergabung</span>
                    </div>
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
                <div class="stat-icon" style="background:var(--success-bg); color:var(--success)"><i
                        class="fas fa-book"></i></div>
                <div>
                    <div class="stat-num"><?php echo number_format($total_books); ?>+</div>
                    <div class="stat-label">Buku Tersalurkan</div>
                </div>
            </div>
            <div class="stat-item reveal" style="transition-delay:0.2s">
                <div class="stat-icon" style="background:var(--terracotta-light); color:var(--terracotta)"><i
                        class="fas fa-clipboard-check"></i></div>
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
                    <p class="section-sub">Proses yang sederhana dan transparan agar setiap buku Anda sampai ke tangan
                        yang tepat.</p>
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
                    <p class="section-sub">Buku-buku yang telah lolos seleksi kelayakan dan siap disalurkan ke
                        perpustakaan tujuan.</p>
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
                        $kondisi_text = ($book['kondisi'] === 'baru') ? 'Baru' : 'Bekas Layak';
                        $kondisi_badge = ($book['kondisi'] === 'baru') ? 'badge-diterima' : 'badge-pending';
                        ?>
                        <div class="gallery-card reveal">
                            <div class="gallery-img-wrap">
                                <a href="#" data-lightbox-src="<?php echo $foto_path; ?>"
                                    data-title="<?php echo htmlspecialchars($book['judul_buku']); ?>"
                                    data-meta="Kategori: <?php echo htmlspecialchars($book['kategori']); ?> | Penulis: <?php echo htmlspecialchars($book['penulis']); ?>">
                                    <img src="<?php echo $foto_path; ?>" class="gallery-img"
                                        alt="<?php echo htmlspecialchars($book['judul_buku']); ?>" loading="lazy">
                                    <div class="gallery-overlay">
                                        <span class="gallery-overlay-text"><i class="fas fa-search-plus"></i> Lihat
                                            Detail</span>
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
                                        <i class="fas fa-user"
                                            style="color:var(--smoke); margin-right:3px; font-size:0.7rem;"></i>
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
                    <p>Buku yang telah lolos verifikasi admin akan tampil di galeri publik ini. Jadilah yang pertama
                        berdonasi!</p>
                    <br>
                    <a href="register.php" class="btn btn-primary" style="margin-top:0.85rem;">
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
                <p>Alih-alih menimbun debu, salurkan buku-buku tersebut kepada anak-anak yang membutuhkan untuk membuka
                    cakrawala dunia mereka.</p>
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
                        Platform donasi buku berbasis web murni untuk mempermudah masyarakat mengumpulkan dan
                        menyalurkan bahan bacaan bermutu ke sekolah dan perpustakaan di daerah tertinggal.
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
                <span><i class="fas fa-heart" style="color:var(--terracotta)"></i></span>
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
                    <h3 id="lightbox-title"
                        style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:0.35rem;"></h3>
                    <p id="lightbox-meta" style="color:var(--smoke); font-size:0.85rem;"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="assets/js/main.js"></script>
    <script>
        // ---- Navbar scroll effect ----
        const header = document.getElementById('main-header');

        window.addEventListener('scroll', () => {
            header.classList.toggle('scrolled', window.scrollY > 40);
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
        const overlay = document.getElementById('lightbox-overlay');
        const lbImg = document.getElementById('lightbox-img');
        const lbTitle = document.getElementById('lightbox-title');
        const lbMeta = document.getElementById('lightbox-meta');
        const lbClose = document.getElementById('lightbox-close');

        document.querySelectorAll('[data-lightbox-src]').forEach(el => {
            el.addEventListener('click', (e) => {
                e.preventDefault();
                lbImg.src = el.dataset.lightboxSrc;
                lbTitle.textContent = el.dataset.title || '';
                lbMeta.textContent = el.dataset.meta || '';
                overlay.classList.add('active');
            });
        });
        lbClose.addEventListener('click', () => overlay.classList.remove('active'));
        overlay.addEventListener('click', (e) => { if (e.target === overlay) overlay.classList.remove('active'); });

        // ---- Smooth scroll for anchor links ----
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
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