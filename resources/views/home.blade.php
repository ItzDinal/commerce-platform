@extends('layouts.app')

@section('title', 'nAARI | Traditional Elegance, Modern Grace')

@section('content')

    {{-- =========================================================
        NAVIGATION
    ========================================================== --}}
    <header class="site-header">
        <nav class="navbar" aria-label="Main navigation">

            <a href="{{ route('home') }}" class="navbar__logo">
                nAARI
            </a>

            <button
                type="button"
                class="navbar__toggle"
                aria-label="Open navigation menu"
                aria-expanded="false"
            >
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="navbar__menu">

                <a href="{{ route('home') }}" class="navbar__link">
                    Home
                </a>

                <a href="{{ route('about') }}" class="navbar__link">
                    About Us
                </a>

                <a href="#collections" class="navbar__link">
                    Collections
                </a>

                <a href="#contact" class="navbar__link">
                    Contact
                </a>

            </div>

        </nav>
    </header>


    <main>

        {{-- =====================================================
            HERO SECTION
        ====================================================== --}}
        <section class="hero">

            <div class="hero__content">

                <p class="hero__eyebrow">
                    Timeless Elegance
                </p>

                <h1 class="hero__title">
                    Where Tradition
                    Meets Modern Grace
                </h1>

                <p class="hero__description">
                    Discover thoughtfully curated pieces that celebrate
                    timeless beauty, culture, and contemporary elegance.
                </p>

                <div class="hero__actions">

                    <a href="#collections" class="button button--primary">
                        Explore Collection
                    </a>

                    <a href="#contact" class="button button--secondary">
                        Contact Us
                    </a>

                </div>

            </div>

            <div class="hero__image">

                {{-- Replace with the final nAARI hero image --}}
                <img
                    src="{{ asset('images/hero/hero.jpg') }}"
                    alt="nAARI fashion collection"
                    loading="eager"
                >

            </div>

        </section>


        {{-- =====================================================
            INTRODUCTION
        ====================================================== --}}
        <section class="intro section">

            <div class="section__container">

                <p class="section__eyebrow">
                    Welcome to nAARI
                </p>

                <h2 class="section__title">
                    Designed to celebrate you
                </h2>

                <p class="section__description">
                    nAARI brings together traditional inspiration and
                    contemporary style to create pieces made for the
                    modern woman.
                </p>

            </div>

        </section>


        {{-- =====================================================
            COLLECTIONS / PRODUCTS
        ====================================================== --}}
        <section
            id="collections"
            class="collections section"
        >

            <div class="section__container">

                <div class="section__header">

                    <div>
                        <p class="section__eyebrow">
                            Our Collection
                        </p>

                        <h2 class="section__title">
                            Explore Our Pieces
                        </h2>
                    </div>

                    <a href="#collections" class="section__link">
                        View All
                    </a>

                </div>


                <div class="product-grid">

                    {{-- Product Card --}}
                    <article class="product-card">

                        <a href="#" class="product-card__image">

                            <img
                                src="{{ asset('images/products/product-1.jpg') }}"
                                alt="Product name"
                                loading="lazy"
                            >

                        </a>

                        <div class="product-card__content">

                            <p class="product-card__category">
                                Collection
                            </p>

                            <h3 class="product-card__name">
                                Product Name
                            </h3>

                            <p class="product-card__price">
                                LKR 0.00
                            </p>

                            <a
                                href="#"
                                class="product-card__action"
                            >
                                Inquire on WhatsApp
                            </a>

                        </div>

                    </article>


                    {{-- Product Card --}}
                    <article class="product-card">

                        <a href="#" class="product-card__image">

                            <img
                                src="{{ asset('images/products/product-2.jpg') }}"
                                alt="Product name"
                                loading="lazy"
                            >

                        </a>

                        <div class="product-card__content">

                            <p class="product-card__category">
                                Collection
                            </p>

                            <h3 class="product-card__name">
                                Product Name
                            </h3>

                            <p class="product-card__price">
                                LKR 0.00
                            </p>

                            <a
                                href="#"
                                class="product-card__action"
                            >
                                Inquire on WhatsApp
                            </a>

                        </div>

                    </article>


                    {{-- Product Card --}}
                    <article class="product-card">

                        <a href="#" class="product-card__image">

                            <img
                                src="{{ asset('images/products/product-3.jpg') }}"
                                alt="Product name"
                                loading="lazy"
                            >

                        </a>

                        <div class="product-card__content">

                            <p class="product-card__category">
                                Collection
                            </p>

                            <h3 class="product-card__name">
                                Product Name
                            </h3>

                            <p class="product-card__price">
                                LKR 0.00
                            </p>

                            <a
                                href="#"
                                class="product-card__action"
                            >
                                Inquire on WhatsApp
                            </a>

                        </div>

                    </article>


                    {{-- Product Card --}}
                    <article class="product-card">

                        <a href="#" class="product-card__image">

                            <img
                                src="{{ asset('images/products/product-4.jpg') }}"
                                alt="Product name"
                                loading="lazy"
                            >

                        </a>

                        <div class="product-card__content">

                            <p class="product-card__category">
                                Collection
                            </p>

                            <h3 class="product-card__name">
                                Product Name
                            </h3>

                            <p class="product-card__price">
                                LKR 0.00
                            </p>

                            <a
                                href="#"
                                class="product-card__action"
                            >
                                Inquire on WhatsApp
                            </a>

                        </div>

                    </article>

                </div>

            </div>

        </section>


        {{-- =====================================================
            ABOUT / BRAND STORY
        ====================================================== --}}
        <section class="brand-story section">

            <div class="section__container brand-story__grid">

                <div class="brand-story__image">

                    <img
                        src="{{ asset('images/brand/nAARI-story.jpg') }}"
                        alt="The nAARI brand"
                        loading="lazy"
                    >

                </div>

                <div class="brand-story__content">

                    <p class="section__eyebrow">
                        Our Story
                    </p>

                    <h2 class="section__title">
                        Rooted in tradition.
                        Made for today.
                    </h2>

                    <p class="section__description">
                        nAARI is a celebration of timeless femininity,
                        culture, craftsmanship, and modern expression.
                    </p>

                    <a
                        href="{{ route('about') }}"
                        class="button button--secondary"
                    >
                        Discover Our Story
                    </a>

                </div>

            </div>

        </section>


        {{-- =====================================================
            WHATSAPP CTA
        ====================================================== --}}
        <section class="whatsapp-cta section">

            <div class="section__container">

                <div class="whatsapp-cta__content">

                    <p class="section__eyebrow">
                        Need Assistance?
                    </p>

                    <h2 class="section__title">
                        Find something you love?
                    </h2>

                    <p class="section__description">
                        Contact us directly through WhatsApp to
                        inquire about products, availability, and orders.
                    </p>

                    <a
                        href="#"
                        class="button button--primary"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Chat With Us on WhatsApp
                    </a>

                </div>

            </div>

        </section>


        {{-- =====================================================
            EMAIL SUBSCRIPTION
        ====================================================== --}}
        <section class="newsletter section">

            <div class="section__container">

                <div class="newsletter__content">

                    <p class="section__eyebrow">
                        Stay Connected
                    </p>

                    <h2 class="section__title">
                        Join the nAARI community
                    </h2>

                    <p class="section__description">
                        Subscribe to receive updates about new collections,
                        special offers, and stories from nAARI.
                    </p>

                    <form
                        action="#"
                        method="POST"
                        class="newsletter__form"
                    >

                        @csrf

                        <label
                            for="email"
                            class="sr-only"
                        >
                            Email address
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter your email address"
                            required
                            autocomplete="email"
                        >

                        <button
                            type="submit"
                            class="button button--primary"
                        >
                            Subscribe
                        </button>

                    </form>

                </div>

            </div>

        </section>

    </main>


    {{-- =========================================================
        FOOTER
    ========================================================== --}}
    <footer
        id="contact"
        class="site-footer"
    >

        <div class="site-footer__container">

            <div class="site-footer__brand">

                <a
                    href="{{ route('home') }}"
                    class="site-footer__logo"
                >
                    nAARI
                </a>

                <p>
                    Timeless elegance for the modern woman.
                </p>

            </div>


            <div class="site-footer__links">

                <div>

                    <h3>Explore</h3>

                    <a href="{{ route('home') }}">
                        Home
                    </a>

                    <a href="{{ route('about') }}">
                        About Us
                    </a>

                    <a href="#collections">
                        Collections
                    </a>

                </div>


                <div>

                    <h3>Contact</h3>

                    <a href="#">
                        WhatsApp
                    </a>

                    <a href="mailto:hello@naari.com">
                        hello@naari.com
                    </a>

                </div>

            </div>

        </div>


        <div class="site-footer__bottom">

            <p>
                &copy; {{ date('Y') }} nAARI. All rights reserved.
            </p>

        </div>

    </footer>

@endsection