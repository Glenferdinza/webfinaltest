<!-- resources/views/profile.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Portalis</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .font-inter { font-family: 'Inter', sans-serif; }
        .font-poppins { font-family: 'Poppins', sans-serif; }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-100px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes glow {
            0%, 100% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.3); }
            50% { box-shadow: 0 0 40px rgba(59, 130, 246, 0.6); }
        }

        .hero-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 30%, #334155 70%, #475569 100%);
            position: relative;
            min-height: 100vh;
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
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
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
            animation: float 8s ease-in-out infinite;
        }

        .shape-1 {
            top: 10%;
            left: 5%;
            width: 120px;
            height: 120px;
            background: linear-gradient(45deg, #3b82f6, #06b6d4);
            border-radius: 50%;
        }

        .shape-2 {
            top: 70%;
            right: 10%;
            width: 100px;
            height: 100px;
            background: linear-gradient(45deg, #f59e0b, #ef4444);
            transform: rotate(45deg);
            animation-delay: 2s;
        }

        .shape-3 {
            bottom: 10%;
            left: 15%;
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #10b981, #059669);
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            animation-delay: 4s;
        }

        .input-focus:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(118, 75, 162, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3);
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(245, 158, 11, 0.3);
        }

        .profile-avatar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            animation: glow 3s ease-in-out infinite alternate;
        }

        .logo-img {
            height: 40px;
            width: auto;
            filter: brightness(0) invert(1);
        }

        .animate-fadeInUp { animation: fadeInUp 0.8s ease-out; }
        .animate-slideInLeft { animation: slideInLeft 0.8s ease-out; }
        
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #1f2937; }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, #3b82f6, #06b6d4);
            border-radius: 4px;
        }
    </style>
</head>
<body class="hero-bg font-inter">
    <!-- Floating Shapes Background -->
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <div class="min-h-screen relative z-10">
        <!-- Header -->
        <header class="glass-effect shadow-2xl">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <img src="{{ asset('images/lg-portalis.png') }}" alt="Portal Isweb Logo" class="logo-img">
                </div>
                <nav class="flex items-center space-x-6">
                    <a href="{{ route('dashboard') }}" class="text-white hover:text-blue-300 transition-colors duration-300 font-medium">Dashboard</a>
                    <a href="{{ route('events.index') }}" class="text-white hover:text-blue-300 transition-colors duration-300 font-medium">Events</a>
                    <div class="relative">
                        <div class="profile-avatar w-10 h-10 rounded-full flex items-center justify-center text-white font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    </div>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <!-- Profile Header -->
            <div class="glass-effect rounded-3xl p-8 mb-8 animate-fadeInUp">
                <div class="flex items-center space-x-6">
                    <div class="relative">
                        @if(auth()->user()->profile_image)
                            <img src="{{ auth()->user()->profile_image }}" 
                                 alt="Profile Picture" 
                                 class="w-24 h-24 rounded-full object-cover profile-avatar">
                        @else
                            <div class="profile-avatar w-24 h-24 rounded-full flex items-center justify-center text-white text-3xl font-bold">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <h2 class="text-3xl font-bold text-white font-poppins">{{ auth()->user()->name }}</h2>
                        <p class="text-blue-200 text-lg">{{ auth()->user()->email }}</p>
                        @if(auth()->user()->institution)
                            <p class="text-gray-300">{{ auth()->user()->institution }}</p>
                        @endif
                        @if(auth()->user()->student_id)
                            <p class="text-gray-400 text-sm">Student ID: {{ auth()->user()->student_id }}</p>
                        @endif
                        <p class="text-gray-300">Member since {{ auth()->user()->created_at->format('F Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Profile Form -->
            <div class="glass-effect rounded-3xl p-8 animate-slideInLeft">
                <h3 class="text-2xl font-bold text-white font-poppins mb-6">Edit Profile</h3>
                
                @if(session('success'))
                    <div class="bg-green-500/20 border border-green-500/30 text-green-100 px-4 py-3 rounded-lg mb-6">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-red-500/20 border border-red-500/30 text-red-100 px-4 py-3 rounded-lg mb-6">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-200 mb-2">Full Name</label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', auth()->user()->name) }}"
                                   class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/20 text-white placeholder-gray-400 input-focus transition-all duration-300"
                                   placeholder="Enter your full name"
                                   required>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-200 mb-2">Email Address</label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', auth()->user()->email) }}"
                                   class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/20 text-white placeholder-gray-400 input-focus transition-all duration-300"
                                   placeholder="Enter your email address"
                                   required>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-200 mb-2">Phone Number</label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone', auth()->user()->phone) }}"
                                   class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/20 text-white placeholder-gray-400 input-focus transition-all duration-300"
                                   placeholder="Enter your phone number">
                        </div>

                        <!-- Institution -->
                        <div>
                            <label for="institution" class="block text-sm font-medium text-gray-200 mb-2">Institution</label>
                            <input type="text" 
                                   id="institution" 
                                   name="institution" 
                                   value="{{ old('institution', auth()->user()->institution) }}"
                                   class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/20 text-white placeholder-gray-400 input-focus transition-all duration-300"
                                   placeholder="Enter your institution/university">
                        </div>

                        <!-- Student ID -->
                        <div>
                            <label for="student_id" class="block text-sm font-medium text-gray-200 mb-2">Student ID</label>
                            <input type="text" 
                                   id="student_id" 
                                   name="student_id" 
                                   value="{{ old('student_id', auth()->user()->student_id) }}"
                                   class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/20 text-white placeholder-gray-400 input-focus transition-all duration-300"
                                   placeholder="Enter your student ID">
                        </div>

                        <!-- Profile Image URL -->
                        <div>
                            <label for="profile_image" class="block text-sm font-medium text-gray-200 mb-2">Profile Image URL</label>
                            <input type="url" 
                                   id="profile_image" 
                                   name="profile_image" 
                                   value="{{ old('profile_image', auth()->user()->profile_image) }}"
                                   class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/20 text-white placeholder-gray-400 input-focus transition-all duration-300"
                                   placeholder="Enter profile image URL">
                        </div>
                    </div>

                    <!-- Bio -->
                    <div>
                        <label for="bio" class="block text-sm font-medium text-gray-200 mb-2">Bio</label>
                        <textarea id="bio" 
                                  name="bio" 
                                  rows="4"
                                  class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/20 text-white placeholder-gray-400 input-focus transition-all duration-300"
                                  placeholder="Tell us about yourself...">{{ old('bio', auth()->user()->bio) }}</textarea>
                    </div>

                    <!-- Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-6">
                        <button type="submit" 
                                class="btn-gradient text-white font-semibold py-3 px-8 rounded-lg transition-all duration-300 flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Update Profile</span>
                        </button>


                        <button type="button" 
                                onclick="toggleForms()"
                                class="btn-warning text-white font-semibold py-3 px-8 rounded-lg transition-all duration-300 flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <span>Change Password</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Reset Password Form (hidden by default) -->
            <div id="reset-password-form" class="glass-effect rounded-3xl p-8 animate-slideInLeft" style="display: none;">
                <h3 class="text-2xl font-bold text-white font-poppins mb-6">Reset Password</h3>

                @if ($errors->password->any())
                    <div class="text-red-500">
                        <ul>
                            @foreach ($errors->password->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif


                <form action="{{ route('profile.password') }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-200 mb-2">Current Password</label>
                        <div class="relative">
                            <input type="password" 
                                id="current_password" 
                                name="current_password" 
                                required
                                class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/20 text-white placeholder-gray-400 input-focus transition-all duration-300"
                                placeholder="Enter your current password">
                            <button type="button" onclick="togglePassword('current_password')" 
                                    class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white transition-all duration-300 focus:outline-none">
                                    <i class="fas fa-eye" id="eye-password"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-200 mb-2">New Password</label>
                        <div class="relative">
                            <input type="password" 
                                id="new_password" 
                                name="new_password" 
                                required
                                class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/20 text-white placeholder-gray-400 input-focus transition-all duration-300"
                                placeholder="Enter your new password">
                            <button type="button" onclick="togglePassword('new_password')" 
                                    class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white transition-all duration-300 focus:outline-none">
                                    <i class="fas fa-eye" id="eye-password"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="new_password_confirmation" class="block text-sm font-medium text-gray-200 mb-2">Confirm New Password</label>
                        <div class="relative">
                            <input type="password" 
                                id="new_password_confirmation" 
                                name="new_password_confirmation" 
                                required
                                class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/20 text-white placeholder-gray-400 input-focus transition-all duration-300"
                                placeholder="Confirm your new password">
                            <button type="button" onclick="togglePassword('new_password_confirmation')" 
                                    class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white transition-all duration-300 focus:outline-none">
                                    <i class="fas fa-eye" id="eye-password"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Link Forgot Password -->
                    <div class="text-right">
                        <a href="{{ route('password.forgot.logged') }}" class="text-sm text-white hover:underline">
                            Forgot Password?
                        </a>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 pt-6">
                        <button type="submit" 
                                class="btn-gradient text-white font-semibold py-3 px-8 rounded-lg transition-all duration-300 flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Update Password</span>
                        </button>

                        <button type="button" 
                                onclick="toggleForms()" 
                                class="btn-warning text-white font-semibold py-3 px-8 rounded-lg transition-all duration-300 flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span>Cancel</span>
                        </button>
                    </div>
                </form>
            </div>

        </main>
    </div>

    <script>

        function togglePassword(fieldId) {
            const input = document.getElementById(fieldId);
            const eyeIcon = document.getElementById(`eye-${fieldId}`);

            if (input.type === "password") {
                input.type = "text";
                eyeIcon.className = "fas fa-eye-slash"; // Ubah ke mata tercoret
            } else {
                input.type = "password";
                eyeIcon.className = "fas fa-eye"; // Ubah ke mata terbuka
            }
        }

        function toggleForms() {
            const editProfileForm = document.querySelector('form[action="{{ route('profile.update') }}"]').parentElement;
            const resetPasswordForm = document.getElementById('reset-password-form');

            if (resetPasswordForm.style.display === 'none') {
                // Show reset password, hide edit profile
                resetPasswordForm.style.display = 'block';
                editProfileForm.style.display = 'none';
            } else {
                // Show edit profile, hide reset password
                resetPasswordForm.style.display = 'none';
                editProfileForm.style.display = 'block';
            }
        }


        // Show success message if profile updated
        @if(session('success'))
            Swal.fire({
                title: 'Berhasil!',
                text: '{{ session("success") }}',
                icon: 'success',
                timer: 3000,
                showConfirmButton: false,
                background: 'rgba(15, 23, 42, 0.95)',
                color: '#ffffff'
            });
        @endif
    </script>
</body>
</html>