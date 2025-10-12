<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wolfallet - A Secure Password Wallet</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :root {
            /* Enhanced Color Palette */
            --primary-blue: #007bff;
            --primary-blue-hover: #0069d9;
            --primary-blue-light: #4da3ff;
            --primary-blue-dark: #0056b3;

            /* Vercel-inspired Dark Theme - Enhanced */
            --bg-primary: #000000;
            --bg-secondary: #0a0a0a;
            --bg-card: #111111;
            --bg-accent: #1a1a1a;

            /* Text Colors - Enhanced Contrast */
            --text-heading: #ffffff;
            --text-primary: #f5f5f5;
            --text-secondary: #a0a0a0;
            --text-muted: #6a6a6a;
            
            /* Accent Colors */
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;

            /* Borders - Enhanced */
            --border-primary: #222222;
            --border-secondary: #333333;
            --border-accent: #3a3a3a;

            /* Spacing - Consistent Scale */
            --section-padding: 6rem 1.5rem;
            --container-max-width: 1200px;
            --border-radius: 0.75rem;
            --border-radius-lg: 1rem;
            --border-radius-xl: 1.25rem;

            /* Shadows */
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.6);
            --shadow-blue: 0 0 20px rgba(0, 123, 255, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.7;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        h1, h2, h3, h4 {
            color: var(--text-heading);
            font-weight: 700;
            line-height: 1.2;
            text-wrap: balance;
        }

        h1 { font-size: clamp(2.5rem, 5vw, 4rem); margin-bottom: 1.5rem; }
        h2 { font-size: clamp(2rem, 4vw, 3rem); margin-bottom: 1.5rem; }
        h3 { font-size: clamp(1.25rem, 3vw, 1.75rem); font-weight: 600; margin-bottom: 1rem; }
        h4 { font-size: 1.1rem; color: var(--text-primary); font-weight: 500; margin-bottom: 0.5rem; }

        p {
            margin-bottom: 1.5rem;
            color: var(--text-secondary);
            max-width: 65ch;
            line-height: 1.7;
        }
        .text-center p { margin-left: auto; margin-right: auto; }

        a {
            color: var(--primary-blue);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        a:hover { color: var(--primary-blue-light); }

        .container {
            max-width: var(--container-max-width);
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        section { 
            padding: var(--section-padding);
            position: relative;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.75rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid transparent;
            font-family: 'Ubuntu', sans-serif;
            position: relative;
            overflow: hidden;
        }

        .btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }

        .btn:focus:not(:active)::after {
            animation: ripple 1s ease-out;
        }

        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            100% {
                transform: scale(20, 20);
                opacity: 0;
            }
        }

        .btn-primary {
            background-color: var(--primary-blue);
            color: white;
            border-color: var(--primary-blue);
            box-shadow: var(--shadow-md);
        }
        .btn-primary:hover {
            background-color: var(--primary-blue-hover);
            border-color: var(--primary-blue-hover);
            color: white;
            box-shadow: var(--shadow-blue);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: transparent;
            color: var(--text-primary);
            border: 1px solid var(--border-secondary);
        }
        .btn-secondary:hover {
            background-color: var(--bg-card);
            border-color: var(--border-accent);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-large {
            padding: 1.125rem 2.25rem;
            font-size: 1.1rem;
        }

        .card {
            background-color: var(--bg-card);
            border-radius: var(--border-radius-lg);
            padding: 2.5rem;
            border: 1px solid var(--border-primary);
            transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
        }
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--primary-blue), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            border-color: var(--border-secondary);
            box-shadow: var(--shadow-lg);
        }
        .card:hover::before {
            opacity: 1;
        }

        .text-center { text-align: center; }
        .section-header { max-width: 700px; margin: 0 auto 4rem auto; }
        .section-header p { font-size: 1.125rem; }

        /* Header Styles - Enhanced */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            z-index: 1000;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-primary);
            transition: all 0.3s ease;
        }
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            display: flex;
            align-items: center;
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--text-heading);
        }
        .logo:hover { color: var(--text-heading); }
        .logo span { color: var(--primary-blue); }
        .logo i { margin-right: 0.5rem; color: var(--primary-blue); }
        .nav-links { display: flex; list-style: none; gap: 2rem; }
        .nav-links a {
            color: var(--text-secondary);
            font-weight: 500;
            position: relative;
            font-size: 0.95rem;
        }
        .nav-links a:hover { color: var(--text-primary); }
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background-color: var(--primary-blue);
            transition: width 0.3s ease;
        }
        .nav-links a:hover::after { width: 100%; }
        .nav-actions { display: flex; gap: 1rem; align-items: center; }
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: var(--text-primary);
            font-size: 1.75rem;
            cursor: pointer;
            z-index: 1001;
            transition: color 0.3s ease;
        }
        .mobile-menu-btn:hover { color: var(--primary-blue); }

        /* Hero Section - Enhanced */
        .hero {
            padding-top: 12rem;
            padding-bottom: 8rem;
            position: relative;
            overflow: hidden;
            text-align: center;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: min(1200px, 100%);
            height: 800px;
            transform: translate(-50%, -50%);
            background: radial-gradient(circle, rgba(0, 123, 255, 0.08) 0%, transparent 70%);
            z-index: -1;
            pointer-events: none;
        }
        .hero-content { max-width: 800px; margin: 0 auto; }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: var(--bg-card);
            border: 1px solid var(--border-primary);
            color: var(--text-primary);
            padding: 0.5rem 1.25rem;
            border-radius: 2rem;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
        }
        .hero-badge i { color: var(--success); }
        .hero-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        /* Social Proof Section - Enhanced */
        .social-proof {
            padding-top: 0;
            padding-bottom: 6rem;
            text-align: center;
        }
        .social-proof p {
            color: var(--text-muted);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 500;
        }
        .social-proof-logos {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 4rem;
            flex-wrap: wrap;
            filter: grayscale(1) opacity(0.6);
        }
        .social-proof-logos span {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-secondary);
        }
        
        /* How It Works Section - COMPLETELY REDESIGNED */
        .how-it-works { 
            background-color: var(--bg-secondary);
            position: relative;
        }
        .how-it-works-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2.5rem;
        }
        .how-it-works .card { 
            position: relative; 
            padding: 2.5rem 2rem 2rem;
            display: flex;
            flex-direction: column;
            border: 1px solid var(--border-primary);
            background: var(--bg-card);
            border-radius: 1.25rem;
            overflow: visible;
            transition: all 0.3s ease;
        }
        .how-it-works .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            border-color: var(--primary-blue);
        }
        .step-number {
            position: absolute;
            top: -1.5rem;
            left: 2rem;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-blue-dark));
            color: white;
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            border: 4px solid var(--bg-secondary);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
            z-index: 2;
        }
        .how-it-works .card h3 {
            font-size: 1.5rem;
            margin: 1.5rem 0 1rem;
            font-weight: 700;
            color: var(--text-heading);
        }
        .how-it-works .card p {
            color: var(--text-secondary);
            line-height: 1.7;
            margin-bottom: 0;
        }
        
        /* Features Section - Enhanced */
        .features-grid { 
            display: grid; 
            grid-template-columns: repeat(3, 1fr); 
            gap: 2rem; 
        }
        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            color: var(--primary-blue);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background-color: rgba(0, 123, 255, 0.1);
        }

        /* About Section - NEW */
        .about-section {
            background-color: var(--bg-secondary);
            position: relative;
        }
        .about-section .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            gap: 4rem;
        }
        .about-image-placeholder {
            width: 100%;
            height: 400px;
            background: linear-gradient(145deg, var(--bg-card) 0%, var(--bg-accent) 100%);
            border-radius: var(--border-radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-primary);
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }
        .about-image-placeholder i {
            font-size: 10rem;
            color: var(--primary-blue);
            opacity: 0.8;
            text-shadow: 0 0 30px rgba(0, 123, 255, 0.3);
            z-index: 1;
            position: relative;
        }

        /* Security Section - Enhanced */
        .security-section {
            background-color: var(--bg-primary);
            position: relative;
            overflow: hidden;
        }
        .security-section .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            gap: 4rem;
        }
        .security-image-placeholder {
            width: 100%;
            height: 400px;
            background: linear-gradient(145deg, var(--bg-card) 0%, var(--bg-accent) 100%);
            border-radius: var(--border-radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-primary);
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }
        .security-image-placeholder i {
            font-size: 10rem;
            color: var(--primary-blue);
            opacity: 0.8;
            text-shadow: 0 0 30px rgba(0, 123, 255, 0.3);
            z-index: 1;
            position: relative;
        }

        /* Pricing Section - Enhanced */
        .pricing-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            max-width: 900px;
            margin: 0 auto;
            align-items: start;
        }
        .pricing-card {
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }
        .pricing-card.popular {
            border: 2px solid var(--primary-blue);
            box-shadow: var(--shadow-blue);
            transform: scale(1.03);
        }
        .popular-badge {
            position: absolute;
            top: 15px;
            right: -35px;
            background: linear-gradient(45deg, var(--primary-blue), var(--primary-blue-light));
            color: white;
            padding: 0.5rem 3rem;
            font-size: 0.8rem;
            font-weight: 600;
            transform: rotate(45deg);
            box-shadow: var(--shadow-md);
        }
        .price { 
            font-size: 3.5rem; 
            font-weight: 700; 
            color: var(--text-heading); 
            margin: 0.5rem 0; 
            display: flex;
            align-items: baseline;
            justify-content: center;
            gap: 0.25rem;
        }
        .price .price-period { 
            color: var(--text-secondary); 
            font-size: 1rem; 
            font-weight: 400; 
        }
        .price-features {
            list-style: none;
            margin: 2rem 0;
            flex-grow: 1;
            text-align: left;
        }
        .price-features li { 
            display: flex; 
            align-items: center; 
            gap: 0.75rem; 
            color: var(--text-primary); 
            margin-bottom: 0.75rem; 
            padding: 0.25rem 0;
        }
        .price-features i { font-size: 1.2rem; }
        .price-features .li-green i { color: var(--success); }
        .price-features .li-red i { color: var(--danger); }
        
        /* FAQ Section - Enhanced */
        .faq-section { 
            background-color: var(--bg-secondary);
            position: relative;
        }
        .faq-container { max-width: 800px; margin: 2rem auto 0; }
        .faq-item { 
            border-bottom: 1px solid var(--border-primary);
            transition: all 0.3s ease;
        }
        .faq-item:first-child { border-top: 1px solid var(--border-primary); }
        .faq-question {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            padding: 1.5rem 0;
            transition: all 0.3s ease;
        }
        .faq-question h3 { 
            margin-bottom: 0; 
            font-size: 1.2rem; 
            transition: color 0.3s ease; 
            font-weight: 600;
        }
        .faq-question:hover { background-color: rgba(255, 255, 255, 0.02); }
        .faq-question:hover h3 { color: var(--primary-blue-light); }
        .faq-question span i { 
            font-size: 1.5rem; 
            transition: transform 0.3s ease; 
            color: var(--text-secondary);
        }
        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease-out, padding-bottom 0.4s ease-out;
        }
        .faq-answer.active { 
            max-height: 500px; 
            padding-bottom: 1.5rem; 
        }
        .faq-item.active .faq-question span i { 
            transform: rotate(45deg); 
            color: var(--primary-blue);
        }

        /* CTA Section - Enhanced */
        .cta-section {
            position: relative;
        }
        .cta-card {
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid var(--border-secondary);
            background: var(--bg-secondary);
            padding: 4rem 3rem;
            border-radius: var(--border-radius-xl);
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }
        .cta-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-blue), var(--primary-blue-light));
        }
        .cta-card h2 { margin-bottom: 1rem; }
        .cta-card p { margin-bottom: 2rem; }

        /* Contact Section - NEW */
        .contact-section {
            background-color: var(--bg-primary);
            position: relative;
        }
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: start;
        }
        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }
        .contact-icon {
            font-size: 1.5rem;
            color: var(--primary-blue);
            margin-top: 0.25rem;
            flex-shrink: 0;
        }
        .contact-details h4 {
            margin-bottom: 0.5rem;
            color: var(--text-heading);
        }
        .contact-details p {
            margin-bottom: 0;
            color: var(--text-secondary);
        }

        /* Footer - Enhanced */
        footer {
            background-color: var(--bg-secondary);
            padding: 4rem 0 2rem;
            border-top: 1px solid var(--border-primary);
        }
        .footer-grid { 
            display: grid; 
            grid-template-columns: 2fr 1fr 1fr 1fr; 
            gap: 3rem; 
        }
        .footer-logo { 
            font-size: 1.5rem; 
            font-weight: 700; 
            margin-bottom: 1rem; 
            display: inline-block;
        }
        .footer-logo span { color: var(--primary-blue); }
        .footer-social-icons { 
            display: flex;
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        .footer-social-icons a { 
            font-size: 1.5rem; 
            color: var(--text-secondary); 
            transition: all 0.3s ease;
        }
        .footer-social-icons a:hover { 
            color: var(--primary-blue); 
            transform: translateY(-2px);
        }
        .footer-links { list-style: none; }
        .footer-links li { margin-bottom: 0.75rem; }
        .footer-links a { 
            color: var(--text-secondary); 
            transition: color 0.3s ease; 
            font-size: 0.95rem;
        }
        .footer-links a:hover { 
            color: var(--primary-blue); 
        }
        .footer-bottom {
            margin-top: 4rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        /* Mobile Navigation - Enhanced */
        .mobile-nav {
            position: fixed;
            top: 0;
            right: -100%;
            width: 85%;
            max-width: 320px;
            height: 100%;
            background-color: var(--bg-secondary);
            z-index: 1000;
            padding: 6rem 2rem 2rem;
            transition: right 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            box-shadow: var(--shadow-xl);
            overflow-y: auto;
            border-left: 1px solid var(--border-primary);
        }
        .mobile-nav.active { right: 0; }
        .mobile-nav-links { 
            list-style: none; 
            margin-bottom: 2rem; 
        }
        .mobile-nav-links li { 
            margin-bottom: 0.5rem; 
            border-bottom: 1px solid var(--border-primary);
        }
        .mobile-nav-links a { 
            color: var(--text-primary); 
            font-weight: 500; 
            font-size: 1.2rem; 
            display: block; 
            padding: 1rem 0; 
            transition: color 0.3s ease;
        }
        .mobile-nav-links a:hover { 
            color: var(--primary-blue); 
        }
        .mobile-nav-actions { 
            display: flex; 
            flex-direction: column; 
            gap: 1rem; 
            margin-top: 2rem;
        }
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .overlay.active { 
            opacity: 1; 
            visibility: visible; 
        }

        /* Responsive Styles - Enhanced */
        @media (max-width: 1024px) {
            .how-it-works-grid, .features-grid { 
                grid-template-columns: repeat(2, 1fr); 
            }
            .security-section .grid,
            .about-section .grid,
            .contact-grid { 
                gap: 2rem; 
            }
            .footer-grid {
                grid-template-columns: 1fr 1fr;
                gap: 2.5rem;
            }
        }

        @media (max-width: 768px) {
            :root { 
                --section-padding: 4rem 1.5rem; 
            }
            
            .nav-links, .nav-actions .btn { 
                display: none; 
            }
            .mobile-menu-btn { 
                display: block; 
            }
            
            .hero-actions {
                flex-direction: column;
                align-items: center;
            }
            .hero-actions .btn { 
                width: 100%; 
                max-width: 320px; 
            }

            .social-proof-logos { 
                gap: 2rem; 
            }
            .social-proof-logos span { 
                font-size: 1.2rem; 
            }

            .how-it-works-grid, .features-grid, .pricing-grid, 
            .security-section .grid, .about-section .grid, .contact-grid {
                grid-template-columns: 1fr;
            }
            
            .security-image-placeholder,
            .about-image-placeholder { 
                grid-row: 1; 
                margin-bottom: 2rem; 
                height: 300px;
            }
            .security-image-placeholder i,
            .about-image-placeholder i {
                font-size: 7rem;
            }
            .pricing-card.popular { 
                transform: scale(1); 
            }
            
            .footer-grid { 
                grid-template-columns: 1fr; 
                gap: 2.5rem; 
                text-align: center;
            }
            .footer-social-icons {
                justify-content: center;
            }
            .footer-bottom {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }
        
        @media (max-width: 480px) {
            .hero {
                padding-top: 10rem;
                padding-bottom: 6rem;
            }
            .hero-badge {
                font-size: 0.8rem;
                padding: 0.4rem 1rem;
            }
            .btn-large { 
                padding: 1rem 1.8rem; 
                font-size: 1rem; 
            }
            .card {
                padding: 2rem 1.5rem;
            }
            .feature-icon {
                width: 60px;
                height: 60px;
                font-size: 2rem;
            }
            .cta-card {
                padding: 3rem 1.5rem;
            }
            .price {
                font-size: 3rem;
            }
            .how-it-works .card {
                padding: 2rem 1.5rem 1.5rem;
            }
            .step-number {
                width: 3rem;
                height: 3rem;
                font-size: 1.25rem;
                left: 1.5rem;
            }
            .contact-item {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }
            .contact-icon {
                margin-top: 0;
            }
        }

        /* Additional Utility Classes */
        .mt-2 { margin-top: 2rem; }
        .mb-0 { margin-bottom: 0; }
        .mb-1 { margin-bottom: 1rem; }
        .mb-2 { margin-bottom: 2rem; }
        .mb-3 { margin-bottom: 3rem; }
        .mb-4 { margin-bottom: 4rem; }

        /* Scroll Progress Bar */
        .scroll-progress {
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-blue), var(--primary-blue-light));
            z-index: 1001;
            transition: width 0.1s ease;
        }

        /* Modal Styles - Added */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 2000;
            overflow-y: auto;
            padding: 2rem 1rem;
        }
        
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content-f {
            background-color: var(--bg-card);
            border-radius: var(--border-radius-lg);
            width: 100%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            border: 1px solid var(--border-primary);
            box-shadow: var(--shadow-xl);
            position: relative;
        }
        
        .modal-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background-color: var(--bg-card);
            z-index: 10;
        }
        
        .modal-header h5 {
            font-size: 1.5rem;
            margin: 0;
            color: var(--text-heading);
        }
        
        .modal-close {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.5rem;
            cursor: pointer;
            transition: color 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
        }
        
        .modal-close:hover {
            color: var(--text-primary);
            background-color: var(--bg-accent);
        }
        
        .modal-body {
            padding: 2rem;
            color: var(--text-secondary);
        }
        
        .modal-body h5 {
            color: var(--text-heading);
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            font-size: 1.2rem;
        }
        
        .modal-body p {
            margin-bottom: 1rem;
            line-height: 1.7;
        }
        
        .modal-body p strong {
            color: var(--text-primary);
        }
        
        .modal-footer {
            padding: 1.5rem 2rem;
            border-top: 1px solid var(--border-primary);
            display: flex;
            justify-content: flex-end;
            background-color: var(--bg-card);
            position: sticky;
            bottom: 0;
        }
    </style>
</head>
<body>
    <!-- Scroll Progress Bar -->
    <div class="scroll-progress"></div>

    <!-- Header -->
    <header>
        <div class="container header-container">
            <a href="#" class="logo"><i class="ri-settings-2-fill"></i><span>Wolfallet</span></a>
            <nav>
                <ul class="nav-links">
                    <li><a href="#features">Features</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#security">Security</a></li>
                    <li><a href="#pricing">Pricing</a></li>
                    <li><a href="#faq">FAQ</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </nav>
            <div class="nav-actions">
                <a href="#pricing" class="btn btn-secondary">Go Premium</a>
                <a href="#" class="btn btn-primary">Get Started</a>
            </div>
            <button class="mobile-menu-btn"><i class="ri-menu-line"></i></button>
        </div>
    </header>

    <!-- Mobile Navigation -->
    <div class="mobile-nav">
        <ul class="mobile-nav-links">
            <li><a href="#features">Features</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#security">Security</a></li>
            <li><a href="#pricing">Pricing</a></li>
            <li><a href="#faq">FAQ</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
        <div class="mobile-nav-actions">
            <a href="#pricing" class="btn btn-secondary">Go Premium</a>
            <a href="#" class="btn btn-primary">Get Started</a>
        </div>
    </div>
    <div class="overlay"></div>

    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <span class="hero-badge"><i class="ri-flashlight-fill"></i> Alpha Launch: Now testing and absolutely free</span>
                <h1>Wallet with security & simplicity.</h1>
                <center><p class="text-center">A fast, secure, and simple password wallet without the bloat. Tired of browser password managers failing you? Take back control of your digital security.</p></center>
                <div class="hero-actions">
                    <a href="#" class="btn btn-primary btn-large">Get Started For Free</a>
                    <a href="#how-it-works" class="btn btn-secondary btn-large">Learn More</a>
                </div>
            </div>
        </section>

        <!-- Social Proof Section
        <section class="social-proof">
            <div class="container">
                <p>Trusted by security-conscious users worldwide</p>
                <div class="social-proof-logos">
                    <span>TechRadar</span>
                    <span>Wired</span>
                    <span>The Verge</span>
                    <span>Security Today</span>
                </div>
            </div>
        </section> -->

        <!-- How It Works Section - REDESIGNED -->
        <section id="how-it-works" class="how-it-works">
            <div class="container">
                <div class="section-header text-center">
                    <h2>How Wolfallet Protects You</h2>
                    <p>0 knowledge architecture ensures that you, and only you, have access to your data through a simple and secure process.</p>
                </div>
                <div class="how-it-works-grid">
                    <div class="card">
                        <div class="step-number">1</div>
                        <h3>Create Your Secure Vault</h3>
                        <p>Sign up anonymously with just a username and a strong master password. This password is your key and is never stored on the servers.</p>
                    </div>
                    <div class="card">
                        <div class="step-number">2</div>
                        <h3>Encrypt Data Locally</h3>
                        <p>Every piece of information you save is instantly encrypted on your device using military-grade AES-256 before it's ever sent to the cloud.</p>
                    </div>
                    <div class="card">
                        <div class="step-number">3</div>
                        <h3>Sync Across Devices</h3>
                        <p>The encrypted data syncs seamlessly across your devices. It's only ever decrypted locally when you enter your master password.</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Features Section -->
        <section id="features">
            <div class="container">
                <div class="section-header text-center">
                    <h2>Everything You Need, Nothing You Don't</h2>
                    <p>Wolfallet focuses on the essential features for secure password management without the unnecessary bloat found in other applications.</p>
                </div>
                <div class="features-grid">
                    <div class="card text-center">
                        <div class="feature-icon"><i class="ri-user-unfollow-line"></i></div>
                        <h3>True Anonymity</h3>
                        <p>No email or phone required. Your account is tied only to your username and master password for absolute privacy.</p>
                    </div>
                    <div class="card text-center">
                        <div class="feature-icon"><i class="ri-shield-keyhole-line"></i></div>
                        <h3>End-to-End Encryption</h3>
                        <p>Military-grade AES-256 encryption secures your data. Even I can't see your passwords or personal information.</p>
                    </div>
                    <div class="card text-center">
                        <div class="feature-icon"><i class="ri-swap-box-line"></i></div>
                        <h3>Cross-Device Sync</h3>
                        <p>Instant, reliable synchronization across all your devices. Enjoy a consistent and up-to-date vault everywhere.</p>
                    </div>
                    <div class="card text-center">
                        <div class="feature-icon"><i class="ri-price-tag-3-line"></i></div>
                        <h3>Smart Organization</h3>
                        <p>Use labels and categories to organize your vault. My powerful search makes finding credentials effortless.</p>
                    </div>
                    <div class="card text-center">
                        <div class="feature-icon"><i class="ri-key-2-line"></i></div>
                        <h3>Password Generator</h3>
                        <p>Generate strong, unique passwords with a single click to secure your accounts against breaches instantly.</p>
                    </div>
                    <div class="card text-center">
                        <div class="feature-icon"><i class="ri-download-cloud-2-line"></i></div>
                        <h3>Data Ownership</h3>
                        <p>You have full control. Export your encrypted vault anytime. You own your data, not me. I make it easy to leave if you choose.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section - NEW -->
        <section id="about" class="about-section">
            <div class="container">
                <div class="grid">
                    <div>
                        <h2>About Wolfallet</h2>
                        <p>Wolfallet was born from my own frustration, existing password managers were either too complex, too restrictive, or made questionable compromises with your data. As a security enthusiast who believed there had to be a better way, I created Wolfallet to represent a fresh approach to password management.</p>
<p>My mission is straightforward: put you in complete control of your digital security without sacrificing simplicity or privacy. No corporate overlords, no investor demands, no hidden agendas just my passionate commitment to building a password manager that actually serves your needs first.</p>
<p>I believe strong security shouldn't require a degree in cryptography, and that your passwords should belong to you and you alone. That's the Wolfallet promise.</p>
                        <div class="hero-actions" style="justify-content: flex-start;">
                            <a href="#" class="btn btn-primary">Explore</a>
                        </div>
                    </div>
                    <div class="about-image-placeholder">
                        <i class="ri-team-line"></i>
                    </div>
                </div>
            </div>
        </section>

        <!-- Security Section -->
        <section id="security" class="security-section">
            <div class="container">
                <div class="grid">
                    <div>
                        <h2>Zero Knowledge. Total Security.</h2>
                        <p>Wolfallet is built on a zero-knowledge architecture. This means your data is encrypted and decrypted locally on your device. Only you can access your vault with your master password I never have access to it, ever.</p>
                         <div class="hero-actions" style="justify-content: flex-start;">
                            <a href="#" class="btn btn-primary">Read Security Whitepaper</a>
                        </div>
                    </div>
                    <div class="security-image-placeholder">
                        <i class="ri-shield-check-line"></i>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing">
            <div class="container">
                <div class="section-header text-center">
                    <h2>Simple, Transparent Pricing</h2>
                    <p>No hidden fees, no surprises. Choose the plan that works for you. Start for free today.</p>
                </div>
                <div class="pricing-grid">
                    <div class="card pricing-card text-center">
                        <h3>Free</h3>
                        <p>Perfect for trying out Wolfallet</p>
                        <div class="price">$0 <span class="price-period">/ Forever</span></div>
                        <ul class="price-features">
                            <li class="li-green"><i class="ri-checkbox-circle-fill"></i> Up to 25 Password Entries</li>
                            <li class="li-green"><i class="ri-checkbox-circle-fill"></i> Military-Grade Encryption</li>
                            <li class="li-green"><i class="ri-checkbox-circle-fill"></i> Cross-Device Sync</li>
                            <li class="li-red"><i class="ri-close-circle-fill"></i> Password Generator</li>
                            <li class="li-red"><i class="ri-close-circle-fill"></i> Priority Support</li>
                        </ul>
                        <a href="#" class="btn btn-secondary btn-large" style="width: 100%; margin-top: auto;">Get Started Free</a>
                    </div>
                    <div class="card pricing-card popular text-center">
                        <div class="popular-badge">Popular</div>
                        <h3>Premium</h3>
                        <p>For ultimate security and features</p>
                        <div class="price">$1 <span class="price-period">/ month</span></div>
                        <ul class="price-features">
                            <li class="li-green"><i class="ri-checkbox-circle-fill"></i> Unlimited Password Entries</li>
                            <li class="li-green"><i class="ri-checkbox-circle-fill"></i> Military-Grade Encryption</li>
                            <li class="li-green"><i class="ri-checkbox-circle-fill"></i> Cross-Device Sync</li>
                            <li class="li-green"><i class="ri-checkbox-circle-fill"></i> Password Generator</li>
                            <li class="li-green"><i class="ri-checkbox-circle-fill"></i> Priority Support</li>
                        </ul>
                        <a href="#" class="btn btn-primary btn-large" style="width: 100%; margin-top: auto;">Go Premium</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section id="faq" class="faq-section">
            <div class="container">
                <h2 class="text-center">Frequently Asked Questions</h2>
                <div class="faq-container">
                    <div class="faq-item">
                        <div class="faq-question"><h3>Is Wolfallet truly secure?</h3><span><i class="ri-add-line"></i></span></div>
                        <div class="faq-answer"><p>Absolutely. Wolfallet uses a zero-knowledge architecture with end-to-end AES-256 encryption. This means your data is encrypted on your device before being sent to my servers. I never have access to your master password or your unencrypted data.</p></div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question"><h3>What happens if I forget my master password?</h3><span><i class="ri-add-line"></i></span></div>
                        <div class="faq-answer"><p>Due to my zero-knowledge security model, I cannot recover your master password or your account data. This is a crucial part of my security promise. I strongly recommend storing your master password in a safe, offline location.</p></div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question"><h3>Can I import my passwords from other managers?</h3><span><i class="ri-add-line"></i></span></div>
                        <div class="faq-answer"><p>Yes. I am building a robust import tool to easily migrate your passwords from all major browsers and other password managers. This feature is currently in development during my alpha phase and will be available soon.</p></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container">
                <div class="card cta-card">
                    <h2>Take Control of Your Passwords Today</h2>
                    <center><p class="text-center">No credit card required. I am in an active alpha launch and open for feedback! Your insights are invaluable as I build a more robust and secure platform.</p></center>
                    <a href="#" class="btn btn-primary btn-large">Get Started For Free</a>
                </div>
            </div>
        </section>

        <!-- Contact Section - NEW -->
        <section id="contact" class="contact-section">
            <div class="container">
                <div class="section-header text-center">
                    <h2>Contact Support</h2>
                    <p>Have questions, feedback, or want to report an issue?</p>
                </div>
                <div class="contact-grid">
                    <div class="contact-info">
                        <div class="contact-item">
                            <div class="contact-icon"><i class="ri-mail-line"></i></div>
                            <div class="contact-details">
                                <h4>Email</h4>
                                <p>ayanabha.c@aol.com</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="ri-github-fill"></i></div>
                            <div class="contact-details">
                                <h4>GitHub</h4>
                                <p>github.com/ayahack89/wolfallet</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="ri-twitter-x-line"></i></div>
                            <div class="contact-details">
                                <h4>Twitter</h4>
                                <p>@ayanabha08</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h3>Get In Touch</h3>
                        <p>As an alpha product, your feedback is incredibly valuable. Help me to shape the future of Wolfallet by sharing your thoughts and experiences.</p>
                        <p>Whether you have feature suggestions, found a bug, or just want to share your experience, I'm always here to listen and improve.</p>
                        <div class="hero-actions" style="justify-content: flex-start; margin-top: 2rem;">
                            <a href="mailto:ayanabha.c@aol.com" class="btn btn-primary">Send me an Email</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

     <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div>
                  <a href="#" class="footer-logo"><i class="ri-settings-2-fill"></i><span>Wolfallet</span></a>
                    <p>A fast, secure, and simple password wallet without the bloat.</p>
                    <div class="footer-social-icons">
                         <ul class="footer-links">
                        <li><a href="#" id="privacyLink">Privacy Policy</a></li>
                        <li><a href="#" id="termsLink">Terms of Service</a></li>
                    </ul>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <div>© 2025 Wolfallet. All rights reserved.</div>
                <div>Made with <i class="ri-heart-fill" style="color: var(--danger);"></i> for privacy-conscious users</div>
            </div>
        </div>
    </footer>

    <!-- Terms & Conditions Modal -->
    <div class="modal" id="termsModal">
        <div class="modal-content-f">
            <div class="modal-header">
                <h5 class="modal-title">Terms & Conditions</h5>
                <button type="button" class="modal-close" aria-label="Close"><i class="ri-close-line"></i></button>
            </div>
            <div class="modal-body">
                <h5>Welcome to Wolfallet</h5>
                <p>By using Wolfallet, you're agreeing to these terms. I've kept them straightforward because I believe you deserve clarity, not legal jargon.</p>

                <h5>Your Responsibility</h5>
                <p>Wolfallet is a password management tool built to keep your credentials safe. Your master password is the key to everything keep it secure and memorable. I've designed the system so that only you can access your data, which means if you lose your master password, I cannot recover it for you. Please store it safely.</p>

                <h5>How I Protect Your Data</h5>
                <p>Your vault is encrypted end-to-end using industry-standard encryption. I cannot see, access, or decrypt your stored passwords. Your data belongs to you, and only you hold the key.</p>

                <h5>Beta Program & Pricing</h5>
                <p>Wolfallet is currently in beta and completely free for anyone who signs up within the first 30 days of launch. Your lifetime access is locked in no expiration, no future charges for founding users. After the beta period, I'll introduce a paid subscription model for new users. Pricing details will be announced before the transition.</p>

                <h5>Refund Policy</h5>
                <p>Once a payment is processed (for future paid plans), all sales are final and non-refundable. I'm committed to delivering value, but please make sure Wolfallet fits your needs before subscribing.</p>

                <h5>Account Recovery</h5>
                <p>Because of the strong encryption I use, if you forget your master password, there's no way for me to reset it or recover your data. Security comes first, but that means you need to remember your password or store it somewhere safe.</p>

                <h5>Cross-Device Sync</h5>
                <p>Sign in from any device, and your encrypted vault syncs automatically. Your passwords follow you wherever you go securely.</p>

                <h5>Exporting Your Data</h5>
                <p>You own your data. You can export your entire vault as a secure .csv file anytime through the Manage Passwords section. Take it with you if you ever decide to leave.</p>

                <h5>Deleting Your Account</h5>
                <p>If you want to delete your account, head to User Profile → Delete Profile. This will permanently erase all your stored data from my servers. This action cannot be undone, so please be certain before proceeding.</p>

                <h5>Service Availability</h5>
                <p>I work hard to keep Wolfallet running smoothly, but like any service, there may be occasional downtime for maintenance or unexpected issues. I'll do my best to notify you in advance and resolve problems quickly.</p>

                <h5>Changes to These Terms</h5>
                <p>As Wolfallet grows, I may need to update these terms. If I make significant changes, I'll notify you via email or through the app. Continuing to use the service means you accept the updated terms.</p>

                <h5>Limitation of Liability</h5>
                <p>I've built Wolfallet with security and reliability in mind, but I cannot guarantee it will be error-free or uninterrupted. You use Wolfallet at your own risk. I'm not liable for any data loss, security breaches, or damages resulting from your use of the service so please keep backups of critical information.</p>

                <h5>Contact & Support</h5>
                <p>Got questions? Facing issues? Want to share feedback? I'm here for you. Reach out through the contact form or support section, and I'll get back to you as quickly as possible.</p>

                <h5>Final Note</h5>
                <p>I built Wolfallet because I believe everyone deserves simple, strong security. Thank you for trusting me with your digital safety. Let's build something great together.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close-btn">Close</button>
            </div>
        </div>
    </div>

    <!-- Privacy Policy Modal -->
    <div class="modal" id="privacyModal">
        <div class="modal-content-f">
            <div class="modal-header">
                <h5 class="modal-title">Privacy Policy</h5>
                <button type="button" class="modal-close" aria-label="Close"><i class="ri-close-line"></i></button>
            </div>
            <div class="modal-body">
                <h5>Your Privacy Matters</h5>
                <p>I built Wolfallet with privacy at its core. This policy explains what data I collect, how I protect it, and what rights you have. I believe in transparency, so I've written this in plain language.</p>

                <h5>What Information I Collect</h5>
                <p>I collect only what's necessary to make Wolfallet work for you:</p>
                <p><strong>Account Information:</strong> Your email address and account credentials (your master password is never stored only a secure hash).</p>
                <p><strong>Encrypted Vault Data:</strong> Your stored passwords and credentials, fully encrypted before they ever reach my servers.</p>
                <p><strong>Usage Data:</strong> Basic information like login times, device types, and feature usage to improve the service and troubleshoot issues.</p>
                <p><strong>Payment Information:</strong> If you subscribe to a paid plan in the future, payment details are processed through secure third-party payment processors. I never see or store your full credit card information.</p>

                <h5>How I Protect Your Data</h5>
                <p>Security isn't just a feature it's the foundation of Wolfallet:</p>
                <p><strong>End-to-End Encryption:</strong> Your vault is encrypted using AES-256-CBC encryption on your device before it's sent to my servers. I cannot decrypt or access your passwords only you hold the key.</p>
                <p><strong>Zero-Knowledge Architecture:</strong> Your master password never leaves your device in plain text. I store only a cryptographic hash, which means even I can't see what's inside your vault.</p>
                <p><strong>Secure Transmission:</strong> All data transfers between your device and my servers use TLS/SSL encryption.</p>
                <p><strong>Regular Security Audits:</strong> I continuously monitor and update security measures to protect against emerging threats.</p>

                <h5>How I Use Your Information</h5>
                <p>I use your data solely to:</p>
                <p>• Provide and maintain Wolfallet's services<br>
                    • Sync your encrypted vault across your devices<br>
                    • Send important service updates, security alerts, or account notifications<br>
                    • Improve features and fix bugs based on usage patterns<br>
                    • Respond to your support requests and feedback</p>

                <h5>What I Don't Do With Your Data</h5>
                <p>Let me be clear: I will never sell, rent, trade, or share your personal information with third parties for marketing purposes. Your data is yours, not a product.</p>
                <p>The only exception is if I'm legally required to disclose information by law enforcement or court order—and even then, your encrypted vault data remains inaccessible to everyone, including me.</p>

                <h5>Third-Party Services</h5>
                <p>Wolfallet uses a few trusted third-party services to function:</p>
                <p><strong>Hosting Providers:</strong> Secure cloud infrastructure to store encrypted data.<br>
                    <strong>Payment Processors:</strong> For handling subscriptions (when applicable).<br>
                    <strong>Analytics Tools:</strong> Anonymous usage statistics to improve the app.
                </p>
                <p>These partners are bound by strict confidentiality agreements and cannot access your encrypted vault data.</p>

                <h5>Data Retention</h5>
                <p>I keep your data only as long as your account is active. If you delete your account, all your data—including your encrypted vault is permanently removed from my servers within 30 days. Backups are securely wiped after this period.</p>

                <h5>Your Rights & Control</h5>
                <p>You're in control of your data. You have the right to:</p>
                <p>• <strong>Access:</strong> View what data I store about you.<br>
                    • <strong>Export:</strong> Download your vault data as a .csv file anytime.<br>
                    • <strong>Correct:</strong> Update your account information whenever you need.<br>
                    • <strong>Delete:</strong> Permanently erase your account and all associated data through User Profile → Delete Profile.</p>

                <h5>Cookies & Tracking</h5>
                <p>Wolfallet uses minimal cookies to keep you logged in and remember your preferences. I don't use intrusive tracking or sell your browsing data to advertisers. You can manage cookie settings in your browser, but disabling them may affect functionality.</p>

                <h5>Data Breach Protocol</h5>
                <p>In the unlikely event of a security breach, I'll notify you immediately via email and provide clear guidance on protective steps. Thanks to zero-knowledge encryption, even in a breach scenario, your vault data remains secure and unreadable.</p>

                <h5>International Users</h5>
                <p>If you're using Wolfallet from outside my hosting region, your data may be transferred and stored in data centers located in different countries. Rest assured, your data is always encrypted and protected under the same strict privacy standards, regardless of location.</p>

                <h5>Changes to This Policy</h5>
                <p>As Wolfallet evolves, I may update this privacy policy. I'll notify you of significant changes via email or through the app. Your continued use of the service means you accept the updated policy.</p>

                <h5>Children's Privacy</h5>
                <p>Wolfallet is not intended for users under 13 years old. I do not knowingly collect personal information from children. If you believe a child has created an account, please contact me immediately so I can delete it.</p>

                <h5>Contact Me</h5>
                <p>Have questions about privacy? Want to exercise your data rights? I'm here to help. Reach out through the support section, and I'll respond as quickly as possible.</p>

                <h5>My Commitment to You</h5>
                <p>Privacy isn't an afterthought it's why Wolfallet exists. I'm committed to protecting your data with the same care I'd want for my own. Thank you for trusting me with your security.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close-btn">Close</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // All existing JavaScript code remains unchanged
            // ... existing JavaScript code ...
            
            // Modal Functionality - Added
            const privacyLink = document.getElementById('privacyLink');
            const termsLink = document.getElementById('termsLink');
            const privacyModal = document.getElementById('privacyModal');
            const termsModal = document.getElementById('termsModal');
            const closeButtons = document.querySelectorAll('.modal-close, .modal-close-btn');
            const modals = document.querySelectorAll('.modal');
            
            // Open Privacy Policy Modal
            privacyLink.addEventListener('click', (e) => {
                e.preventDefault();
                privacyModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
            
            // Open Terms Modal
            termsLink.addEventListener('click', (e) => {
                e.preventDefault();
                termsModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
            
            // Close Modals
            closeButtons.forEach(button => {
                button.addEventListener('click', () => {
                    modals.forEach(modal => {
                        modal.classList.remove('active');
                    });
                    document.body.style.overflow = '';
                });
            });
            
            // Close modal when clicking outside content
            modals.forEach(modal => {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        modal.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            });
            
            // Close modal with Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    modals.forEach(modal => {
                        modal.classList.remove('active');
                    });
                    document.body.style.overflow = '';
                }
            });
        });
    </script>
</body>
</html>