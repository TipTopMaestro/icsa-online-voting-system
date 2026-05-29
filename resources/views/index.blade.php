
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICSA OVS - Home</title>
    <link rel="shortcut icon" href="{{ asset('images/icsalogo.png') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">

    <!-- sycopate font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&family=Syncopate:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container-fluid px-4 px-lg-5">
            <!-- Left Section (Brand) -->
            <div class="d-flex flex-grow-1 align-items-center">
                <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('index') }}">
                    <img src="{{ asset('images/icsalogo.png') }}" alt="IC Logo">
                    <span class="syncopate-bold d-none d-sm-inline" style="text-shadow: 0 2px 8px rgba(0,0,0,0.5);">ICSA OVS</span>
                </a>
            </div>

            <!-- Toggler for Mobile -->
            <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Center Section (Navigation Links) -->
            <div class="collapse navbar-collapse flex-grow-0" id="navbarNav">
                <ul class="navbar-nav mx-auto text-center">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('index') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('about') }}">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('contact') }}">Contact</a>
                    </li>
                    <!-- Login for Mobile -->
                    <li class="nav-item d-lg-none mt-3">
                        <a href="{{ route('login') }}" class="btn btn-login w-100">Login</a>
                    </li>
                </ul>
            </div>

            <!-- Right Section (Login Button) -->
            <div class="d-none d-lg-flex flex-grow-1 justify-content-end align-items-center">
                <a href="{{ route('login') }}" class="btn btn-login px-4">Login</a>   
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section hero-img-bg">
        <div class="container mt-3">
            <div class="hero-logos">
                <div class="hero-logo">
                    <img src="{{ asset('images/icsalogo.png') }}" alt="ICSA Logo">
                </div>
                <div class="hero-logo">
                    <img src="{{ asset('images/iclogo.jpg') }}" alt="IC Logo">
                </div>
            </div>
            <h1 class="hero-title">
                <span>SECURE VOTING</span><br>TRUSTED RESULTS
            </h1>
            <p class="hero-subtitle">
                The ICSA's trusted online voting system. Secure, reliable, and built to ensure that every IC student's voice is heard and counted.
            </p>
            <a href="{{ route('register') }}"><button class="btn btn-register">Register Now</button></a>
        </div>
    </section>

    <!-- Why Use ICSA OVS Section -->
    <section class="why-section">
        <div class="container">
            <h2 class="section-title">WHY USE ICSA OVS?</h2>
            <p class="section-subtitle">A modern, secure, and transparent voting system designed specifically for the Institute of Computing Student Association elections.</p>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-lock-fill"></i>
                        </div>
                        <h3 class="feature-title">Secure & Private</h3>
                        <p class="feature-text">Advanced security measures ensure your vote remains confidential and tamper-proof throughout the election process.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <h3 class="feature-title">Transparent Results</h3>
                        <p class="feature-text">Real-time vote counting with transparent reporting ensures integrity and builds trust in the election outcome.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-clock-fill"></i>
                        </div>
                        <h3 class="feature-title">Easy to Use</h3>
                        <p class="feature-text">Intuitive interface designed for students, making it simple to view candidates and cast your vote in minutes.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-section">
        <div class="container">
            <h2 class="section-title">HOW IT WORKS</h2>
            <p class="section-subtitle">Simple steps to participate in the election process</p>
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="step-card">
                        <div class="step-number">01</div>
                        <h3 class="step-title">Register</h3>
                        <p class="step-text">Create your account with your student credentials</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="step-card">
                        <div class="step-number">02</div>
                        <h3 class="step-title">Browse</h3>
                        <p class="step-text">View candidate profiles and their platforms</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="step-card">
                        <div class="step-number">03</div>
                        <h3 class="step-title">Vote</h3>
                        <p class="step-text">Cast your vote securely for your chosen candidates</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="step-card">
                        <div class="step-number">04</div>
                        <h3 class="step-title">Result</h3>
                        <p class="step-text">Cast your vote securely for your chosen candidates</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="footer-logo">
                        <img src="{{ asset('images/icsalogo.png') }}" alt="IC Logo">
                        <h5 class="mb-0 syncopate-bold">ICSA OVS</h5>
                    </div>
                    <p>Institute of Computing Student <br> Association Online Voting System</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Links</h5>
                    <a href="{{ route('index') }}">Home</a>
                    <a href="{{ route('about') }}">About</a>
                    <a href="{{ route('contact') }}">Contact</a>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Contact Us</h5>
                    <p>Davao del Norte State College<br>
                    Institute of Computing<br>
                    icsa@dnsc.edu.ph</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 ICSA Online Voting System — Davao del Norte State College</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>
