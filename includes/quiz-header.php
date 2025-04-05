<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* Ensure body has no margins or padding */
    body {
        margin: 0;
        padding: 0;
        width: 100%;
    }

    .masthead-container {
        font-family: ReithSans, Helvetica, Arial, freesans, sans-serif;
        font-weight: 400;
        font-size: 1rem;
        line-height: 1.375;
        background-color: green;
        color: white;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .article-masthead-container {
        font-family: ReithSans, Helvetica, Arial, freesans, sans-serif;
        font-weight: 400;
        font-size: 1rem;
        line-height: 1.375;
        background-color: darkred;
        color: white;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .title-container {
        width: 73%;
        height: 3rem; /* Ensure the height is consistent */
        margin: 0.8rem 0 0 0;
    }

    .title-container #title {
        font-weight: 700;
        font-size: 2.5rem;
        color: white;
    }

    .title-container a {
        color: white; /* White color for the text */
        text-decoration: none; /* Remove underline */
        font-weight: 700; /* Optional: make the title bold */
    }

    /* Hover state for the title link */
    .title-container a:hover {
        color: white; /* Ensure the text remains white on hover */
        text-decoration: none; /* Ensure there is no underline on hover */
    }


    .divider {
        position: relative;
        left: 0;
        width: 100%;
        background-color: white;
        height: 1px;
    }

    .primary-navigation {
        width: 73%;
        height: 2.2rem;
        position: relative;
    }

    .primary-navigation ul {
        display: flex;
        flex-direction: row;
        align-items: flex-start;
        list-style-type: none;
    }

    .primary-navigation .nav-bar {
        margin-top: 0.5rem;
        padding: 0 0.5em;
    }

    .nav-links a {
        color: white;
        text-decoration: none;
        text-underline: none;


    }

    .nav-links a:hover {
        color: white;
        text-decoration: none;
        text-underline: none;
    }
    .nav-bar {
        border-bottom: green 5px solid;
    }

    .nav-links .nav-bar:hover {
        border-bottom: white 6px solid;
    }

    .nav-links {
        width: 50%;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
    }






    /*Lifestyle page */
    .secondary-masthead {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .sub-navigation {
        width: 73%;
        height: 2.2rem;
        position: relative;
    }

    .sub-navigation ul {
        display: flex;
        flex-direction: row;
        align-items: flex-start;
        list-style-type: none;
    }

    .sub-navigation .sub-nav {
        border-right: black solid 1px;
        margin-top: 0.5rem;
        padding: 0 0.5em;
    }

    .sub-nav-links a {
        color: black;
        text-decoration: none;
        text-underline: none;
    }

    .sub-nav-links a:hover {
        color: black;
        text-decoration: none;
        text-underline: none;
    }
    .sub-nav {
        border-bottom: white 3px solid;
        padding: 0 1rem;
    }

    .sub-nav-links .sub-nav:hover {
        border-bottom: #003366 4px solid;
    }

</style>
</head>
<body>

<div class="divider"></div>
<div class="masthead-container">
    <div class="title-container">
        <h2 id="title"><a href="<?php echo BASE_URL?>">QUIZZES</a></h2>
    </div>
    <div class="divider"></div>
    <nav class="primary-navigation">
        <div class="navigation">
            <ul class="nav-links">
                <a href="<?php echo BASE_URL?>quiz/home.php"><li class="nav-bar nav-home">Home</li></a>
                <a href="<?php echo BASE_URL?>quiz/profile.php"><li class="nav-bar nav-profile">Profile</li></a>
                <a href="<?php echo BASE_URL?>quiz/categories.php"><li class="nav-bar nav-categories">Categories</li></a>
<!--                <a href="--><?php //echo BASE_URL?><!--"><li class="nav-bar nav-badges">Badges</li></a>-->
                <a href="<?php echo BASE_URL?>quiz/createQuiz.php"><li class="nav-bar nav-create">Create</li></a>
                <a href="<?php echo BASE_URL?>quiz/quiz-search.php"><li class="nav-bar nav-search">Search</li></a>
            </ul>
        </div>
    </nav>
</div>
<!---->
<!--<section class="secondary-masthead">-->
<!--    <nav class="sub-navigation">-->
<!--        <div class="lifestyle-nav">-->
<!--            <ul class="sub-nav-links">-->
<!--                <a href="e-stockholm.php"><li class="sub-nav nav-stockholm">Stockholm</li></a>-->
<!--            </ul>-->
<!--        </div>-->
<!--    </nav>-->
<!--</section>-->



</body>
</html>