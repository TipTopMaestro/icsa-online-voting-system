<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICSA OVS - About</title>
    <link rel="shortcut icon" href="{{ asset('images/icsalogo.png') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/about.css') }}">

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
                        <a class="nav-link active" href="{{ route('about') }}">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('contact') }}">Contact</a>
                    </li>
                </ul>
                <a href="{{ route('login') }}" class="btn btn-login">Login</a>
            </div>
        </div>
    </nav>

    <!-- Page Title Section -->
    <section class="page-title-section">
        <div class="container">
            <h1 class="page-title">ABOUT THE SYSTEM</h1>
        </div>
    </section>

    <!-- Content Section -->
    <section class="content-section">
        <div class="container ">
            <!-- System Purpose -->
            <div class="content-card card-hover">
                <h3>System Purpose</h3>
                <p>
                    The ICSA Online Voting System is a comprehensive web-based platform designed to facilitate democratic and transparent elections for the Institute of Computing Student Association (ICSA) at Davao del Norte State College (DNSC).
                </p>
                <p>
                    This innovative system replaces traditional paper-based voting methods and generic online forms with a dedicated, secure, and efficient digital solution that ensures every student's voice is heard while maintaining the highest standards of electoral integrity.
                </p>
                <p>
                    Built specifically for the unique needs of student elections, our platform provides a complete election management solution from candidate registration to final results announcement.
                </p>
            </div>

            <!-- System Objectives -->
            <div class="content-card card-hover">
                <h3>System Objectives</h3>
                
                <div class="objective-item">
                    <div class="objective-number">1</div>
                    <div class="objective-content">
                        <h4>Enhance Electoral Security</h4>
                        <p>Implement robust encryption and authentication mechanisms to protect voter identity and ensure ballot integrity, preventing fraud and unauthorized access to the voting system.</p>
                    </div>
                </div>

                <div class="objective-item">
                    <div class="objective-number">2</div>
                    <div class="objective-content">
                        <h4>Increase Student Participation</h4>
                        <p>Remove physical barriers to voting by providing a convenient online platform accessible from anywhere, encouraging higher turnout and more representative election results.</p>
                    </div>
                </div>

                <div class="objective-item">
                    <div class="objective-number">3</div>
                    <div class="objective-content">
                        <h4>Ensure Transparency</h4>
                        <p>Provide real-time result tracking and transparent counting processes that build trust in the electoral system and allow stakeholders to verify election outcomes.</p>
                    </div>
                </div>

                <div class="objective-item">
                    <div class="objective-number">4</div>
                    <div class="objective-content">
                        <h4>Streamline Administration</h4>
                        <p>Reduce the administrative burden of traditional voting methods through automated processes, making election management more efficient and cost-effective for the organization.</p>
                    </div>
                </div>
            </div>

            <!-- Benefits -->
            <div class="content-card card-hover">
                <h3>Benefits</h3>
                <div class="row">
                    <div class="col-md-6 benefits-column">
                        <h4>For Students</h4>
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div class="benefit-text">
                                <strong>Secure Voting:</strong> Your vote is encrypted and anonymized, ensuring complete privacy and protection from tampering.
                            </div>
                        </div>
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div class="benefit-text">
                                <strong>Convenient Access:</strong> Vote from anywhere using your computer or mobile device during the election period.
                            </div>
                        </div>
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div class="benefit-text">
                                <strong>Informed Decisions:</strong> Access detailed candidate profiles and platforms before casting your vote.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 benefits-column">
                        <h4>For Administrators</h4>
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div class="benefit-text">
                                <strong>Efficient Management:</strong> Automated vote counting and result tabulation save time and reduce errors.
                            </div>
                        </div>
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div class="benefit-text">
                                <strong>Real-time Monitoring:</strong> Track voter turnout and election progress in real-time through comprehensive dashboards.
                            </div>
                        </div>
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div class="benefit-text">
                                <strong>Audit Trail:</strong> Complete logging and audit capabilities ensure accountability and compliance with election regulations.
                            </div>
                        </div>
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
