<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICSA OVS - Contact</title>
    <link rel="shortcut icon" href="{{ asset('images/icsalogo.png') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/contact.css') }}">

    <!-- sycopate font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&family=Syncopate:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('index') }}">
                <img src="{{ asset('images/icsalogo.png') }}" alt="IC Logo">
                ICSA OVS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('index') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="{{ route('about') }}">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('contact') }}">Contact</a>
                    </li>
                </ul>
                <a href="{{ route('voterLogin') }}" class="btn btn-login">Login</a>
            </div>
        </div>
    </nav>

    <!-- Page Title Section -->
    <section class="page-title-section">
        <div class="container">
            <h1 class="page-title">CONTACT US</h1>
        </div>
    </section>

    <!-- Content Section -->
    <section class="content-section">
        <div class="container">
            <!-- Contact Info Boxes -->
            <div class="contact-info-section">
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="contact-box card-hover">
                            <div class="contact-icon">
                                <i class="bi bi-envelope-fill"></i>
                            </div>
                            <h4>Email</h4>
                            <p>icsa@dnsc.edu.ph</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="contact-box card-hover">
                            <div class="contact-icon">
                                <i class="bi bi-telephone-fill"></i>
                            </div>
                            <h4>Phone</h4>
                            <p>+63 123 456 7890</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="contact-box card-hover">
                            <div class="contact-icon">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <h4>Office</h4>
                            <p>ICSA Office, IC Building<br>DNSC Campus</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form-card">
                <h3>Send us a Message</h3>
                <form>
                    <div class="mb-3">
                        <label for="fullName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="fullName" placeholder="Enter your full name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" placeholder="Enter your email address" required>
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" placeholder="Enter message subject" required>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" rows="6" placeholder="Enter your message" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-send ">Send Message</button>
                </form>
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
                    <p>Institute of Computing Student <br>Association Online Voting System</p>
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
