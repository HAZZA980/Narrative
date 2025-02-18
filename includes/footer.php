<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="footer.php">
    <style>
        /* Footer  */
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Arial', sans-serif;
        }

        .footer-container {
            background-color: #1a1a1a;
            color: white;
            padding: 2rem 0;
            margin-top: 2em;
            height: 160px;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* Contact Links */
        .contact-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .contact-links .element {
            text-align: center;
        }

        .contact-links .element img {
            width: 40px;
            height: 40px;
            transition: transform 0.3s ease;
        }

        .contact-links .element img:hover {
            transform: scale(1.1);
        }

        .contact-links .element h4, .contact-links .element h5 {
            margin-top: 0.5rem;
            font-size: 1.1rem;
            color: white;
            font-weight: 400;
        }

        .contact-links .element h4:hover, .contact-links .element h5:hover {
            color: #f4a261;
            cursor: pointer;
        }

        /* Additional Links */
        .additional {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .additional a {
            color: white;
            font-size: 1rem;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .additional a:hover {
            color: #f4a261;
        }

        /* Legal Info */
        .legal {
            display: flex;
            justify-content: center;
            gap: 2rem;
            width: 100%;
            font-size: 0.9rem;
            color: #b0b0b0;
        }

        .legal .a {
            font-weight: 400;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .contact-links {
                flex-direction: column;
                align-items: center;
            }

            .additional {
                flex-direction: column;
                align-items: center;
            }

            .legal {
                flex-direction: column;
                align-items: center;
            }
        }

    </style>
</head>
<body>
<footer class="footer-container">
    <div class="footer-content">
        <!-- Contact Links -->
        <div class="contact-links">
        </div>

        <!-- Additional Links -->
        <div class="additional">
            <a href="<?php echo BASE_URL?>includes/aboutMe.php">About Me</a>
            <a href="<?php echo BASE_URL?>includes/contactMe.php">Contact Me</a>
            <a href="<?php echo BASE_URL?>layouts/sitemap/siteMap.php">Site Map</a>
        </div>

        <!-- Legal Info -->
        <div class="legal">
            <h4 class="a copyright">© 2024 Harry Pape</h4>
            <h4 class="a rights">All Rights Reserved</h4>
        </div>
    </div>
</footer>
</body>
</html>
