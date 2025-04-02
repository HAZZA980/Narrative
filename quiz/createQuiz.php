<?php
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . 'includes/quiz-header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quiz | Narrative Quizzes</title>
    <link rel="stylesheet" href="<?php echo BASE_URL ?>public/css/styles-create-quiz.css">
    <style>
        /* Import a modern font */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap');

        /* Ensure full-page height */
        .quiz-container {
            display: flex;
            flex-direction: row;
            min-height: 100vh;
        }

        /* General Page Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        /* Fancy H1 Styling */
        h1 {
            text-align: center;
            color: #007BFF;
            font-family: 'Poppins', sans-serif; /* Modern font */
            font-size: 2rem;
            margin-bottom: 30px;
        }

        /* Form Styling */
        #quizForm {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Labels */
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
            margin-top: 8px;
        }

        /* Input Fields */
        /* Form container styles */
        .form-row {
            display: flex;
            gap: 20px;
        }

        /* Style for input fields */
        .form-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        /* Style for text areas */
        .form-textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            resize: vertical;
        }

        /* Style for select dropdowns */
        .form-select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            background-color: white;
            cursor: pointer;
        }

        /* Style for buttons */
        .form-button {
            background-color: #007BFF;
            color: white;
            font-weight: 600;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 16px;
        }

        .form-button:hover {
            background-color: #0056b3;
        }


        /* Styled Dropdowns */
        select {
            appearance: none;
            background-color: #fff;
            border: 1px solid #ccc;
            padding: 12px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        /* Hover & Focus Effects */
        select:hover,
        select:focus {
            border-color: #007BFF;
            outline: none;
        }

        /* Textarea Adjustments */
        textarea {
            min-height: 120px;
            resize: vertical;
        }

        /* Buttons */
        button {
            margin-top: 8px;
            background-color: #007BFF;
            color: white;
            font-weight: 600;
            border: none;
            padding: 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 16px;
        }



        /* Question Section */
        #questionsContainer {
            margin-top: 25px;
        }

        /* Individual Question Block */
        .question {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
            margin-bottom: 20px; /* More space between questions */
        }

        /* Remove Question Button */
        .removeQuestion {
            background-color: #dc3545;
            color: white;
            margin-top: 10px;
        }

        .removeQuestion:hover {
            background-color: #c82333;
        }

        /* More Margin Between Sections */
        .section {
            margin-bottom: 30px; /* Extra spacing */
        }

        /* Responsive Design */
        @media screen and (max-width: 600px) {
            #quizForm {
                padding: 20px;
            }
        }


        /* Login Section */
        .login-section {
            position: relative;
            left: 36%;
            top: 30%;
            height: 30vh;            /* Full viewport height */
            text-align: center;
        }

        .login-button {
            background-color: #007BFF;
            color: white;
            padding: 12px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
        }

        .login-button:hover {
            background-color: #0056b3;
        }

    </style>
</head>
<body>

<div class="quiz-container">
    <?php
    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        // User is not logged in, show login prompt and button
        echo '
            <div class="login-section">
                <h1>Log in to create a quiz!</h1>
                <a href="' . BASE_URL . 'user_auth.php" class="login-button">Log in</a>
            </div>
        ';
    } else {
        // User is logged in, show quiz creation page
        include BASE_PATH . 'quiz/views/sidebar-create.php';
        ?>
        <div class="content">
            <?php
            $section = isset($_GET['create']) ? $_GET['create'] : 'BasicInfo';

            switch ($section) {
                case 'Data':
                    include 'quiz-sections/data.php';
                    break;
                case 'Advanced':
                    include 'quiz-sections/advanced.php';
                    break;
                case 'Contribute':
                    include 'quiz-sections/contribute.php';
                    break;
                case 'BasicInfo':
                default:
                    include 'quiz-sections/basic-info.php';
                    break;
            }
            ?>
        </div>
        <?php
    }
    ?>
</div>


</body>
</html>
