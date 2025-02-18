<?php
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Blog Website</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            width: 80%;
            max-width: 900px;
            margin: 40px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            font-size: 36px;
            color: #333;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 24px;
            color: #007bff;
            margin-top: 30px;
            margin-bottom: 15px;
        }

        .faq {
            margin-bottom: 20px;
        }

        .question {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }

        .answer {
            font-size: 16px;
            color: #555;
            margin-left: 20px;
        }

        .section-title {
            font-size: 22px;
            font-weight: bold;
            color: #444;
            border-bottom: 2px solid #007bff;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }

        /* Responsive Styles */
        @media screen and (max-width: 768px) {
            .container {
                width: 90%;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Frequently Asked Questions</h1>

    <div class="faq-section">
        <div class="section-title">General Questions</div>
        <div class="faq">
            <div class="question">What is this blog about?</div>
            <div class="answer">This blog focuses on [insert topic(s) here], providing insightful articles, guides, and tips to help our readers stay informed and inspired.</div>
        </div>
        <div class="faq">
            <div class="question">Who writes the articles on this blog?</div>
            <div class="answer">Our articles are written by [our team of experienced writers/community contributors/professionals in the field].</div>
        </div>
        <div class="faq">
            <div class="question">How often is the blog updated?</div>
            <div class="answer">We publish new content [daily/weekly/bi-weekly/monthly], so check back regularly for updates!</div>
        </div>
        <div class="faq">
            <div class="question">Can I subscribe to get updates?</div>
            <div class="answer">Yes! You can subscribe to our newsletter to receive the latest posts directly to your inbox.</div>
        </div>
    </div>

    <div class="faq-section">
        <div class="section-title">Account and Membership</div>
        <div class="faq">
            <div class="question">Do I need to create an account to read the blog?</div>
            <div class="answer">No, you can read our blog without an account. However, creating an account allows you to [comment, bookmark articles, or receive personalized recommendations].</div>
        </div>
        <div class="faq">
            <div class="question">How do I create an account?</div>
            <div class="answer">Click on the "Sign Up" button in the top-right corner and fill out the registration form.</div>
        </div>
        <div class="faq">
            <div class="question">I forgot my password. How can I reset it?</div>
            <div class="answer">Click on the "Forgot Password?" link on the login page and follow the instructions to reset your password.</div>
        </div>
        <div class="faq">
            <div class="question">Can I delete my account?</div>
            <div class="answer">Yes, you can delete your account by visiting the Settings page and selecting "Delete Account."</div>
        </div>
    </div>

    <div class="faq-section">
        <div class="section-title">Posting and Contributing</div>
        <div class="faq">
            <div class="question">Can I contribute articles to this blog?</div>
            <div class="answer">Yes, we welcome guest posts! Visit the Contribute page to learn about our submission guidelines.</div>
        </div>
        <div class="faq">
            <div class="question">How do I submit an article?</div>
            <div class="answer">You can submit your article by logging into your account, navigating to the Write an Article section, and following the prompts.</div>
        </div>
        <div class="faq">
            <div class="question">Will my article be published immediately?</div>
            <div class="answer">No, all submissions go through a review process to ensure quality and relevance before being published.</div>
        </div>
        <div class="faq">
            <div class="question">Do I get credit for my published articles?</div>
            <div class="answer">Yes, your name and profile will be displayed alongside your article.</div>
        </div>
    </div>

    <div class="faq-section">
        <div class="section-title">Technical and Troubleshooting</div>
        <div class="faq">
            <div class="question">The website isn't loading properly. What should I do?</div>
            <div class="answer">Try clearing your browser cache or reloading the page. If the problem persists, contact us via the Support page.</div>
        </div>
        <div class="faq">
            <div class="question">Why can’t I comment on articles?</div>
            <div class="answer">You may need to log in or verify your email address to comment on articles. If you're still having issues, please contact support.</div>
        </div>
        <div class="faq">
            <div class="question">I’m experiencing issues with my account. How can I get help?</div>
            <div class="answer">Visit the Help Center or reach out to us directly through the Contact Us form.</div>
        </div>
    </div>

    <div class="faq-section">
        <div class="section-title">Privacy and Security</div>
        <div class="faq">
            <div class="question">Is my personal information safe on this website?</div>
            <div class="answer">We take your privacy seriously and use industry-standard measures to protect your data. Please review our Privacy Policy for more details.</div>
        </div>
        <div class="faq">
            <div class="question">Do you use cookies?</div>
            <div class="answer">Yes, we use cookies to improve your experience on our site. For more information, read our Cookie Policy.</div>
        </div>
        <div class="faq">
            <div class="question">Can I opt out of receiving emails?</div>
            <div class="answer">Yes, you can manage your email preferences in the Settings section of your account.</div>
        </div>
    </div>

    <div class="faq-section">
        <div class="section-title">Monetization</div>
        <div class="faq">
            <div class="question">Is the blog free to access?</div>
            <div class="answer">Yes, all of our articles are free to read. However, we may offer premium content or features in the future.</div>
        </div>
        <div class="faq">
            <div class="question">Do you accept sponsored posts or advertisements?</div>
            <div class="answer">Yes, we occasionally publish sponsored content. For inquiries, visit the Advertise with Us page.</div>
        </div>
        <div class="faq">
            <div class="question">How can I support this blog?</div>
            <div class="answer">You can support us by sharing our articles, subscribing to our newsletter, or donating through the Support Us page.</div>
        </div>
    </div>
</div>

</body>
</html>

