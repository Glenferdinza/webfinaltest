<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portalis - Student Event Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <style>
        .font-inter {
            font-family: 'Inter', sans-serif;
        }
        .font-poppins {
            font-family: 'Poppins', sans-serif;
        }

        /* Custom Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-100px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(100px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        @keyframes sparkle {
            0%, 100% { opacity: 0; transform: scale(0.5) rotate(0deg); }
            50% { opacity: 1; transform: scale(1) rotate(180deg); }
        }

        @keyframes glow {
            0%, 100% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.3); }
            50% { box-shadow: 0 0 40px rgba(59, 130, 246, 0.6); }
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        .animate-float-delayed {
            animation: float 6s ease-in-out infinite;
            animation-delay: 2s;
        }

        .animate-gradient {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .card-hover {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-15px) scale(1.03);
            box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.25);
        }

        .bg-particles {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: #ffffff;
            border-radius: 50%;
            animation: sparkle 3s infinite;
        }

        .scroll-indicator {
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 4px;
            background: linear-gradient(to right, #3b82f6, #06b6d4, #10b981);
            z-index: 1000;
            transition: width 0.1s ease;
        }

        .navbar {
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: rgba(17, 24, 39, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .text-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn-glow {
            position: relative;
            overflow: hidden;
        }

        .btn-glow::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s;
        }

        .btn-glow:hover::before {
            left: 100%;
        }

        .reveal {
            opacity: 0;
            transform: translateY(50px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        .hero-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 30%, #334155 70%, #475569 100%);
            position: relative;
            overflow: hidden;
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            opacity: 0.15;
        }

        .shape-1 {
            top: 20%;
            left: 10%;
            width: 120px;
            height: 120px;
            background: linear-gradient(45deg, #3b82f6, #06b6d4);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }

        .shape-2 {
            top: 60%;
            right: 15%;
            width: 100px;
            height: 100px;
            background: linear-gradient(45deg, #f59e0b, #ef4444);
            transform: rotate(45deg);
            animation: float 6s ease-in-out infinite reverse;
        }

        .shape-3 {
            bottom: 20%;
            left: 20%;
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #10b981, #059669);
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            animation: float 10s ease-in-out infinite;
        }

        .image-hover {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .image-hover:hover {
            transform: scale(1.08) rotate(1deg);
            filter: brightness(1.1) saturate(1.2);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #1f2937;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, #3b82f6, #06b6d4);
            border-radius: 4px;
        }

        .section-transition {
            background: linear-gradient(180deg, transparent 0%, rgba(59, 130, 246, 0.05) 50%, transparent 100%);
        }

        .typing-effect {
            border-right: 2px solid #3b82f6;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 50% { border-right-color: transparent; }
            51%, 100% { border-right-color: #3b82f6; }
        }

        .morphing-bg {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab, #667eea, #764ba2);
            background-size: 600% 600%;
            animation: gradientShift 20s ease infinite;
        }

        .floating-icon {
            animation: float 4s ease-in-out infinite;
        }

        .floating-icon:nth-child(2) { animation-delay: 1s; }
        .floating-icon:nth-child(3) { animation-delay: 2s; }

        .glow-effect {
            animation: glow 2s ease-in-out infinite alternate;
        }

        .parallax {
            transform: translateZ(0);
        }
    </style>
</head>
<body class="bg-gray-50 font-inter text-gray-900 overflow-x-hidden">
    <div class="scroll-indicator"></div>

    <div class="hero-bg text-white px-8 py-4 text-center h-auto relative z-50">

        <div class="bg-particles">
            <div class="particle" style="left: 10%; top: 20%; animation-delay: 0s;"></div>
            <div class="particle" style="left: 20%; top: 30%; animation-delay: 1s;"></div>
            <div class="particle" style="left: 30%; top: 40%; animation-delay: 2s;"></div>
            <div class="particle" style="left: 40%; top: 50%; animation-delay: 3s;"></div>
            <div class="particle" style="left: 50%; top: 60%; animation-delay: 4s;"></div>
            <div class="particle" style="left: 60%; top: 70%; animation-delay: 5s;"></div>
            <div class="particle" style="left: 70%; top: 80%; animation-delay: 6s;"></div>
            <div class="particle" style="left: 80%; top: 25%; animation-delay: 7s;"></div>
            <div class="particle" style="left: 90%; top: 35%; animation-delay: 8s;"></div>
        </div>

        <div class="mx-40 mt-40 reveal">
            <img src="{{ $event->image ? Storage::url($event->image) : asset('images/default.jpg') }}"
                 alt="{{ $event->title }}"
                 class="w-auto h-[400px] object-cover mx-auto rounded-4xl shadow-2xl">
        </div>

        <div class="flex justify-between">
            <h2 class=" mx-25 mt-30 mb-20 text-white text-left text-5xl font-bold font-poppins leading-tight reveal">{{ $event->title }}</h2>
        </div>
    </div>

    <div class="relative z-50 -mt-16 reveal">
        <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-lg p-8 grid md:grid-cols-2 gap-6 text-gray-800">
            <div class="space-y-4">
                <div class="flex flex-col items-start">
                    <span class="text-gray-500">Event Organization</span>
                    <span class="text-3xl font-bold">{{ $event->organizer }}</span>
                    <span class="text-gray-500 py-5">Category : {{ $event->category }}</span>
                    <div class="flex gap-3">
                        @if(isset($event->participant_quota))
                        <span class="text-red-600 font-semibold bg-red-100 px-4 py-3 rounded-lg shadow-sm">
                            {{ $event->participant_quota }} participants
                        </span>
                        @endif

                        @if(isset($event->registration_fee))
                        <span class="text-green-600 font-semibold bg-green-100 px-4 py-3 rounded-lg shadow-sm">
                            @if(is_numeric($event->registration_fee) && $event->registration_fee == 0)
                                Free Registration
                            @elseif(strtolower($event->registration_fee) == 'free')
                                Free Registration
                            @else
                                Rp {{ number_format($event->registration_fee, 0, ',', '.') }}
                            @endif
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="space-y-4 my-auto">
                @if($event->date_start)
                <div class="flex items-center space-x-3">
                    <div class="bg-gray-800 rounded-full p-1">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <span>
                        {{ \Carbon\Carbon::parse($event->date_start)->format('j M') }}
                        @if($event->date_end && $event->date_end != $event->date_start)
                            - {{ \Carbon\Carbon::parse($event->date_end)->format('j M Y') }}
                        @else
                            {{ \Carbon\Carbon::parse($event->date_start)->format('Y') }}
                        @endif
                    </span>
                </div>
                @endif

                @if($event->location)
                <div class="flex items-center space-x-3">
                    <div class="bg-gray-800 rounded-full p-1">
                        {{-- Simple Pin Icon for location --}}
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span>{{ $event->location }}</span>
                </div>
                @endif

                @if($event->contact_email)
                <div class="flex items-center space-x-3">
                    <div class="bg-gray-800 rounded-full p-1">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <span>{{ $event->contact_email }}</span>
                </div>
                @endif

                @if($event->contact_phone)
                <div class="flex items-center space-x-3">
                    <div class="bg-gray-800 rounded-full p-1">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 5a2 2 0 012-2h2.6a1 1 0 01.97.757l.62 2.483a1 1 0 01-.243.957L7.4 9.6a11.037 11.037 0 005 5l1.403-1.403a1 1 0 01.957-.243l2.483.62a1 1 0 01.757.97V19a2 2 0 01-2 2h-1C9.373 21 3 14.627 3 7V5z" />
                        </svg>
                    </div>
                    <span>{{ $event->contact_phone }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>


    <section class="bg-white py-16 px-20">
        <div class="max-w-7xl mx-auto px-8">
            {{-- If description contains both sections --}}
            @if($event->description)
            <div class="reveal prose lg:prose-xl max-w-none text-gray-800 leading-relaxed text-justify">
                 {{-- Use {!! !!} to render HTML from description if it contains formatting --}}
                {!! $event->description !!}
            </div>
            @else
            {{-- Fallback if description is split or not available --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 reveal">
                <div>
                    <h3 class="text-3xl font-bold mb-4">Event Detail</h3>
                    <p class="text-gray-800 leading-relaxed mb-6 text-justify">
                        Detail for this event is not yet available. Please check back later.
                    </p>
                </div>
                <div>
                    <h3 class="text-3xl font-bold mb-4">Additional Information</h3>
                     <ul class="list-none space-y-4 text-gray-800 text-justify">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-1 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            More information will be provided soon.
                        </li>
                    </ul>
                </div>
            </div>
            @endif

            {{-- Action Buttons --}}
            {{-- Consider adding auth checks if these buttons should only be visible to admins/event owners --}}
            {{-- @auth --}}
            {{-- @if(auth()->user()->can('update', $event) || auth()->user()->isAdmin()) --}}
            <div class="flex flex-col sm:flex-row gap-6 py-16 justify-start items-center reveal">
                <a href="{{ route('events.edit', $event->id) }}" class="btn-glow px-8 py-4 bg-gradient-to-r from-green-500 to-emerald-400 text-gray-900 font-semibold rounded-2xl shadow-[0_0_30px_rgba(34,197,94,0.4)] hover:shadow-[0_0_50px_rgba(34,197,94,0.6)] transition-all duration-300 transform hover:scale-105 glow-effect">
                    Edit Event
                </a>
                <form action="{{ route('events.destroy', $event->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this event?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-glow px-8 py-4 bg-gradient-to-r from-red-500 to-rose-400 text-gray-900 font-semibold rounded-2xl shadow-[0_0_30px_rgba(239,68,68,0.4)] hover:shadow-[0_0_50px_rgba(239,68,0.6)] transition-all duration-300 transform hover:scale-105 glow-effect">
                        Delete Event
                    </button>
                </form>
            </div>
            {{-- @endif --}}
            {{-- @endauth --}}
        </div>
    </section>

    <footer class="bg-gradient-to-br from-gray-900 via-gray-800 to-black text-white py-16 px-8 relative overflow-hidden">
        <div class="absolute inset-0">
            <div class="particle" style="left: 10%; top: 20%; animation-delay: 0s;"></div>
            <div class="particle" style="left: 90%; top: 30%; animation-delay: 2s;"></div>
            <div class="particle" style="left: 30%; top: 80%; animation-delay: 4s;"></div>
            <div class="particle" style="left: 70%; top: 60%; animation-delay: 6s;"></div>
        </div>

        <div class="max-w-7xl mx-auto relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                <div class="md:col-span-2">
                    <img src="{{ asset('images/lg-portalis.png') }}" alt="Portalis Logo" class="h-16 mb-4">
                    <p class="text-gray-300 leading-relaxed mb-6">
                        Connecting students through amazing events. Create, discover, and participate in competitions, seminars, and workshops that shape your future.
                    </p>
                    <div class="flex space-x-4">
                        {{-- Replace # with actual links or actions --}}
                        <a href="mailto:info@portalis.com" class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center hover:scale-110 transition-transform duration-300 cursor-pointer">
                            <span class="text-xl">📧</span>
                        </a>
                        <a href="https://wa.me/yourphonenumber" class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center hover:scale-110 transition-transform duration-300 cursor-pointer">
                            <span class="text-xl">📱</span>
                        </a>
                        <a href="https://instagram.com/portalis" class="w-12 h-12 bg-pink-600 rounded-full flex items-center justify-center hover:scale-110 transition-transform duration-300 cursor-pointer">
                            <span class="text-xl">📷</span>
                        </a>
                    </div>
                </div>

                <div>
                    <h4 class="font-bold text-xl mb-6 text-gray-100">Platform</h4>
                    <div class="space-y-3">
                        <a href="{{ route('events.create') }}" class="block text-gray-300 hover:text-blue-400 transition-colors duration-300">Create Event</a>
                        <a href="{{ route('events.index') }}" class="block text-gray-300 hover:text-blue-400 transition-colors duration-300">Browse Events</a>
                        <a href="#contact" class="block text-gray-300 hover:text-blue-400 transition-colors duration-300">Support</a> {{-- Make sure #contact exists or link to a support page --}}
                    </div>
                </div>

                <div>
                    <h4 class="font-bold text-xl mb-6 text-gray-100">Company</h4>
                    <div class="space-y-3">
                        <a href="#about" class="block text-gray-300 hover:text-blue-400 transition-colors duration-300">About</a> {{-- Make sure #about exists or link to an about page --}}
                        <a href="#contact" class="block text-gray-300 hover:text-blue-400 transition-colors duration-300">Contact</a>
                        <a href="#" class="block text-gray-300 hover:text-blue-400 transition-colors duration-300">Terms</a> {{-- Link to a terms page --}}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center border-t border-gray-700 pt-8">
                <div>
                    <h4 class="font-bold text-xl mb-4 text-gray-100">Our Location</h4>
                    <div class="bg-gray-800 rounded-2xl p-4 hover:bg-gray-700 transition-colors duration-300">
                        {{-- Replace with your actual Google Maps embed code if you have one for Portalis HQ --}}
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3953.150790515149!2d110.36020181537766!3d-7.773906994392627!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a58f2b7087999%3A0x8a8c076778520486!2sSleman%2C%20Special%20Region%20of%20Yogyakarta!5e0!3m2!1sen!2sid!4v1678888888888!5m2!1sen!2sid"
                            width="100%"
                            height="200"
                            style="border:0; border-radius:12px;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                        <p class="text-gray-300 mt-3 text-center">📍 Based in Sleman, Yogyakarta, Indonesia</p>
                    </div>
                </div>

                <div class="text-center lg:text-right">
                    <div class="inline-block bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-6">
                        <div class="flex justify-start gap-8">
                            <img src="{{ asset('images/lg.png') }}" alt="Portalis Logo Small" class="h-16 w-auto"> {{-- Ensure this image exists --}}
                            <div>
                                <h5 class="font-bold text-xl mb-2">Join Our Community!</h5>
                                <p class="text-sm mb-4 opacity-90">Get notified about new events</p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <form action="{{ route('subscribe') }}" method="POST" class="flex gap-2">
                                @csrf
                                <input type="email" name="email" placeholder="Enter your email" required
                                    class="px-4 py-2 border text-gray-900 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    value="{{ old('email') }}">
                                <button type="submit" class="px-6 py-2 bg-white text-blue-600 rounded-lg font-semibold hover:bg-gray-100 transition-colors duration-300">
                                    Subscribe
                                </button>
                            </form>

                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-12 pt-8 border-t border-gray-700">
                <p class="text-gray-400">&copy; {{ date('Y') }} Portalis. All rights reserved. Made by Portalis Team </p>
            </div>
        </div>
    </footer>

    <script>
        // Scroll Progress Indicator
        window.addEventListener('scroll', () => {
            const scrolled = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
            const scrollIndicator = document.querySelector('.scroll-indicator');
            if (scrollIndicator) {
                scrollIndicator.style.width = scrolled + '%';
            }
        });

        // Navbar Scroll Effect (assuming .navbar exists in your main layout)
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (navbar) {
                if (window.scrollY > 100) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            }
        });

        // Reveal Animation on Scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
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

        // Smooth Scrolling for Navigation Links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const hrefAttribute = this.getAttribute('href');
                if (hrefAttribute && hrefAttribute !== '#') { // Ensure it's not just a placeholder '#'
                    const target = document.querySelector(hrefAttribute);
                    if (target) {
                        e.preventDefault();
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });

        // Dynamic Particles
        function createParticle() {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.top = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 3 + 's';

            document.querySelectorAll('.bg-particles').forEach(container => {
                if (container.children.length < 15) { // Limit number of particles
                    container.appendChild(particle.cloneNode());
                }
            });
        }
        // Create initial particles if containers exist
        if (document.querySelector('.bg-particles')) {
            for(let i=0; i<9; i++) { // Create some initial particles for the hero
                 createParticle();
            }
            // Add more particles periodically
            setInterval(createParticle, 2000);
        }


        // Form Enhancement (for subscribe input)
        const formInputs = document.querySelectorAll('input[type="email"], textarea, select'); // Be more specific
        formInputs.forEach(input => {
            input.addEventListener('focus', function() {
                // this.parentElement.style.transform = 'scale(1.02)'; // This might be too aggressive for inline form
                // this.parentElement.style.transition = 'transform 0.2s ease';
                this.style.boxShadow = '0 0 0 2px #3b82f6'; // Example focus style
            });

            input.addEventListener('blur', function() {
                // this.parentElement.style.transform = 'scale(1)';
                this.style.boxShadow = '';
            });
        });

        // Typing Effect for Hero Text (if you add a class .typing-effect to the hero title)
        const heroText = document.querySelector('.typing-effect'); // e.g. <h2 class="... typing-effect">
        if (heroText) {
            let isVisible = true;
            // This is a cursor blink, not a typing effect.
            // For a full typing effect, you'd need a more complex script.
            // heroText.style.borderRight = '2px solid #3b82f6'; // Initial cursor
            setInterval(() => {
                // heroText.style.borderRightColor = isVisible ? 'transparent' : '#3b82f6';
                // isVisible = !isVisible;
            }, 500);
        }

        // Interactive Cards (if you add a class .card-hover to some cards)
        document.querySelectorAll('.card-hover').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)'; // Adjusted effect
                this.style.boxShadow = '0 20px 40px -10px rgba(0, 0, 0, 0.2)'; // Adjusted shadow
                this.style.transition = 'transform 0.3s ease, box-shadow 0.3s ease';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
                this.style.boxShadow = '0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05)'; // Default shadow if any
            });
        });

        // Add loading animation (simple fade-in)
        window.addEventListener('load', () => {
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.5s ease-in-out';
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 50); // Short delay
        });
    </script>
</body>
</html>

