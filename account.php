<?php
//BASE_PATH won't work because it's in the config file that we're trying to import.
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
include BASE_PATH . 'account/model/account-logic.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Homepage</title>
    <link rel="stylesheet" href="account/css/styles-account-homepage.css">
    <style>


    </style>
</head>
<body>
<?php include BASE_PATH . "account/account-masthead.php"; ?>

<div class="overview-container">
    <main class="overview-main-container">
        <div class="overview-active">
            <div class="active-member-heading">
            <h2>ACTIVE<span style="color: green; font-size: 16px; margin-left: 10px;">●</span></h2>
            <p>Member since <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
            </div>
        </div>


        <div class="overview">
            <h5 class="overview-titles">Last 30 Days</h5>
            <div class="overview-section">
                <div>
                    <h3><?php echo $articles_last_30_days; ?></h3>
                    <p>Articles Written</p>
                </div>
                <div>
                    <h3><?php echo $comments_last_30_days; ?></h3>
                    <p>Comments Published</p>
                </div>
            </div>

            <h5>All Time</h5>
            <div class="overview-section">
                <div class="overview-titles">
                    <h3><?php echo $total_articles; ?></h3>
                    <p>Articles Written</p>
                </div>
                <div>
                    <h3><?php echo $total_comments; ?></h3>
                    <p>Comments Published</p>
                </div>
            </div>
        </div>


        <h4 class="section-title">Your Favourite Topics to Write About</h4>

        <div class="insights-grid">
            <?php if (!empty($article_counts)): ?>
                <?php foreach ($article_counts as $count): ?>
                    <div>
                        <?php
                        // Check if 'Category' is NULL and replace it with 'Uncategorized'
                        $category = !empty($count['Category']) ? htmlspecialchars($count['Category']) : 'Miscellaneous';
                        ?>
                        <?php echo $category; ?><br>
                        <strong><?php echo htmlspecialchars($count['article_count'] ?? 0); ?></strong>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div colspan="7">No articles found.</div>
            <?php endif; ?>
        </div>

        <h4 class="section-title">Badges</h4>

        <section class="badges-section">
            <div class="badge <?php echo !$badges['newbie_writer'] ? 'badge-inactive' : ''; ?>">
                <img src="<?php echo BASE_URL; ?>public/images/badges/newBieWriter.png">
                <h2 class="badge-header">Newbie Writer</h2>
                <p class="badge-desc">Publish your first article.</p>
            </div>

            <div class="badge <?php echo !$badges['prolific_writer'] ? 'badge-inactive' : ''; ?>">
                <img src="<?php echo BASE_URL; ?>public/images/badges/prolificWriter.png">
                <h2 class="badge-header">Prolific Writer</h2>
                <p class="badge-desc">Publish 50 articles.</p>
            </div>

            <div class="badge <?php echo !$badges['feedback_guru'] ? 'badge-inactive' : ''; ?>">
                <img src="<?php echo BASE_URL; ?>public/images/badges/feedbackGuru.png">
                <h2 class="badge-header">Feedback Guru</h2>
                <p class="badge-desc">Post 100 comments.</p>
            </div>

            <div class="badge <?php echo !$badges['social_butterfly'] ? 'badge-inactive' : ''; ?>">
                <img src="<?php echo BASE_URL; ?>public/images/badges/socialButterfly.png">
                <h2 class="badge-header">Social Butterfly</h2>
                <p class="badge-desc">Bookmark 10 articles.</p>
            </div>

            <div class="badge <?php echo !$badges['trendsetter'] ? 'badge-inactive' : ''; ?>">
                <img src="<?php echo BASE_URL; ?>public/images/badges/trendsetter.png">
                <h2 class="badge-header">Trendsetter</h2>
                <p class="badge-desc">Publish 5 featured articles.</p>
            </div>

            <div class="badge <?php echo !$badges['deep_thinker'] ? 'badge-inactive' : ''; ?>">
                <img src="<?php echo BASE_URL; ?>public/images/badges/deepThinker.png">
                <h2 class="badge-header">Deep Thinker</h2>
                <p class="badge-desc">Write an article with over 2,000 words.</p>
            </div>

            <div class="badge <?php echo !$badges['early_bird'] ? 'badge-inactive' : ''; ?>">
                <img src="<?php echo BASE_URL; ?>public/images/badges/earlyBird.svg">
                <h2 class="badge-header">Early Bird</h2>
                <p class="badge-desc">Comment on 10 articles within an hour of publication.</p>
            </div>

            <div class="badge <?php echo !$badges['top_contributor'] ? 'badge-inactive' : ''; ?>">
                <img src="<?php echo BASE_URL; ?>public/images/badges/topContributor.png">
                <h2 class="badge-header">Top Contributor</h2>
                <p class="badge-desc">Write articles in 3 different categories.</p>
            </div>

            <div class="badge <?php echo !$badges['loyal_reader'] ? 'badge-inactive' : ''; ?>">
                <img src="<?php echo BASE_URL; ?>public/images/badges/loyalReader.png">
                <h2 class="badge-header">Loyal Reader</h2>
                <p class="badge-desc">Bookmark articles in 5 different tags.</p>
            </div>

            <div class="badge <?php echo !$badges['topic_enthusiast'] ? 'badge-inactive' : ''; ?>">
                <img src="<?php echo BASE_URL; ?>public/images/badges/topicEnthusiast.png">
                <h2 class="badge-header">Topic Enthusiast</h2>
                <p class="badge-desc">Write 10 articles in a single category</p>
            </div>

            <div class="badge <?php echo !$badges['bookmark_collector'] ? 'badge-inactive' : ''; ?>">
                <img src="<?php echo BASE_URL; ?>public/images/badges/bookmarkCollector.png">
                <h2 class="badge-header">Bookmark Collector</h2>
                <p class="badge-desc">Bookmark at least 50 articles</p>
            </div>

            <div class="badge <?php echo !$badges['consistent_contributor'] ? 'badge-inactive' : ''; ?>">
                <img src="<?php echo BASE_URL; ?>public/images/badges/consistentContributor.png">
                <h2 class="badge-header">Bookmark Collecor</h2>
                <p class="badge-desc">Bookmark at least 50 articles</p>
            </div>

        </section>
    </main>
</div>

</body>
</html>
