<?php
session_start();
                            //Category Title goes in the Score variables
//Film & TV
$scoreFilms = isset($_SESSION['scores']['films']) ? $_SESSION['scores']['films'] : null;
$scoreDW = isset($_SESSION['scores']['Doctor_Who_Episodes']) ? $_SESSION['scores']['Doctor_Who_Episodes'] : null;
$scoreYearReleased = isset($_SESSION['scores']['filmsYear']) ? $_SESSION['scores']['filmsYear'] : null;
$scoreCaryGrantFilmography = isset($_SESSION['scores']['caryGrantFilms']) ? $_SESSION['scores']['caryGrantFilms'] : null;
$score40s = isset($_SESSION['scores']['40s_Films']) ? $_SESSION['scores']['40s_Films'] : null;

//History
$scoreHistory = isset($_SESSION['scores']['history']) ? $_SESSION['scores']['history'] : null;
$scoreFamousWars = isset($_SESSION['scores']['Famous_Wars']) ? $_SESSION['scores']['Famous_Wars'] : null;
$scoreUSPresidents = isset($_SESSION['scores']['US_Presidents']) ? $_SESSION['scores']['US_Presidents'] : null;
$scoreGreeks = isset($_SESSION['scores']['greek_mythology']) ? $_SESSION['scores']['greek_mythology'] : null;
$scoreYearsToRemember = isset($_SESSION['scores']['years_to_remember']) ? $_SESSION['scores']['years_to_remember'] : null;

$scoreEmpires = isset($_SESSION['scores']['empires']) ? $_SESSION['scores']['empires'] : null;

//Literature
$scoreLiterature = isset($_SESSION['scores']['literature']) ? $_SESSION['scores']['literature'] : null;
$scoreShakespearePlays = isset($_SESSION['scores']['Shakespeare_Plays']) ? $_SESSION['scores']['Shakespeare_Plays'] : null;
$scoreShakespearePlaysQuiz = isset($_SESSION['scores']['ShakespearePlayQuiz']) ? $_SESSION['scores']['ShakespearePlayQuiz'] : null;
$scoreScience = isset($_SESSION['scores']['science']) ? $_SESSION['scores']['science'] : null;

$scoreGeneralKnowledge = isset($_SESSION['scores']['generalKnowledge']) ? $_SESSION['scores']['generalKnowledge'] : null;

//Geography
$scoreGeography = isset($_SESSION['scores']['geography']) ? $_SESSION['scores']['geography'] : null;
$scoreParis = isset($_SESSION['scores']['paris']) ? $_SESSION['scores']['paris'] : null;

//Comp Science
$scoreSoftware_Development_Methodologies = isset($_SESSION['scores']['Software_Development_Methodologies']) ? $_SESSION['scores']['Software_Development_Methodologies'] : null;



$generalKnowledgeCategories = [
    'film_and_tv' => [
        'title' => 'Film and TV',
        'subcategories' => [
            'film_tv_quiz' => [
                'title' => 'General Knowledge - Film & TV',
                'link' => 'questionFunctions.php?category=films',
                'description' => '20 Random Questions about to test your knowledge on Hollywood\'s Golden Era!',
                'scoreVar' => $scoreFilms
            ],
            'film_year_tv_quiz' => [
                'title' => 'Guess the Year of these Films!',
                'link' => 'questionFunctions.php?category=filmsYear',
                'description' => '20 Random Films every time! Can you guess the year of these Films released before 1970!',
                'scoreVar' => $scoreYearReleased
            ],
            'caryGrantFilms' => [
                'title' => 'Cary Grant Filmography',
                'link' => 'columnQuestionFunctions.php?category=caryGrantFilms',
                'description' => 'How well do you know the films of Cary Grant?',
                'scoreVar' => $scoreCaryGrantFilmography
            ],
            'Doctor_Who_Episodes' => [
                'title' => 'Doctor Who Episodes',
                'link' => 'columnQuestionFunctions.php?category=Doctor_Who_Episodes',
                'description' => 'Name every episode of the rebooted Doctor Who Series from 2005!',
                'scoreVar' => $scoreDW
            ],
            '40s_Films' => [
                'title' => '1940\'s Films',
                'link' => 'columnQuestionFunctions.php?category=40s_Films',
                'description' => '40 Films from the 40s. How many can you get?',
                'scoreVar' => $score40s
            ],
        ]
    ],
    'history' => [
        'title' => 'History',
        'subcategories' => [
            'history_quiz' => [
                'title' => 'The Randomness of History',
                'link' => 'questionFunctions.php?category=history',
                'description' => 'Random Questions about Wars, Monarchs, Empires and Leaders',
                'scoreVar' => $scoreHistory
            ],
            'us_presidents' => [
                'title' => 'US Presidents',
                'link' => 'columnQuestionFunctions.php?category=US_Presidents',
                'description' => 'See if you can name all the US Presidents from Washington to Biden.',
                'scoreVar' => $scoreUSPresidents
            ],
            'famous_wars' => [
                'title' => 'Famous Wars going back to ancient times',
                'link' => 'columnQuestionFunctions.php?category=Famous_Wars',
                'description' => 'Can you name these famous wars throughout history?',
                'scoreVar' => $scoreFamousWars
            ],
            'Greek Mythology Title' => [ //greek_mythology
                'title' => 'Greek Mythology',
                'link' => 'columnQuestionFunctions.php?category=greek_mythology',
                'description' => 'How much do you know about Greek Mythology?',
                'scoreVar' => $scoreGreeks
            ],
            'years To Remember' => [
                'title' => 'Historic Dates Years to Remember',
                'link' => 'questionFunctions.php?category=years_to_remember',
                'description' => 'Assassinations, Reigns, Surrenders and Dynasties. What year was it?',
                'scoreVar' => $scoreYearsToRemember
            ],
            'Empires To Remember' => [
                'title' => 'Empires of the Past',
                'link' => 'questionFunctions.php?category=empires',
                'description' => 'The Byzantines, Ottomans, Roman, Mongolian and everything in between!?',
                'scoreVar' => $scoreEmpires
            ],
        ]
    ],
    'literature' => [
        'title' => 'Literature',
        'subcategories' => [
            'literature' => [
                'title' => 'General Knowledge: Literature',
                'link' => 'questionFunctions.php?category=literature',
                'description' => 'Test your literature knowledge! Shakespeare, Austen, Bronte, and more!',
                'scoreVar' => $scoreLiterature
            ],
            'shakespearePlays' => [
                'title' => 'Shakespeare Plays',
                'link' => 'columnQuestionFunctions.php?category=Shakespeare_Plays',
                'description' => 'All of Shakespeare\'s plays from all three Folios!',
                'scoreVar' => $scoreShakespearePlays
            ],
            'shakespearePlayQuiz' => [
                'title' => 'Knowledge of Shakespeare Plays',
                'link' => 'columnQuestionFunctions.php?category=shakespearePlayQuiz',
                'description' => 'You\'ve read the plays, now how much do you remeber about the plots? Antagonists, Protagonists, Fairies and the odd Bear!',
                'scoreVar' => $scoreShakespearePlaysQuiz
            ]
        ]
    ],
    'geography' => [
        'title' => 'Geography',
        'subcategories' => [
            'geography' => [
                'title' => 'Test your Geography General Knowledge!',
                'link' => 'questionFunctions.php?category=geography',
                'description' => 'Capitals, Countries, Continents and Countless more! 20 Random Questions selected every time!',
                'scoreVar' => $scoreGeography
            ],
            'paris' => [
                'title' => 'Paris, or Paname as the locals call it',
                'link' => 'questionFunctions.php?category=paris',
                'description' => 'How much do you know about the French Captial?!',
                'scoreVar' => $scoreParis
            ]
        ]
    ],
    'science' => [
        'title' => 'Science',
        'subcategories' => [
            'geography' => [
                'title' => 'Biology, Physics and (very little) Chemistry',
                'link' => 'questionFunctions.php?category=science',
                'description' => 'Test your knowledge on these topics! 20 Random Questions every time!',
                'scoreVar' => $scoreScience
            ]
        ]
    ],
    'generalKnowledge' => [
        'title' => 'General Knowledge',
        'link' => 'questionFunctions.php?category=generalKnowledge',
        'description' => 'Test your knowledge on science topics! 20 Random Questions every time!',
        'scoreVar' => $scoreGeneralKnowledge
    ],
    'compScience' => [
        'title' => 'Computer Science',
        'subcategories' => [
            'methodologies' => [
                'title' => 'See how many Software Development Methodologies you can name?',
                'link' => 'questionFunctions.php?category=Software_Development_Methodologies',
                'description' => 'We\'ve all heard of Agile but how many of the arcane and archaic development methods do you know?',
                'scoreVar' => $scoreSoftware_Development_Methodologies
            ],
        ]
    ]
];

?>